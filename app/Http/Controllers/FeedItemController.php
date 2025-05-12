<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Feed_Item;

use Illuminate\Http\Request;
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
        try {
            $feed = Feed::find($id);

            $items = FeedItemService::fromFeed($feed)
                ->paginate(20)
                ->get();

            return view('admin.feed_items')->with('feed_items', $items);
        } catch (Throwable $th) {
            return back();
        }

    }

    public function directAll()
    {
        try {
            $feeds = Feed::all();

            $feed_items = FeedItemService::fromFeeds($feeds)
                ->sort('desc')
                ->paginate(10)
                ->get();

            return view('admin.feed_items', compact('feed_items'));
        } catch (Throwable $th) {
            return back();
        }

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        try {
            $feed = Feed::findOrFail($id);
            $items = FeedItemService::fromFeed($feed)
                ->sort('asc')
                ->get();
            $processedItems = FeedItemService::processItems($items);
            if ($processedItems){
                $columns = array_diff(
                    array_keys(reset($processedItems)),
                    ['guid', 'created_at']
                );
                
                Feed_Item::upsert($processedItems, ['guid'], $columns);
            }

            $count = count($processedItems);
            $skipped = count($items) - $count;
            $count == 0
                ? $message = "No new items found. {$skipped} duplicates skipped"
                : $message = "Saved {$count} items" . ($skipped ? ", {$skipped} duplicates skipped" : "");

            return back()->with('success', $message);

        } catch (Exception $e) {
            Log::error("Feed processing error: {$e->getMessage()}");
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Feed_Item $feed_Item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feed_Item $feed_Item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feed_Item $feed_Item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feed_Item $feed_Item)
    {
        //
    }
}
