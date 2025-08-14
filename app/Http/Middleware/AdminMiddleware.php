<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->akses_level == 'Admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Akses ditolak. Hanya untuk Admin.'], 403);
    }
}