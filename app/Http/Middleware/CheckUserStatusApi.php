<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserStatusApi
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();
        if ($user && $user->status === 'blocked') {
            Log::warning("User ID {$user->id} ({$user->email}) diblokir mencoba mengakses API: {$request->path()}");
            return response()->json([
                'status' => false,
                'error' => 'Akun Anda telah diblokir. Silakan hubungi admin untuk informasi lebih lanjut.',
                'blocked' => true,
            ], 403);
        }
        return $next($request);
    }
}