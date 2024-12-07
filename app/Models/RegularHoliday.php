<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\GlobalState\Restorer;

class RegularHoliday extends Model
{
    use HasFactory;

    protected $fillable = ['day', 'day_index'];

    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'regular_holiday_restaurant');
    }
}
