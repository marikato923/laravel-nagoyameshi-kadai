<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\TermController as AdminTermController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FavoriteController;



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
    Route::get('company', [CompanyController::class, 'index'] )->name('company.index'); 
    Route::get('terms', [TermController::class, 'index'] )->name('terms.index'); 
});

// 認証とメール認証が必要なルート
Route::group(['middleware' => ['auth', 'verified']], function () {
    // ユーザー管理
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::patch('{user}', [UserController::class, 'update'])->name('update');
    });

    // サブスクリプション管理
    Route::middleware('NotSubscribed')->group(function () {
        Route::get('subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
        Route::post('subscription/store', [SubscriptionController::class, 'store'])->name('subscription.store');
    });

    Route::middleware('Subscribed')->group(function () {
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::get('edit', [SubscriptionController::class, 'edit'])->name('edit');
            Route::patch('update', [SubscriptionController::class, 'update'])->name('update');
            Route::get('cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
            Route::delete('/', [SubscriptionController::class, 'destroy'])->name('destroy');
        });
    });

    // レビュー管理
    Route::get('restaurants/{restaurant}/reviews', [ReviewController::class, 'index'])->name('restaurants.reviews.index');
    Route::middleware('Subscribed')->resource('restaurants.reviews', ReviewController::class)->except(['index', 'show']);

    // 予約管理
    Route::middleware('Subscribed')->get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::middleware('Subscribed')->get('/restaurants/{restaurant}/reservations/create', [ReservationController::class, 'create'])->name('restaurants.reservations.create');
    Route::middleware('Subscribed')->post('/restaurants/{restaurant}/reservations', [ReservationController::class, 'store'])->name('restaurants.reservations.store');
    Route::middleware('Subscribed')->delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

// お気に入り機能
    Route::middleware('Subscribed')->get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::middleware('Subscribed')->post('favorites/{restaurant}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::middleware('Subscribed')->delete('favorites/{restaurant}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
});

// 管理者用のルート
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
    Route::resource('users', AdminUserController::class)->only(['index', 'show']);
    Route::resource('restaurants', AdminRestaurantController::class);
    Route::delete('restaurants/{restaurant}', [AdminRestaurantController::class, 'destroy'])->name('restaurants.destroy');
    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::get('company', [AdminCompanyController::class, 'index'])->name('company.index');
    Route::get('company/edit', [AdminCompanyController::class, 'edit'])->name('company.edit');
    Route::patch('company', [AdminCompanyController::class, 'update'])->name('company.update');
    Route::get('terms', [AdminTermController::class, 'index'])->name('terms.index');
    Route::get('terms/edit', [AdminTermController::class, 'edit'])->name('terms.edit');
    Route::patch('terms', [AdminTermController::class, 'update'])->name('terms.update');
});