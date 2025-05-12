<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('dashboard'));
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin', [FeedController::class, 'index'])->name('admin.feeds');
    Route::post('/admin', [FeedController::class, 'store'])->name('admin.feeds');
    
    Route::get('/admin/edit_feed/{id}', [FeedController::class, 'edit'])->name('admin.edit_feed');
    Route::put('/admin/update_feed/{id}', [FeedController::class, 'update'])->name('admin.update_feed');

    Route::get('/admin/direct_feed_items/all', [FeedItemController::class, 'directAll'])->name('admin.direct_feed_items_all');
    Route::get('/admin/direct_feed_items/{id}', [FeedItemController::class, 'direct'])->name('admin.direct_feed_items');
    
    Route::get('/admin/save_feed_items/{id}', [FeedItemController::class, 'store'])->name('admin.save_feed_items');
    
});

require __DIR__.'/auth.php';
