<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\JsonResponse;

class MiniAppRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): mixed
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return new JsonResponse([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds
            ], 429, [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::increment($key, $decayMinutes * 60);

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $remaining = $maxAttempts - RateLimiter::attempts($key);
            
            $response->headers->add([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => max(0, $remaining),
            ]);
        }

        return $response;
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $this->resolveUserId($request);
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        return sha1(implode('|', [
            $request->route()?->getDomain() ?? '',
            $ip,
            $userId,
            $request->path(),
            substr($userAgent, 0, 100) // Ограничиваем длину User-Agent
        ]));
    }

    /**
     * Extract user ID from Telegram WebApp data or IP.
     */
    protected function resolveUserId(Request $request): string
    {
        $initData = $request->header('X-Telegram-Web-App-Init-Data') ?? $request->input('_auth');
        
        if ($initData) {
            parse_str($initData, $data);
            if (isset($data['user'])) {
                $userData = json_decode($data['user'], true);
                if (isset($userData['id'])) {
                    return 'tg_' . $userData['id'];
                }
            }
        }
        
        return 'ip_' . $request->ip();
    }
}