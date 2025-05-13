<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\FeedItem;

use App\Models\Statistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;
use Throwable;
use Vedmant\FeedReader\Facades\FeedReader;
use App\Services\Feed\FeedItemService;

use Carbon\Carbon;

class FeedItemController extends Controller
{
    public function direct($id)
    {
        if ($id === 'all')
            return $this->directAll();

        try {
            $items = FeedItem::where('feed_id', $id)->latest()->paginate(20);
            return view('admin.FeedItems')->with('FeedItems', $items);
        } catch (Throwable $th) {
            Log::error(`Direct feed items error: {$th->getMessage()}`);
            return back()->with('error', $th->getMessage());
        }

    }

    public function directAll()
    {
        try {
            $items = FeedItem::with('feed')
                ->orderBy('published_at', 'desc')
                ->paginate(20);

            return view('admin.FeedItems_all', [
                'items' => $items,
                'totalItems' => $items->total()
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to fetch feed items: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return back()
                ->with('error', 'Произошла ошибка при загрузке новостей');
        }

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = FeedItem::with('feed')
            ->when($request->source, fn($q) => $q->where('feed_id', $request->source))
            ->latest('published_at')
            ->paginate(12);

        $sources = Feed::has('items')->get();

        return view('dashboard', compact('items', 'sources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($id)
    {
        DB::beginTransaction();
        try {
            $feed = Feed::findOrFail($id);
            $items = FeedItemService::fromFeed($feed)
                ->sort('asc')
                ->get();

            if (empty($items)) {
                DB::rollBack();
                return back()->with('info', 'No new items found');
            }

            $processedItems = FeedItemService::processItems($items);
            if ($processedItems) {
                $columns = array_diff(
                    array_keys(reset($processedItems)),
                    ['guid', 'created_at']
                );

                FeedItem::upsert($processedItems, ['guid'], $columns);
            }

            $count = count($processedItems);
            $skipped = count($items) - $count;
            $count == 0
                ? $message = "No new items found. {$skipped} duplicates skipped"
                : $message = "Saved {$count} items" . ($skipped ? ", {$skipped} duplicates skipped" : "");

            $feed->last_fetched_at = now();
            $feed->items_count += $count;
            $feed->save();

            Statistics::increment('items_count', $count);

            DB::commit();
            return back()->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Feed processing error: {$e->getMessage()}");
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FeedItem $FeedItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeedItem $FeedItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeedItem $FeedItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeedItem $FeedItem)
    {
        //
    }
}
