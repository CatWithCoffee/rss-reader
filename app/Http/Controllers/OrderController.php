<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Order;
use App\Models\Statistics;
use Illuminate\Http\Request;
use Throwable;
use Validator;
use DB;
use Log;
use App\Rules\ValidRssFeed;
use App\Services\Feed\FeedService;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['user', 'feed'])
            ->paginate(20);
        return view('admin.orders')
            ->with('orders', $orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $orders = Auth()->user()->orders()->with('feed')->paginate(20);
        // $orders = Order::with(['user', 'feed'])
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10);
        // dd($orders);
        return view('orders')
            ->with('orders', $orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required', 'max:255', new ValidRssFeed(), 'unique:' . Feed::class, 'unique:' . Order::class],
        ]);

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        try {
            $order = FeedService::fromRequest($request)->order()->get();
        } catch (Throwable $th) {
            return back()
                ->with('error', $th->getMessage());
        }
        Order::create($order);

        return back();
    }

    public function accept($id)
    {
        $order = Order::find($id);
        $url = $order->url;

        DB::beginTransaction();

        try {
            $feedData = FeedService::fromUrl($url)->read()->get();
            $feed = new Feed($feedData);
            $feed->order_id = $order->id;
            $feed->save();

            Statistics::increment('feeds_count');

            $order->update(['status' => 'accepted']);

            DB::commit();
            return back()->with('success', 'Feed created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Feed saving error: {$e->getMessage()}");
            return redirect(route('admin.feeds'))->withErrors(['url' => $e->getMessage()]);
        }
    }

    public function reject($id)
    {
        Order::find($id)
            ->update(['status' => 'rejected']);
        return back()->with('info', 'Order rejected.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}
