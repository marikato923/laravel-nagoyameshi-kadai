<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function __construct()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }
    }

    public function index()
    {
        $term = Term::first();

        return view('terms.index', compact('term'));
    }
}
