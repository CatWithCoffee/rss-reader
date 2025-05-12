<?php

namespace App\Http\Controllers;

use App\Models\Statistics;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        $stat = Statistics::all();
        // dd($stat);
        return view('admin.ap')->with('stat', $stat[0]);
    }
}
