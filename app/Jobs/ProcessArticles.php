<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Feed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Http;
use DB;
use Log;
use Exception;
use Carbon\Carbon;
use Vedmant\FeedReader\Facades\FeedReader;
use App\Models\Article;
use App\Models\Statistics;

class ProcessArticles
{
    use Queueable,
        Batchable,
        Dispatchable,
        InteractsWithQueue,
        SerializesModels;

    public $tries = 3;
    public $maxExceptions = 3;
    public $timeout = 60;
    public $failOnTimeout = true;
    public $backoff = [5, 10, 15];

    /**
     * Create a new job instance.
     */
    public function __construct(public Feed $feed)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): array
    {
        Log::info("Processing feed: {$this->feed->id}:{$this->feed->title}");
        dump("Processing feed: {$this->feed->id}:{$this->feed->title}");

        $articles = $this->fetchArticles($this->feed);
        if ($articles === null) {
            dump("No new articles to process for feed: {$this->feed->title}");
            Log::info("No new articles to process for feed: {$this->feed->title}");
            return ['count' => 0, 'skipped' => null];
        }

        $articlesCount = count($articles);
        Log::info("Total articles fetched: " . $articlesCount);

        $totalCount = 0;

        // Разбиваем элементы на чанки и обрабатываем их
        foreach (array_chunk($articles, 100) as $chunk) {
            $processedArticles = $this->processChunk($chunk);

            if (!empty($processedArticles)) {
                $count = $this->saveArticles($processedArticles);
                $totalCount += $count;
                Log::info("Chunk processed: count = {$count}");
            } else {
                Log::info("All articles in the chunk were duplicates.");
            }
        }

        $skipped = $articlesCount - $totalCount;
        Log::info("Feed processing completed: {$this->feed->title}, total articles saved: {$totalCount}, skipped: {$skipped}");
        dump("Completed: {$this->feed->title}, total articles saved: {$totalCount}, skipped: {$skipped}");
        $result = ['count' => $totalCount, 'skipped' => $skipped];
        return (array) $result;
    }

    protected function fetchArticles($feed)
    {
        try {
            // 1. Настраиваем запрос с явным указанием поддерживаемых методов сжатия
            $response = Http::withOptions([
                'decode_content' => false, 
                'force_ip_resolve' => 'v4', 
            ])->withHeaders([
                        'If-None-Match' => $feed->etag ? '"' . $feed->etag . '"' : null,
                        'Accept-Encoding' => 'gzip, deflate', // Указываем поддерживаемые методы сжатия
                        'User-Agent' => config('app.name') . '/1.0',
                    ])->timeout(15)->get($feed->url);

            // 2. Проверяем, что запрос выполнен успешно
            if (!$response->successful()) {
                if ($response->status() === 304) {
                    Log::info('Response status: 304. Feed unchanged');
                    return null;
                }
                throw new Exception('Ошибка при выполнении запроса: ' . $response->status());
            }
            Log::info('Response status: ' . $response->status());

            //--- 3. Обработка Content-Encoding
            $contentEncoding = $response->header('Content-Encoding');
            Log::info("Content-encoding: " . $contentEncoding);
            $content = $response->body();

            if ($contentEncoding == 'br') {
                Log::warning('Unaccepted content-encoding: ' . $contentEncoding);
                return null;
            }

            // switch ($contentEncoding) {
            //     case 'br': // Brotli
            //         if (!function_exists('brotli_uncompress')) {
            //             throw new Exception('Требуется расширение brotli для декомпрессии');
            //         }
            //         $content = brotli_uncompress($content);
            //         dump($content);
            //         break;

            //     case 'gzip': // Gzip
            //         $content = gzdecode($content);
            //         break;

            //     case 'deflate': // Deflate
            //         $content = gzinflate($content);
            //         break;

            //     default: // Без сжатия
            //         // Ничего не делаем, данные уже готовы
            //         break;
            // }


            // 3. Проверка пустого содержимого
            if (empty($content)) {
                dump('empty response');
                throw new Exception('Получен пустой ответ');
            }

            // 4. Проверка изменений через хеш
            $newContentHash = md5($content);
            if ($this->feedUnchanged($feed, $response, $newContentHash)) {
                dump('feed unchanged');
                $feed->touch();
                return null;
            }

            // 5. Обработка фида
            $f = FeedReader::read($feed->url);
            $f->set_raw_data($content);
            $f->enable_cache(false);

            if (!$f->init()) {
                dump('parsing error');
                throw new Exception("Parsing error: " . $f->error());
            }

            // 6. Обновление метаданных
            $this->updateFeedMetadata($feed, $response, $newContentHash);

            // 7. Обработка элементов
            return $this->processArticles($f->get_items(), $feed);

        } catch (Exception $e) {
            Log::error("Feed processing error {$feed->url}: " . $e->getMessage());
            dump("Feed processing error {$feed->url}: " . $e->getMessage());
            return null;
        }
    }

    protected function processArticles($articles, $feed)
    {
        return collect($articles)
            ->sortBy(function ($article) {
                return $article->get_gmdate() ?? $article->get_date();
            })
            ->map(function ($article) use ($feed) {
                return [
                    'feed_id' => $feed->id,
                    'guid' => $article->get_id(),
                    'title' => $this->cleanText($article->get_title()),
                    'description' => $this->cleanText($article->get_description()),
                    'content' => $article->get_description() !== $article->get_content() ? $this->cleanText($article->get_content()) : null,
                    'link' => $article->get_permalink() ?? $article->get_link(),
                    'published_at' => Carbon::parse($article->get_local_date())->setTimezone(config('app.timezone'))->toDateTimeString(),
                    'thumbnail' => $this->get_thumbnail($article),
                    'authors' => $this->get_authors($article),
                    'categories' => $this->get_categories($article),
                    'enclosures' => $this->get_enclosures($article)
                ];
            })
            ->toArray();
    }


    protected function processChunk(array $articles)
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

            // try {
            //     Log::info("Original published_at: " . $article['published_at']);
            //     // Явно указываем часовой пояс при парсинге
            //     $article['published_at'] = $article['published_at']
            //         ? Carbon::parse($article['published_at'])->utc()->setTimezone(config('app.timezone'))->toDateTimeString()
            //         : null;
            //     $parsedTime = Carbon::parse($article['published_at']);
            //     Log::info("Parsed time (UTC): " . $parsedTime->toDateTimeString());
            //     Log::info("Parsed time (Moscow): " . $parsedTime->setTimezone(config('app.timezone'))->toDateTimeString());
            // } catch (Exception $e) {
            //     Log::warning("Date parsing failed for GUID: " . $article['guid']);
            //     $article['published_at'] = null;
            // }

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


    protected function saveArticles(array $articles): int
    {
        if (empty($articles)) {
            return 0;
        }

        DB::beginTransaction();

        try {
            // Вставка данных с использованием upsert
            Article::upsert(
                $articles, // Уже обработанные данные
                ['guid'], // Уникальные поля
                ['title', 'description', 'content', 'link', 'published_at', 'thumbnail', 'authors', 'categories', 'enclosures', 'updated_at'] // Поля для обновления
            );

            // Подсчёт обработанных элементов
            $count = count($articles);

            // Обновление статистики фида
            $this->feed->last_fetched_at = now();
            $this->feed->articles_count += $count;
            $this->feed->save();

            // Обновление статистики приложения
            Statistics::increment('articles_count', $count);

            DB::commit();

            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error saving feed articles: " . $e->getMessage());
            return 0;
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
