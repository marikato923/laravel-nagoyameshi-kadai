<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;

class RestaurantController extends Controller
{
    public function index(Request $request)
{
    $keyword = $request->input('keyword');
    $category_id = $request->input('category_id');
    $price = $request->input('price');
    $sorted = 'created_at desc';

    $sorts = [
        '掲載日が新しい順' => 'created_at desc',
        '価格が安い順' => 'lowest_price asc'
    ];

    $query = Restaurant::query();

    if ($keyword) {
        $query->where('name', 'like', "%{$keyword}%")
            ->orWhere("address", 'like', "%{$keyword}%")
            ->orWhereHas('categories', function ($query) use ($keyword) {
                $query->where('categories.name', 'like', "%{$keyword}%");
            })->get();
    }

    if ($category_id) {
        $query->whereHas('categories', function ($query) use ($category_id) {
            $query->where('categories.id', $category_id);
        });
    }

    if ($price) {
        $query->where('lowest_price', '<=', $price);
    }

    if (!$keyword && !$category_id && !$price) {
        $query = Restaurant::query();
    }

    $sort_query = [];
    $sorted = "created_at desc";

    if ($request->has('salect_sort')) {
        $slices = explode(' ', $request->input('select_sort'));
        $sort_query[$slices[0]] = $slices[1];
        $sorted = $request->input('select_sort');
    }

    $restaurants = $query
        ->sortable($sort_query)
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    $categories = Category::all();

    $total = $restaurants->total();

    return view('restaurants.index', compact(
        'keyword',
        'category_id',
        'price',
        'sorts',
        'sorted',
        'restaurants',
        'categories',
        'total'
    ));
    }
    public function show(Restaurant $restaurant)
    {
        return view('restaurants.show', compact('restaurant'));
    }
}
