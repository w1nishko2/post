<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserColorScheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Применяем цветовую схему только для HTML ответов и авторизованных пользователей
        if ($request->user() && 
            $response instanceof \Illuminate\Http\Response &&
            str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            
            $user = $request->user();
            $userColors = $user->getColorSchemeCss();
            
            if (!empty($userColors)) {
                $content = $response->getContent();
                
                // Создаем CSS переменные для пользовательской схемы
                $cssVars = ':root {' . PHP_EOL;
                foreach ($userColors as $property => $value) {
                    $cssVars .= "    {$property}: {$value};" . PHP_EOL;
                }
                $cssVars .= '}' . PHP_EOL;
                
                // Добавляем CSS в head перед закрывающим тегом </head>
                $customCss = "<style id=\"user-color-scheme\">{$cssVars}</style>";
                
                // Вставляем CSS в head
                if (str_contains($content, '</head>')) {
                    $content = str_replace('</head>', $customCss . '</head>', $content);
                } else {
                    // Если нет </head>, добавляем в начало body
                    $content = str_replace('<body', $customCss . '<body', $content);
                }
                
                $response->setContent($content);
            }
        }

        return $response;
    }
}
