<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOwnsStatistics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, что пользователь авторизован
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $botId = $request->get('bot_id');

        // Если указан bot_id, проверяем, что бот принадлежит пользователю
        if ($botId) {
            $bot = $user->telegramBots()->find($botId);
            if (!$bot) {
                // Если бот не принадлежит пользователю, перенаправляем на страницу без фильтра по боту
                return redirect()->route('statistics.index')->with('error', 'У вас нет доступа к статистике этого бота.');
            }
        }

        return $next($request);
    }
}
