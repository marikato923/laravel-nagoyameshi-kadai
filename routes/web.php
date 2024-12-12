<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Models\Restaurant;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__.'/auth.php';


Route::group(['middleware' => 'guest:admin'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
});

Route::group(['middleware' => ['auth', 'verified']], function() {
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('user/{user}', [UserController::class, 'update'])->name('user.update');
});

Route::group(['middleware' => ['auth', 'verified']], function() {
    // NotSubscribed ミドルウェアを適用するグループ
    Route::middleware('NotSubscribed')->group(function () {
        Route::get('subscription/create', [SubscriptionController::class, 'create'])
            ->name('subscription.create');
        Route::post('/subscription/store', [SubscriptionController::class, 'store'])
            ->name('subscription.store');
    });

    // subscribed ミドルウェアを適用するグループ
    Route::middleware('Subscribed')->group(function () {
        Route::get('/subscription/edit', [SubscriptionController::class, 'edit'])
            ->name('subscription.edit');
        Route::patch('/subscription/update', [SubscriptionController::class, 'update'])
            ->name('subscription.update');
        Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])
            ->name('subscription.cancel');
        Route::delete('/subscription', [SubscriptionController::class, 'destroy'])
            ->name('subscription.destroy');
    });
});

// レビュー管理機能
Route::group(['middleware' => ['auth', 'verified']], function() {
    Route::get('/restaurants/{restaurant}/reviews', [ReviewController::class, 'index'])
            ->name('restaurants.reviews.index'); // ログイン済みの一般ユーザーはアクセス可能
    Route::middleware('Subscribed')->resource('restaurants.reviews', ReviewController::class)
            ->except(['index', 'show']); // 認証済み、かつ有料会員のみアクセス可能
});

// 管理者用のルート
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
    Route::resource('users', AdminUserController::class)->only(['index', 'show']);
    Route::resource('restaurants', AdminRestaurantController::class);
    Route::delete('restaurants/{restaurant}', [AdminRestaurantController::class, 'destroy'])->name('restaurants.destroy');
    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::get('company', [Admin\CompanyController::class, 'index'])->name('company.index');
    Route::get('company/edit', [Admin\CompanyController::class, 'edit'])->name('company.edit');
    Route::put('company', [Admin\CompanyController::class, 'update'])->name('company.update');
    Route::get('terms', [Admin\TermController::class, 'index'])->name('terms.index');
    Route::get('terms/edit', [Admin\TermController::class, 'edit'])->name('terms.edit');
    Route::put('terms', [Admin\TermController::class, 'update'])->name('terms.update');
});