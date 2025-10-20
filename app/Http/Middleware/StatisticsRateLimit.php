<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class StatisticsRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'statistics:' . $request->user()->id . ':' . $request->ip();
        
        // Ограничение: 60 запросов статистики в минуту на пользователя
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'error' => 'Слишком много запросов. Попробуйте позже.'
            ], 429);
        }
        
        RateLimiter::hit($key, 60);
        
        return $next($request);
    }
}