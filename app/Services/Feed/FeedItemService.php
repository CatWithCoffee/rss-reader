<?php

namespace App\Services\Feed;

use App\Models\Feed_Item;

use app\Models\Feed;
use Vedmant\FeedReader\Facades\FeedReader;

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

    public static function fromFeeds(iterable $feeds)
    {
        return (new self())->loadFeeds($feeds);
    }

    public function loadFeed(Feed $feed)
    {
        $this->source = $feed;
        $this->items = $this->readFeed($feed);
        return $this;
    }

    public function loadFeeds(iterable $feeds)
    {
        $this->source = collect($feeds);
        $this->items = [];

        foreach ($this->source as $feed) {
            $this->items = array_merge($this->items, $this->readFeed($feed));
        }
        return $this;
    }

    protected function readFeed($feed)
    {
        $feedItems = FeedReader::read($feed->url)->get_items();
        $result = [];

        foreach ($feedItems as $key => $item) {
            $result[$key] = [
                'feed_id' => $feed->id,
                'guid' => $item->get_id(),
                'title' => $item->get_title(),
                'description' => strip_tags($item->get_description()),
                'content' => strip_tags($item->get_content()),
                'link' => $item->get_permalink() ?? $item->get_link(),
                'published_at' => $item->get_gmdate() ?? $item->get_date(),
                'thumbnail' => $this->get_thumbnail($item),
                'authors' => $this->get_authors($item),
                'categories' => $this->get_categories($item),
                'enclosures' => $this->get_enclosures($item),
                'feed' => $feed->title,
                'color' => $feed->color
            ];
        }
        return $result;
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
        $filtered =  array_filter($processedItems, function ($item) {
            if (!isset($item['guid']) || is_array($item['guid'])) {
                Log::warning("Invalid GUID format", ['guid' => $item['guid'] ?? null]);
                return false;
            }

            return !Feed_Item::where('guid', $item['guid'])->exists();


        });
        // dd($filtered);
        return $filtered;
    }

    /**
     * asc (by oldest)/desc (by newest) sort
     *
     * desc sort is default
     */
    public function sort(string $sort_by = 'desc')
    {
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

    public function paginate(int $perPage = 10)
    {
        $currentPage = request()->get('page', 1);
        $pagedData = array_slice($this->items, ($currentPage - 1) * $perPage, $perPage);

        $this->items = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($this->items),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
        return $this;
    }

    public function get()
    {
        return $this->items;
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