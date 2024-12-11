<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }

        if (! $request->user()?->subscribed('premium_plan')) {
            return $next($request);
        }
        return redirect('/subscription/edit');
    }
}
