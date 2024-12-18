<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('user/index', compact('user'));
    }

    public function edit(User $user)
    {
        if(Auth::id() !== $user->id)
        {
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if(Auth::id() !== $user->id)
        {
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }

        $validated = $request->validate(User::rule($user));

        $user->update($validated);

        $request->session()->flash('flash_message', '会員情報を編集しました。');

        return redirect()->route('user.index');
    }
}
