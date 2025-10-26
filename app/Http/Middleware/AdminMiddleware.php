<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user() instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Unauthorized (Admin only)'], 401);
        }

        return $next($request);
    }
}
