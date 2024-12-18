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
        $sorted = $request->input('select_sort', 'created_at desc');

        $sorts = [
            '掲載日が新しい順' => 'created_at desc',
            '価格が安い順' => 'lowest_price asc',
            '評価が高い順' => 'rating desc',
            '人気順' => 'popular desc', // 予約数でソート
        ];

        $query = Restaurant::query();

        // キーワード検索
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('address', 'like', "%{$keyword}%")
                ->orWhereHas('categories', function ($query) use ($keyword) {
                    $query->where('categories.name', 'like', "%{$keyword}%");
                });
        }

        // カテゴリ絞り込み
        if ($category_id) {
            $query->whereHas('categories', function ($query) use ($category_id) {
                $query->where('id', $category_id);
            });
        }

        // 価格絞り込み
        if ($price) {
            $query->where('lowest_price', '<=', $price);
        }
        
        // 並べ替え処理
        if ($sorted === 'rating desc') {
            $query = Restaurant::ratingSortable($query);
        } elseif ($sorted === 'popular desc') {
            $query = Restaurant::popularSortable($query);
        }

        $restaurants = $query->paginate(15);
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