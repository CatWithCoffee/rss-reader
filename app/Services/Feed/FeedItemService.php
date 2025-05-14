<?php

namespace App\Services\Feed;

use App\Models\FeedItem;

use app\Models\Feed;
use app\Models\Statistics;
use Vedmant\FeedReader\Facades\FeedReader;
use Http;
use DB;
use Log;
use Exception;
use Carbon\Carbon;

class FeedItemService
{
    protected $items = [];
    protected $source; // collection/feed/null

    public static function fromFeed($feed)
    {
        return (new self())->loadFeed($feed);
    }

    public function loadFeed(Feed $feed)
    {
        $this->source = $feed;
        $this->items = $this->readFeed($feed);
        return $this;
    }

    public static function processItems(array $items): array
    {
        // Сначала преобразуем все элементы
        $processedItems = array_map(function ($item) {
            // Подготовка данных
            $item['title'] = html_entity_decode($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $item['description'] = html_entity_decode($item['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $item['authors'] = isset($item['authors']) && is_array($item['authors'])
                ? json_encode($item['authors'], JSON_UNESCAPED_UNICODE)
                : null;

            $item['categories'] = isset($item['categories']) && is_array($item['categories'])
                ? json_encode($item['categories'], JSON_UNESCAPED_UNICODE)
                : null;

            $item['enclosures'] = isset($item['enclosures']) && is_array($item['enclosures'])
                ? json_encode($item['enclosures'], JSON_UNESCAPED_UNICODE)
                : null;

            try {
                $item['published_at'] = $item['published_at']
                    ? Carbon::parse($item['published_at'])->toDateTimeString()
                    : null;
            } catch (Exception $e) {
                Log::warning("Date parsing failed for GUID: " . $item['guid']);
                $item['published_at'] = null;
            }

            unset($item['color'], $item['feed']);

            return $item;
        }, $items);

        // Затем фильтруем дубликаты
        $filtered = array_filter($processedItems, function ($item) {
            if (!isset($item['guid']) || is_array($item['guid'])) {
                Log::warning("Invalid GUID format", ['guid' => $item['guid'] ?? null]);
                return false;
            }

            return !FeedItem::where('guid', $item['guid'])->exists();
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
        if (!$this->items) {
            return $this;
        }
        if (!in_array($sort_by, ['asc', 'desc'])) {
            $sort_by = 'desc';
        }
        $data = [];
        if ($sort_by == 'asc') {
            $data = usort($this->items, function ($a, $b) {
                $dateA = strtotime($a['published_at']);
                $dateB = strtotime($b['published_at']);
                return $dateA <=> $dateB;
            });
        } else
            $data = usort($this->items, function ($a, $b) {
                $dateA = strtotime($a['published_at']);
                $dateB = strtotime($b['published_at']);
                return $dateB <=> $dateA;
            });
        return $this;
    }

    // public function paginate(int $perPage = 10)
    // {
    //     $currentPage = request()->get('page', 1);
    //     $pagedData = array_slice($this->items, ($currentPage - 1) * $perPage, $perPage);

    //     $this->items = new \Illuminate\Pagination\LengthAwarePaginator(
    //         $pagedData,
    //         count($this->items),
    //         $perPage,
    //         $currentPage,
    //         [
    //             'path' => request()->url(),
    //             'query' => request()->query()
    //         ]
    //     );
    //     return $this;
    // }


    public function get()
    {
        return $this->items;
    }

    public function save()
    {
        if (empty($this->items)) {
            return ['count' => 0, 'skipped' => 0];
        }
        DB::beginTransaction();

        try {
            $processedItems = $this->processItems($this->items);
            if ($processedItems) {
                $columns = array_diff(
                    array_keys(reset($processedItems)),
                    ['guid', 'created_at']
                );

                FeedItem::upsert($processedItems, ['guid'], $columns);
            }

            $count = count($processedItems);
            $skipped = count($this->items) - $count;

            $feed = $this->source;
            $feed->last_fetched_at = now();
            $feed->items_count += $count;
            $feed->save();

            Statistics::increment('items_count', $count);

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
            return $this->processFeedItems($f->get_items(), $feed);

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

    protected function processFeedItems($items, $feed)
    {
        return collect($items)->map(function ($item) use ($feed) {
            return [
                'feed_id' => $feed->id,
                'guid' => $item->get_id(),
                'title' => $this->cleanText($item->get_title()),
                'description' => $this->cleanText($item->get_description()),
                'content' => $item->get_description() !== $item->get_content() ? $this->cleanText($item->get_content()) : null,
                'link' => $item->get_permalink() ?? $item->get_link(),
                'published_at' => $item->get_gmdate() ?? $item->get_date(),
                'thumbnail' => $this->get_thumbnail($item),
                'authors' => $this->get_authors($item),
                'categories' => $this->get_categories($item),
                'enclosures' => $this->get_enclosures($item),
                'feed' => $feed->title,
                'color' => $feed->color
            ];
        })->toArray();
    }

    protected function cleanText(?string $text): ?string
    {
        return $text ? strip_tags(html_entity_decode($text)) : null;
    }

    protected function get_thumbnail($item)
    {
        return $item->get_thumbnail() ?? $item->get_enclosure()->get_player() ?? $item->get_enclosure()->get_link() ?? null;
    }

    protected function get_authors($item)
    {
        if (!$item->get_authors())
            return null;
        $result = [];
        foreach ($item->get_authors() as $key => $author) {
            $result[$key] = $item->get_author($key)->get_name();
        }
        return $result;
    }

    protected function get_categories($item)
    {
        if (!$item->get_categories())
            return null;
        $result = [];
        foreach ($item->get_categories() as $key => $cat) {
            $result[$key] = $item->get_category($key)->get_label();
        }
        return $result;
    }
    protected function get_enclosures($item)
    {
        if (!$item->get_enclosures())
            return null;

        $result = [];
        foreach ($item->get_enclosures() as $key => $enc) {
            $link = $item->get_enclosure($key)->get_player() ?? $item->get_enclosure($key)->get_link();
            $result = null;
            if ($link == $this->get_thumbnail($item)) {
                // $result[$key] = null;
                continue;
            }
            $result[$key] = [
                'link' => $item->get_enclosure($key)->get_player() ?? $item->get_enclosure($key)->get_link(),
                'type' => $item->get_enclosure($key)->get_type(),
                'thumbnail' => $item->get_enclosure($key)->get_thumbnail(),
            ];
        }
        return $result;
    }
}