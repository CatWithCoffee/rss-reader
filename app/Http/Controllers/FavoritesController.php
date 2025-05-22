<?php

namespace App\Http\Controllers;

use App\Models\Favorites;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function toggleFavorite(Request $request, $itemId)
    {
        $user = auth()->user();

        if ($user->favorites()->where('item_id', $itemId)->exists()) {
            $user->favorites()->detach($itemId);
            $isFavorited = false;
        } else {
            $user->favorites()->attach($itemId);
            $isFavorited = true;
        }

        return response()->json(['isFavorited' => $isFavorited]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favorites = auth()->user()->favorites()->orderByDesc('created_at')->paginate(20);

        return view('favorites')->with('favorites', $favorites);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Favorites $favorites)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favorites $favorites)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favorites $favorites)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Favorites $favorites)
    {
        //
    }
}
