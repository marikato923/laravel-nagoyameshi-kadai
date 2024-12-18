<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;
use Illuminate\Validation\Rule;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'kana',
        'email',
        'password',
        'postal_code',
        'address',
        'phone_number',
        'birthday',
        'occupation',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);    
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function favorite_restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_user', 'user_id', 'restaurant_id')
                ->withTimestamps();
    }  
    
    // vlidation rule
    public static function rule(User $user)
    {
        return [
                'name' => ['required', 'string', 'max:255'],
                'kana' => ['required', 'string', 'regex:/\A[ァ-ヴー\s]+\z/u', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255',Rule::unique('users')->ignore($user->id)],
                'postal_code' =>['required', 'digits:7'],
                'address' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'digits_between:10,11'],
                'birthday' => ['nullable', 'digits:8'],
                'occupation' => ['nullable', 'string', 'max:255'],
        ];
    }
}
