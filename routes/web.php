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
use App\Http\Controllers\ReservationController;
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