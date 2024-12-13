<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User; 
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }
    }

    public function index(Restaurant $restaurant)
    {
        $reviews = $restaurant->reviews()->orderBy('created_at', 'desc');
        $user = Auth::user();

        if ($user && $user->subscribed('premium_plan')) {
            $reviews = $reviews->paginate(5);
        } else {
        $reviews = $reviews->paginate(3);
        }

        return view('reviews.index', compact('restaurant', 'reviews'));
        }


    public function create(Restaurant $restaurant)
    {
        return view('reviews.create', compact('restaurant'));
    }


    public function store(Request $request, Restaurant $restaurant, Review $review)
    {
        $request->validate([
            'score' => 'required|numeric|between:1,5',
            'content' => 'required',
        ]);

        $review = new Review([
            'score' => $request->score,
            'content' => $request->content,
            'restaurant_id' => $restaurant->id,
            'user_id' => auth()->id(),
        ]);
        $review->save();

        return redirect()->route('restaurants.reviews.index', $restaurant)
            ->with('flash_message', 'レビューを投稿しました。');
    }


    public function edit(Restaurant $restaurant, Review $review)
    {
        if($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index', ['restaurant' => $restaurant])
                ->with('error_message', '不正なアクセスです。');
        }

        return view('reviews.edit', compact('restaurant', 'review'));
    } 

    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index', ['restaurant' => $restaurant])
            ->with('error_message', '不正なアクセスです。');
    }
        $request->validate([
            'score' => 'required|numeric|between:1,5',
            'content' => 'required',
        ]);

        $review->update($request->only(['score', 'content']));

        return redirect(route('restaurants.reviews.index', $restaurant))
            ->with('flash_message', 'レビューを編集しました。');
    } 

    public function destroy(Request $request, Restaurant $restaurant, Review $review)
    {
        if($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index', ['restaurant' => $restaurant])
            ->with('error_message', '不正なアクセスです。');
        }

        $review->delete();

        return redirect(route('restaurants.reviews.index', $restaurant))->with('flash_message', 'レビューを削除しました。');
    }
}
