<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Statistics;
use App\Rules\ValidRssFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Feed\FeedService;
use Validator;
use Log;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeds = Feed::all();
        return view('admin.feeds')->with('feeds', $feeds);
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
    public function store(Request $request)
    {
        ///добавить выбор категории или категорий источника

        $validator = Validator::make($request->all(), [
            'url' => ['required', 'max:255', new ValidRssFeed()],
        ]);
        
        if ($validator->fails()){
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }
        
        DB::beginTransaction();

        try {
            $feed = FeedService::fromRequest($request)->read()->get();
            Feed::create($feed);

            Statistics::increment('feeds_count');

            DB::commit();
            return back()->with('success', 'Feed created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Feed saving error: {$e->getMessage()}");
            return redirect(route('admin.feeds'))->withErrors(['url' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Feed $feed)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $feed = Feed::findOrFail($id);
        return view('admin.edit_feed')->with('feed', $feed);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $feed = Feed::findOrFail($id);
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'url' => ['required', 'string', 'max:255'],
                'site_url' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:255'],
                'language' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:255'],
                'favicon' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'string', 'max:255'],
                'color' => ['nullable', 'string', 'max:255'],
                'update_frequency' => ['required', 'integer', 'max:255'],
                'is_active' => ['sometimes'],
            ]);

            $validated['is_active'] = $request->has('is_active');

            $feed->update($validated);
            return back()->with('success', 'Feed updated successfully.');
        } catch (\Exception $e) {
            Log::error("Feed updating error: {$e->getMessage()}");
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feed $feed)
    {
        //
    }
}
