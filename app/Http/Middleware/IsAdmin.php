<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Admin Auth Check: ' . (Auth::guard('admin')->check() ? 'Authenticated' : 'Not Authenticated'));
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }
        return redirect()->route('admin.login')->with('error', 'Akses ditolak. Hanya untuk admin.');
    }
}
