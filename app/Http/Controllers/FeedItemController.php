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
use App\Jobs\ProcessFeedItems;

use Carbon\Carbon;

class FeedItemController extends Controller
{
    public function direct($id)
    {
        if ($id === 'all')
            return $this->directAll();

        try {
            $feed = Feed::findOrFail($id)->title;
            $items = FeedItem::where('feed_id', $id)->orderBy('published_at', 'desc')->paginate(20);
            return view('admin.FeedItems')->with(['FeedItems' => $items, 'feed' => $feed]);
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
            ->paginate(24);

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
        if ($id === 'all') return $this->store_all();
        
        try {
            $feed = Feed::findOrFail($id);

            if (!$feed->is_active){
                return back()->with('error', 'Feed is inactive');
            }

            $result = ProcessFeedItems::dispatchSync($feed);

            if (!is_array($result) || !isset($result['count'])) {
                throw new Exception("Invalid result format from ProcessFeedItems job.");
            }

            if ($result['count'] == 0) {
                $message = ['info' => "No new items found. {$result['skipped']} duplicates skipped"];
            } else {
                $message = ['success' => "Saved {$result['count']} items" . ($result['skipped'] ? ", {$result['skipped']} duplicates skipped" : "")];
            }

            return back()->with($message);
        } catch (Exception $e) {
            Log::error("Feed processing error: {$e->getMessage()}");
            return back()->with('error', $e->getMessage());
        }
    }

    public function store_all()
    {
        try {
            $totalCount = 0;
            $totalSkipped = 0;

            $feeds = Feed::where('is_active', true)->get();

            foreach ($feeds as $feed) {
                Log::info("Processing feed: " . $feed->url);

                $result = ProcessFeedItems::dispatchSync($feed);

                if (!is_array($result) || !isset($result['count'])) {
                    throw new Exception("Invalid result format from ProcessFeedItems job for feed {$feed->url}.");
                }

                $totalCount += $result['count'];
                $totalSkipped += $result['skipped'];
            }

            if ($totalCount == 0) {
                $message = ['info' => "No new items found. {$totalSkipped} duplicates skipped"];
            } else {
                $message = ['success' => "Saved {$totalCount} items" . ($totalSkipped ? ", {$totalSkipped} duplicates skipped" : "")];
            }

            return back()->with($message);
        } catch (Exception $e) {
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
