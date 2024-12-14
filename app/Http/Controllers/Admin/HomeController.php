<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index() {

        $total_users = DB::table('users')->count();

        $total_premium_users = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count();

        $total_free_users = $total_users - $total_premium_users;

        $total_restaurants = DB::table('restaurants')->count();

        $total_reservations = DB::table('reservations')->count();

        $sales_for_this_month = $total_premium_users * 300;
        
        return view('admin.home', compact(
            'total_users',
            'total_premium_users',
            'total_free_users',
            'total_restaurants',
            'total_reservations',
            'sales_for_this_month'
        ));
    }
}
