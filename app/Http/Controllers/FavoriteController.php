<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }
    }

    public function index()
    {
        $favorite_restaurants = auth()->user()
            ->favorite_restaurants()
            ->orderBy('restaurant_user.created_at', 'desc')
            ->paginate(15);

        return view('favorites.index', compact('favorite_restaurants'));
    }

    public function store(Restaurant $restaurant)
    {
        auth()->user()->favorite_restaurants()->attach($restaurant->id);

        return redirect()->back()->with('flash_message', 'お気に入りに追加しました。');
    }

    public function destroy(Restaurant $restaurant)
    {
        auth()->user()->favorite_restaurants()->detach($restaurant->id);

        return redirect()->back()->with('flash_message', 'お気に入りを解除しました。');
    }
}
