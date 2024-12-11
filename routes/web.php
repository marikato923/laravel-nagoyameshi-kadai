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
});

Route::group(['middleware' => ['auth', 'verified', 'guest:admin']], function() {
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('user/{user}', [UserController::class, 'update'])->name('user.update');
});

// 管理者ユーザー用のルート
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