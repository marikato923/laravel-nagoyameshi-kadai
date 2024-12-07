<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;

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

Route::get('/', function () {
    return view('welcome');
});


require __DIR__.'/auth.php';


Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
    Route::resource('users', Admin\UserController::class)->only(['index', 'show']);

    Route::resource('restaurants', RestaurantController::class);

    Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');

    Route::resource('categories', Admin\CategoryController::class)->except(['show']);

    Route::get('company', [Admin\CompanyController::class, 'index'])->name('company.index');
    Route::get('company/edit', [Admin\CompanyController::class, 'edit'])->name('company.edit');
    Route::put('company', [Admin\CompanyController::class, 'update'])->name('company.update');

    Route::get('terms', [Admin\TermController::class, 'index'])->name('terms.index');
    Route::get('terms/edit', [Admin\TermController::class, 'edit'])->name('terms.edit');
    Route::put('terms/', [Admin\TermController::class, 'update'])->name('terms.update');
});