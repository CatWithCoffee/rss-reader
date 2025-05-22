<?php

namespace App\Http\Controllers;

use App\Models\Statistics;
use App\Models\Feed;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        $stat = Statistics::all();
        $feeds = Feed::orderBy('items_count', 'desc')
            ->take(10)
            ->get();
        $feedNames = $feeds->pluck('title');
        $itemsCount = $feeds->pluck('items_count');
        $feedColors = $feeds->pluck('color');

        return view('admin.ap')->with(['stat' => $stat[0], 'feedNames' => $feedNames, 'itemsCount' => $itemsCount, 'feedColors' => $feedColors]);
    }
}
