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
use App\Models\FeedItem;
use App\Models\Statistics;

class ProcessFeedItems
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
        Log::info("Processing feed: {$this->feed->title}");
        dump("Processing feed: {$this->feed->title}");

        $items = $this->fetchFeedItems($this->feed);
        if ($items === null) {
            dump("No new items to process for feed: {$this->feed->title}");
            Log::info("No new items to process for feed: {$this->feed->title}");
            return ['count' => 0, 'skipped' => null];
        }

        $itemsCount = count($items);
        Log::info("Total items fetched: " . $itemsCount);

        $totalCount = 0;

        // Разбиваем элементы на чанки и обрабатываем их
        foreach (array_chunk($items, 100) as $chunk) {
            $processedItems = $this->processChunk($chunk);

            if (!empty($processedItems)) {
                $count = $this->saveItems($processedItems);
                $totalCount += $count;
                Log::info("Chunk processed: count = {$count}");
            } else {
                Log::info("All items in the chunk were duplicates.");
            }
        }

        $skipped = $itemsCount - $totalCount;
        Log::info("Feed processing completed: {$this->feed->title}, total items saved: {$totalCount}, skipped: {$skipped}");
        dump("Completed: {$this->feed->title}, total items saved: {$totalCount}, skipped: {$skipped}");
        $result = ['count' => $totalCount, 'skipped' => $skipped];
        return (array) $result;
    }



    protected function fetchFeedItems($feed)
    {
        try {
            // 1. Настраиваем запрос с явным указанием поддерживаемых методов сжатия
            $response = Http::withOptions([
                'decode_content' => true, // Автоматическое распаковывание
                'force_ip_resolve' => 'v4', // Избегаем проблем с IPv6
            ])->withHeaders([
                        'If-None-Match' => $feed->etag ? '"' . $feed->etag . '"' : null,
                        'Accept-Encoding' => 'gzip, deflate, br', // Только поддерживаемые методы
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
            return $this->processItems($f->get_items(), $feed);

        } catch (Exception $e) {
            Log::error("Feed processing error {$feed->url}: " . $e->getMessage());
            dump("Feed processing error {$feed->url}: " . $e->getMessage());
            return null;
        }
    }

    protected function processItems($items, $feed)
    {
        return collect($items)
            ->sortBy(function ($item) {
                return $item->get_gmdate() ?? $item->get_date();
            })
            ->map(function ($item) use ($feed) {
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
            })
            ->toArray();
    }

    protected function processChunk(array $items)
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

    protected function saveItems(array $items): int
    {
        if (empty($items)) {
            return 0;
        }

        DB::beginTransaction();

        try {
            // Вставка данных с использованием upsert
            FeedItem::upsert(
                $items, // Уже обработанные данные
                ['guid'], // Уникальные поля
                ['title', 'description', 'content', 'link', 'published_at', 'thumbnail', 'authors', 'categories', 'enclosures', 'updated_at'] // Поля для обновления
            );

            // Подсчёт обработанных элементов
            $count = count($items);

            // Обновление статистики фида
            $this->feed->last_fetched_at = now();
            $this->feed->items_count += $count;
            $this->feed->save();

            // Обновление статистики приложения
            Statistics::increment('items_count', $count);

            DB::commit();

            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error saving feed items: " . $e->getMessage());
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
