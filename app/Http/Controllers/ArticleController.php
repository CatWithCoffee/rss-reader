<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Article;

use App\Models\Statistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;
use Throwable;
use Vedmant\FeedReader\Facades\FeedReader;
use App\Services\Feed\Articleservice;
use App\Jobs\ProcessArticles;

use Carbon\Carbon;

class ArticleController extends Controller
{
    public function direct($id)
    {
        if ($id === 'all')
            return $this->directAll();

        try {
            $feed = Feed::findOrFail($id)->title;
            $articles = Article::where('feed_id', $id)->orderBy('published_at', 'desc')->paginate(20);
            return view('admin.articles')->with(['articles' => $articles, 'feed' => $feed]);
        } catch (Throwable $th) {
            Log::error(`Direct feed articles error: {$th->getMessage()}`);
            return back()->with('error', $th->getMessage());
        }

    }

    public function directAll()
    {
        try {
            $articles = Article::with('feed')
                ->orderBy('published_at', 'desc')
                ->paginate(20);

            return view('admin.articles_all', [
                'articles' => $articles,
                'totalArticles' => $articles->total()
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to fetch feed articles: ' . $e->getMessage(), [
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
        $articles = Article::with('feed')
            ->when(
                $request->filled('source'),
                fn($q) => $q->where('feed_id', $request->source)
            )
            ->when(
                $request->filled('category'),
                fn($q) => $q->whereRaw(
                    'JSON_SEARCH(LOWER(categories), "one", LOWER(?)) IS NOT NULL',
                    [trim($request->category)]
                )
            )
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                })
            )
            ->latest('published_at')
            ->paginate(24);

        $sources = Feed::has('articles')->get();

        return view('dashboard', compact('articles', 'sources'));
    }

    public function showCategory($category)
    {
        $articles = Article::whereJsonContains('categories', $category)->latest('published_at')->paginate(24);
        $sources = Feed::has('articles')->get();

        return view('dashboard', compact('articles', 'sources'));
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
        if ($id === 'all')
            return $this->storeAll();

        try {
            $feed = Feed::findOrFail($id);

            if (!$feed->is_active) {
                return back()->with('error', 'Feed is inactive');
            }

            $result = ProcessArticles::dispatchSync($feed);

            if (!is_array($result) || !isset($result['count'])) {
                throw new Exception("Invalid result format from ProcessArticles job.");
            }

            if ($result['count'] == 0) {
                $message = ['info' => "No new articles found. {$result['skipped']} duplicates skipped"];
            } else {
                $message = ['success' => "Saved {$result['count']} articles" . ($result['skipped'] ? ", {$result['skipped']} duplicates skipped" : "")];
            }

            return back()->with($message);
        } catch (Exception $e) {
            Log::error("Feed processing error: {$e->getMessage()}");
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeAll()
    {
        try {
            $totalCount = 0;
            $totalSkipped = 0;

            $feeds = Feed::where('is_active', true)->get();

            foreach ($feeds as $feed) {
                Log::info("Processing feed: " . $feed->url);

                $result = ProcessArticles::dispatchSync($feed);

                if (!is_array($result) || !isset($result['count'])) {
                    throw new Exception("Invalid result format from ProcessArticles job for feed {$feed->url}.");
                }

                $totalCount += $result['count'];
                $totalSkipped += $result['skipped'];
            }

            if ($totalCount == 0) {
                $message = ['info' => "No new articles found. {$totalSkipped} duplicates skipped"];
            } else {
                $message = ['success' => "Saved {$totalCount} articles" . ($totalSkipped ? ", {$totalSkipped} duplicates skipped" : "")];
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
    public function show(Article $Article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $Article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $Article)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $Article)
    {
        //
    }
}
