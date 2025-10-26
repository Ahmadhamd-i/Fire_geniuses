<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user() instanceof \App\Models\Employee) {
            return response()->json(['message' => 'Unauthorized (Employee only)'], 401);
        }

        return $next($request);
    }
}
