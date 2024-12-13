<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Restaurant extends Model
{
    use HasFactory, Sortable;

    protected $table = "restaurants";

    protected $fillable = [
        'name',
        'image',
        'description',
        'lowest_price',
        'highest_price',
        'postal_code',
        'address',
        'opening_time',
        'closing_time',
        'seating_capacity',
        'created_at',
        'updated_at'
    ];

    // Categoryとのリレーション
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant', 'restaurant_id', 'category_id')
                    ->withTimestamps();
    }

    // 定休日とのリレーション
    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class, 'regular_holiday_restaurant');
    }

    // レビューとのリレーション
    public function reviews()
    {
        return $this->hasMany(Review::class);
    } 

    // 予約とのリレーション
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public static function storeRestaurant($request)
    {

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

         return $restaurant;
    }

    // 店舗の平均評価順
    public function ratingSortable($query, $direction)
    {
        return $query->withAvg('reviews', 'score')->orderBy('reviews_avg_score', $direction);
    }

    // 予約数が多い順
    public function popularSortable($query, $dorection)
    {
        return $query->withCount('reservations')->orderBy('reservations_count', 'desc');
    }
    
}
