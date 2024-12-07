<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\RegularHoliday;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // 店舗一覧ページ
    public function index(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $restaurants = Restaurant::when($keyword, function ($query, $keyword) {
            return $query->where('name', 'like', "%{$keyword}%");
        })
        ->paginate(10);

    // 店舗データ、検索キーワード、総数をビューに渡す
    $total = $restaurants->total();

    return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
    }

    // 店舗詳細ページ
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    // 店舗情報作成ページ
    public function create()
    {
        $categories = Category::all();

        $regular_holidays = RegularHoliday::all();

        return view ('admin.restaurants.create', compact('categories', 'regular_holidays'));
    }

    // バリデーションとデータベースへの保存
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required|string',
            'lowest_price' => 'required|numeric|min:0|max:' . $request->input('highest_price'),
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'seating_capacity' => 'required|numeric|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'regular_holiday_ids' => 'nullable|array',
            'regular_holiday_ids.*' => 'exists:regular_holidays,id',
        ]);

        // 画像アップロード処理
        $image = '';
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/restaurants');
            $image = basename($imagePath); // ファイル名を取得
        }
        
        // 新しい店舗をデータベースに保存
        $restaurant = new Restaurant();
        $restaurant->name = $request->name;
        $restaurant->description = $request->description;
        $restaurant->lowest_price = $request->lowest_price;
        $restaurant->highest_price = $request->highest_price;
        $restaurant->postal_code = $request->postal_code;
        $restaurant->address = $request->address;
        $restaurant->opening_time = $request->opening_time;
        $restaurant->closing_time = $request->closing_time;
        $restaurant->seating_capacity = $request->seating_capacity;
        $restaurant->image = $image;
        $restaurant->save();   

        $category_ids = array_filter($request->input('category_ids', []));
        $restaurant->categories()->sync($category_ids);

        $regular_holiday_ids = array_filter($request->input('regular_holiday_ids', []));
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

    // セッションにフラッシュメッセージを追加
    session()->flash('flash_message', '店舗を登録しました。');

    // 店舗一覧ページにリダイレクト
    return redirect()->route('admin.restaurants.index');
    }

    // editアクション
    public function edit(Restaurant $restaurant)
    {
        $categories = Category::all();

        $category_ids = $restaurant->categories->pluck('id')->toArray();

        $regular_holidays = RegularHoliday::all();

        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids', 'regular_holidays'));
    }

    // updateアクション
    public function update(Request $request, Restaurant $restaurant)
    {
        // バリデーション
        $validated = $request->validate([
            'name' => 'required',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|numeric|min:0',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'regular_holiday_ids' => 'nullable|array',
            'regular_holiday_ids.*' => 'exists:regular_holidays,id',
        ]);

        // 画像の処理
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('restaurants', 'public');
            $imageName = basename($image);
            $restaurant->image = $imageName;
        }

        // 店舗情報の更新
        $restaurant->name = $validated['name'];
        $restaurant->description = $validated['description'];
        $restaurant->lowest_price = $validated['lowest_price'];
        $restaurant->highest_price = $validated['highest_price'];
        $restaurant->postal_code = $validated['postal_code'];
        $restaurant->address = $validated['address'];
        $restaurant->opening_time = $validated['opening_time'];
        $restaurant->closing_time = $validated['closing_time'];
        $restaurant->seating_capacity = $validated['seating_capacity'];

        $restaurant->save();

        $category_ids = array_filter($request->input('category_ids',[]));
        $restaurant->categories()->sync($category_ids);

        $regular_holiday_ids = array_filter($request->input('regular_holiday_ids', []));
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        session()->flash('flash_message', '店舗を編集しました。');

        return redirect()->route('admin.restaurants.show', $restaurant->id);
    }

    // destroyアクション
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();

        session()->flash('flash_message', '店舗を削除しました。');

        return redirect()->route('admin.restaurants.index');
    }
}
