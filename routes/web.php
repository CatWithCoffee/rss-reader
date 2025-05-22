<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('dashboard'));
});

Route::get('/dashboard', [FeedItemController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
    Route::post('/favorites/{itemId}', [FavoritesController::class, 'toggleFavorite']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::middleware('auth')->group(function () {
    
// });

Route::middleware(['auth', 'role:admin'])->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    Route::get('/admin/feeds', [FeedController::class, 'index'])->name('admin.feeds');
    Route::post('/admin/feeds', [FeedController::class, 'store'])->name('admin.feeds');
    
    Route::get('/admin/edit_feed/{id}', [FeedController::class, 'edit'])->name('admin.edit_feed');
    Route::put('/admin/update_feed/{id}', [FeedController::class, 'update'])->name('admin.update_feed');

    Route::get('/admin/feed_items/{id}', [FeedItemController::class, 'direct'])->name('admin.FeedItems');
    Route::get('/admin/feed_items/all', [FeedItemController::class, 'directAll'])->name('admin.FeedItems_all');
    
    Route::get('/admin/save_feed_items/{id}', [FeedItemController::class, 'store'])->name('admin.save_FeedItems');
    Route::get('/admin/save_feed_items/all', [FeedItemController::class, 'store_all'])->name('admin.save_FeedItems_all');

    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
});

require __DIR__.'/auth.php';
