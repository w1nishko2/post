<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если это AJAX запрос или запрос ожидает JSON
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            // Отключаем вывод ошибок PHP в браузер
            @ini_set('display_errors', '0');
            @ini_set('html_errors', '0');
            
            try {
                $response = $next($request);
                
                // Проверяем, не HTML ли это вместо JSON
                if ($response instanceof Response) {
                    $content = $response->getContent();
                    
                    // Если ответ начинается с HTML тега - это ошибка
                    if (strpos(trim($content), '<') === 0) {
                        Log::error('HTML response detected instead of JSON', [
                            'content_preview' => substr($content, 0, 200),
                            'url' => $request->url()
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Ошибка сервера: получен HTML вместо JSON. Проверьте логи.',
                            'error_type' => 'unexpected_html_response'
                        ], 500);
                    }
                }
                
                return $response;
                
            } catch (\Throwable $e) {
                // Ловим все ошибки и возвращаем JSON
                Log::error('Caught error in ForceJsonResponse middleware', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->url()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Произошла ошибка: ' . $e->getMessage(),
                    'error_type' => 'caught_exception',
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ] : null
                ], 500);
            }
        }
        
        return $next($request);
    }
}
