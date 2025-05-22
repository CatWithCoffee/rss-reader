<?php

namespace App\Services\Feed;

use App\Models\Article;

use app\Models\Feed;
use app\Models\Statistics;
use Vedmant\FeedReader\Facades\FeedReader;
use Http;
use DB;
use Log;
use Exception;
use Carbon\Carbon;

class Articleservice
{
    protected $articles = [];
    protected $source; // collection/feed/null

    public static function fromFeed($feed)
    {
        return (new self())->loadFeed($feed);
    }

    public function loadFeed(Feed $feed)
    {
        $this->source = $feed;
        $this->articles = $this->readFeed($feed);
        return $this;
    }

    public static function preprocessArticles(array $articles): array
    {
        // Сначала преобразуем все элементы
        $processedArticles = array_map(function ($article) {
            // Подготовка данных
            $article['title'] = html_entity_decode($article['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $article['description'] = html_entity_decode($article['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $article['authors'] = isset($article['authors']) && is_array($article['authors'])
                ? json_encode($article['authors'], JSON_UNESCAPED_UNICODE)
                : null;

            $article['categories'] = isset($article['categories']) && is_array($article['categories'])
                ? json_encode($article['categories'], JSON_UNESCAPED_UNICODE)
                : null;

            $article['enclosures'] = isset($article['enclosures']) && is_array($article['enclosures'])
                ? json_encode($article['enclosures'], JSON_UNESCAPED_UNICODE)
                : null;

            try {
                $article['published_at'] = $article['published_at']
                    ? Carbon::parse($article['published_at'])->toDateTimeString()
                    : null;
            } catch (Exception $e) {
                Log::warning("Date parsing failed for GUID: " . $article['guid']);
                $article['published_at'] = null;
            }

            unset($article['color'], $article['feed']);

            return $article;
        }, $articles);

        // Затем фильтруем дубликаты
        $filtered = array_filter($processedArticles, function ($article) {
            if (!isset($article['guid']) || is_array($article['guid'])) {
                Log::warning("Invalid GUID format", ['guid' => $article['guid'] ?? null]);
                return false;
            }

            return !Article::where('guid', $article['guid'])->exists();
        });
        return $filtered;
    }

    /**
     * asc (by oldest)/desc (by newest) sort
     *
     * desc sort is default
     */
    public function sort(string $sort_by = 'desc')
    {
        if (!$this->articles) {
            return $this;
        }
        if (!in_array($sort_by, ['asc', 'desc'])) {
            $sort_by = 'desc';
        }
        $data = [];
        if ($sort_by == 'asc') {
            $data = usort($this->articles, function ($a, $b) {
                $dateA = strtotime($a['published_at']);
                $dateB = strtotime($b['published_at']);
                return $dateA <=> $dateB;
            });
        } else
            $data = usort($this->articles, function ($a, $b) {
                $dateA = strtotime($a['published_at']);
                $dateB = strtotime($b['published_at']);
                return $dateB <=> $dateA;
            });
        return $this;
    }

    public function get()
    {
        return $this->articles;
    }

    public function save()
    {
        if (empty($this->articles)) {
            return ['count' => 0, 'skipped' => 0];
        }
        DB::beginTransaction();

        try {
            $processedArticles = $this->preprocessArticles($this->articles);
            if ($processedArticles) {
                $columns = array_diff(
                    array_keys(reset($processedArticles)),
                    ['guid', 'created_at']
                );

                Article::upsert($processedArticles, ['guid'], $columns);
            }

            $count = count($processedArticles);
            $skipped = count($this->articles) - $count;

            $feed = $this->source;
            $feed->last_fetched_at = now();
            $feed->articles_count += $count;
            $feed->save();

            Statistics::increment('articles_count', $count);

            DB::commit();

            return ['count' => $count, 'skipped' => $skipped];
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
    protected function readFeed($feed)
    {
        try {
            // 1. Настраиваем запрос с явным указанием поддерживаемых методов сжатия
            $response = Http::withOptions([
                'decode_content' => true, // Автоматическое распаковывание
                'force_ip_resolve' => 'v4', // Избегаем проблем с IPv6
            ])->withHeaders([
                        'If-None-Match' => $feed->etag ? '"' . $feed->etag . '"' : null,
                        'Accept-Encoding' => 'gzip, deflate', // Только поддерживаемые методы
                        'User-Agent' => config('app.name') . '/1.0',
                    ])->timeout(15)->get($feed->url);

            // 2. Обработка возможного brotli-сжатия вручную
            if ($response->header('Content-Encoding') === 'br') {
                if (!function_exists('brotli_uncompress')) {
                    throw new Exception('Требуется расширение brotli для декомпрессии');
                }
                $content = brotli_uncompress($response->body());
            } else {
                $content = $response->body();
            }

            // 3. Проверка пустого содержимого
            if (empty($content)) {
                throw new Exception('Получен пустой ответ');
            }

            // 4. Проверка изменений через хеш
            $newContentHash = md5($content);
            if ($this->feedUnchanged($feed, $response, $newContentHash)) {
                $feed->touch();
                return null;
            }

            // 5. Обработка фида
            $f = FeedReader::read($feed->url);
            $f->set_raw_data($content);
            $f->enable_cache(false);

            if (!$f->init()) {
                throw new Exception("Ошибка парсинга: " . $f->error());
            }

            // 6. Обновление метаданных
            $this->updateFeedMetadata($feed, $response, $newContentHash);

            // 7. Обработка элементов
            return $this->processArticles($f->get_articles(), $feed);

        } catch (Exception $e) {
            Log::error("Ошибка обработки фида {$feed->url}: " . $e->getMessage());
            return null;
        }
    }

    protected function feedUnchanged($feed, $response, $newHash): bool
    {
        return $response->status() === 304 ||
            ($feed->etag && $response->header('ETag') && trim($response->header('ETag'), '"\'') === $feed->etag) ||
            $feed->content_hash === $newHash;
    }

    protected function updateFeedMetadata($feed, $response, $newHash)
    {
        $updateData = [
            'content_hash' => $newHash,
            'last_fetched_at' => now(),
        ];

        if ($etag = $response->header('ETag')) {
            $updateData['etag'] = trim($etag, '"\'');
        }

        if ($lastModified = $response->header('Last-Modified')) {
            $updateData['last_modified'] = $lastModified;
        }

        $feed->update($updateData);
    }

    protected function processArticles($articles, $feed)
    {
        return collect($articles)->map(function ($article) use ($feed) {
            return [
                'feed_id' => $feed->id,
                'guid' => $article->get_id(),
                'title' => $this->cleanText($article->get_title()),
                'description' => $this->cleanText($article->get_description()),
                'content' => $article->get_description() !== $article->get_content() ? $this->cleanText($article->get_content()) : null,
                'link' => $article->get_permalink() ?? $article->get_link(),
                'published_at' => $article->get_gmdate() ?? $article->get_date(),
                'thumbnail' => $this->get_thumbnail($article),
                'authors' => $this->get_authors($article),
                'categories' => $this->get_categories($article),
                'enclosures' => $this->get_enclosures($article),
                'feed' => $feed->title,
                'color' => $feed->color
            ];
        })->toArray();
    }

    protected function cleanText(?string $text): ?string
    {
        return $text ? strip_tags(html_entity_decode($text)) : null;
    }

    protected function get_thumbnail($article)
    {
        return $article->get_thumbnail() ?? $article->get_enclosure()->get_player() ?? $article->get_enclosure()->get_link() ?? null;
    }

    protected function get_authors($article)
    {
        if (!$article->get_authors())
            return null;
        $result = [];
        foreach ($article->get_authors() as $key => $author) {
            $result[$key] = $article->get_author($key)->get_name();
        }
        return $result;
    }

    protected function get_categories($article)
    {
        if (!$article->get_categories())
            return null;
        $result = [];
        foreach ($article->get_categories() as $key => $cat) {
            $result[$key] = $article->get_category($key)->get_label();
        }
        return $result;
    }
    protected function get_enclosures($article)
    {
        if (!$article->get_enclosures())
            return null;

        $result = [];
        foreach ($article->get_enclosures() as $key => $enc) {
            $link = $article->get_enclosure($key)->get_player() ?? $article->get_enclosure($key)->get_link();
            $result = null;
            if ($link == $this->get_thumbnail($article)) {
                // $result[$key] = null;
                continue;
            }
            $result[$key] = [
                'link' => $article->get_enclosure($key)->get_player() ?? $article->get_enclosure($key)->get_link(),
                'type' => $article->get_enclosure($key)->get_type(),
                'thumbnail' => $article->get_enclosure($key)->get_thumbnail(),
            ];
        }
        return $result;
    }
}