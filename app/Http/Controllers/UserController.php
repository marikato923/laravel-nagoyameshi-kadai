<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kana' => ['required', 'string', 'regex:/\A[ァ-ヴー\s]+\z/u', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',Rule::unique('users')->ignore($user->id)],
            'postal_code' =>['required', 'digits:7'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'digits_between:10,11'],
            'birthday' => ['nullable', 'digits:8'],
            'occupation' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        $request->session()->flash('flash_message', '会員情報を編集しました。');

        return redirect()->route('user.index');
    }
}
