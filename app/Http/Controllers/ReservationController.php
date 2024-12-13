<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }
    }

    public function index()
    {
        $reservations = Reservation::where('user_id', auth()->id())
            ->orderBy('reserved_datetime', 'desc')
            ->paginate(15);
        
        return view('reservations.index', compact('reservations'));
    }

    public function create(Restaurant $restaurant)
    {
        return view('reservations.create', compact('restaurant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_date' => 'required|date_format:Y-m-d',
            'reservation_time' => 'required|date_format:H:i',
            'number_of_people' => 'required|integer|between:1,50',
        ]);

        $reservedDatetime = $validated['reservation_date'] . ' ' . $validated['reservation_time'];

        Reservation::create([
            'reserved_datetime' => $reservedDatetime,
            'number_of_people' => $validated['number_of_people'],
            'restaurant_id' => $request->input('restaurant_id'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('reservations.index')
            ->with('flash_message', '予約を完了しました。');
    }
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
        return redirect()->route('reservations.index')
            ->with('error_message', '不正なアクセスです。');
        }

        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('flash_message', '予約をキャンセルしました。');
    }
}
