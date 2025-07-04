<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('dashboard'));
});

Route::get('/dashboard', [ArticleController::class, 'index'])->name('dashboard');
Route::get('/dashboard/search', [ArticleController::class, 'searchCategories'])->name('dashboard.search');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
    Route::post('/favorites/{articleId}', [FavoritesController::class, 'toggleFavorite']);

    Route::get('/orders', [OrderController::class, 'create'])->name('orders');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function() {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    Route::get('/admin/feeds', [FeedController::class, 'index'])->name('admin.feeds');
    Route::post('/admin/feeds', [FeedController::class, 'store'])->name('admin.feeds');
    
    Route::get('/admin/edit_feed/{id}', [FeedController::class, 'edit'])->name('admin.edit_feed');
    Route::put('/admin/update_feed/{id}', [FeedController::class, 'update'])->name('admin.update_feed');

    Route::get('/admin/articles/{id}', [ArticleController::class, 'direct'])->name('admin.Articles');
    Route::get('/admin/articles/all', [ArticleController::class, 'directAll'])->name('admin.Articles_all');
    
    Route::get('/admin/save_articles/{id}', [ArticleController::class, 'store'])->name('admin.save_Articles');
    Route::get('/admin/save_articles/all', [ArticleController::class, 'storeAll'])->name('admin.save_Articles_all');
    
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::patch('/admin/users/{user}/update-role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/users/search', [UserController::class, 'search'])->name('admin.users.search');
    
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders');
    Route::post('/admin/orders/accept/{id}', [OrderController::class, 'accept'])->name('admin.orders.accept');
    Route::post('/admin/orders/reject/{id}', [OrderController::class, 'reject'])->name('admin.orders.reject');
});

require __DIR__.'/auth.php';
