<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // 会員一覧ページを表示
    public function index(Request $request)
    {
        $keyword = $request->input('keyword'); // 検索ボックスのキーワードを取得
        $query = User::query();

        if ($keyword !== null) {
            $users = User::where('name', 'like', "%{$keyword}%")
                         ->orWhere('kana', 'like', "%{$keyword}%")
                         ->paginate(10);
            $total = $users->total();
        } else {
            $users = User::paginate(10);
            $total = User::count();
        }

        return view('admin.users.index', compact('users', 'keyword', 'total'));
    }

    // 会員詳細ページを表示
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }
}
