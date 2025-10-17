<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeUserAgentHeaders
{
    /**
     * Handle an incoming request.
     * 
     * Очищает проблемные заголовки User-Agent, которые могут содержать 
     * символы, конфликтующие с регулярными выражениями Laravel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Очищаем проблемные заголовки, которые могут содержать символы, 
        // конфликтующие с регулярными выражениями Laravel
        $headers = $request->headers;
        
        // Список проблемных заголовков для очистки
        $headersToClean = [
            'sec-ch-ua',
            'sec-ch-ua-mobile',
            'sec-ch-ua-platform',
            'sec-fetch-user',
            'user-agent'
        ];
        
        foreach ($headersToClean as $headerName) {
            if ($headers->has($headerName)) {
                $headerValue = $headers->get($headerName);
                // Более безопасная очистка: используем filter_var для удаления недопустимых символов
                $cleanValue = filter_var($headerValue, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
                
                // Дополнительно удаляем потенциально опасные символы, которые могут вызвать проблемы с regex
                $cleanValue = str_replace(['`', '\\', '|'], '', $cleanValue);
                
                // Ограничиваем длину заголовка
                $cleanValue = substr($cleanValue, 0, 255);
                
                $headers->set($headerName, $cleanValue);
            }
        }
        
        return $next($request);
    }
}