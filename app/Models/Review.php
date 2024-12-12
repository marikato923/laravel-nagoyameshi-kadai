<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'score',
        'restaurant_id',
        'user_id',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    } 

    public function user()
    {
        return $this->belongsTo(User::class);    
    }
}

