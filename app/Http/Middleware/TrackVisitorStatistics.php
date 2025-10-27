<?php

namespace App\Http\Middleware;

use App\Models\VisitorStatistics;
use App\Models\TelegramBot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class TrackVisitorStatistics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Отслеживаем только GET запросы и только успешные ответы
        if ($request->isMethod('GET') && $response->getStatusCode() == 200) {
            $this->trackVisit($request);
        }

        return $response;
    }

    /**
     * Отследить посещение
     */
    private function trackVisit(Request $request)
    {
        try {
            // Получаем информацию о пользователе и боте
            $userId = Auth::id();
            $botId = $this->getBotIdFromRequest($request);
            $telegramChatId = $this->getTelegramChatIdFromRequest($request);

            // Пропускаем административные страницы, API и статичные ресурсы
            if ($this->shouldSkipTracking($request)) {
                return;
            }

            // Создаем запись о посещении
            VisitorStatistics::create([
                'user_id' => $userId,
                'telegram_bot_id' => $botId,
                'session_id' => $request->session()->getId(),
                'telegram_chat_id' => $telegramChatId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('Referer'),
                'page_url' => $request->fullUrl(),
                'visited_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::error('Ошибка при отслеживании посещения: ' . $e->getMessage());
        }
    }

    /**
     * Получить ID бота из запроса
     */
    private function getBotIdFromRequest(Request $request)
    {
        // Если в URL есть параметр бота
        if ($request->route('telegramBot')) {
            return $request->route('telegramBot')->id;
        }

        // Если это Mini App, определяем бота по shortName
        if ($request->route('shortName')) {
            $bot = TelegramBot::where('mini_app_short_name', $request->route('shortName'))->first();
            return $bot ? $bot->id : null;
        }

        // Пытаемся найти в сессии последний используемый бот
        if ($request->session()->has('last_bot_id')) {
            return $request->session()->get('last_bot_id');
        }

        return null;
    }

    /**
     * Получить ID чата Telegram из запроса
     */
    private function getTelegramChatIdFromRequest(Request $request)
    {
        // Для Mini App из query параметров
        if ($request->has('tgChatId')) {
            return $request->get('tgChatId');
        }

        // Из сессии (если пользователь пришел через Telegram)
        if ($request->session()->has('telegram_chat_id')) {
            return $request->session()->get('telegram_chat_id');
        }

        return null;
    }

    /**
     * Проверить, нужно ли пропустить отслеживание для данного запроса
     */
    private function shouldSkipTracking(Request $request)
    {
        $path = $request->path();
        
        // Пропускаем Mini App (у него свой middleware)
        // Проверяем, является ли это запросом к Mini App
        if ($this->isMiniAppRequest($request)) {
            return true;
        }
        
        // Пропускаем административные страницы
        $skipPaths = [
            'login',
            'register',
            'password',
            'admin',
            'statistics',
            'telescope',
            '_debugbar',
            'horizon',
            'image-proxy', // Служебные запросы изображений
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }
        
        // Пропускаем служебные AJAX запросы корзины
        $skipAjaxPaths = [
            'cart/count',
            'cart/get-data',
        ];
        
        foreach ($skipAjaxPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }
        
        // Разрешаем API запросы Mini App (они начинаются с shortName/api)
        if (preg_match('/^[a-zA-Z0-9_]+\/api\//', $path)) {
            return false; // НЕ пропускаем, отслеживаем эти запросы
        }
        
        // Пропускаем остальные API запросы
        if (str_starts_with($path, 'api/')) {
            return true;
        }

        // Пропускаем статичные ресурсы
        $staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf'];
        foreach ($staticExtensions as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }

        // Пропускаем AJAX запросы (кроме основных страниц)
        if ($request->ajax() && !$this->isMainPageAjax($request)) {
            return true;
        }

        return false;
    }

    /**
     * Проверить, является ли запрос к Mini App
     */
    private function isMiniAppRequest(Request $request)
    {
        $path = $request->path();
        
        // Проверяем паттерн Mini App API: shortName/api/*
        if (preg_match('/^[a-zA-Z0-9_]+\/api\//', $path)) {
            return true;
        }
        
        // Проверяем основную страницу Mini App: только shortName
        // Исключаем известные роуты приложения
        $knownRoutes = [
            'home', 'login', 'register', 'password', 'profile', 'products', 'orders', 
            'cart', 'categories', 'bots', 'telegram-bots', 'statistics', 'admin'
        ];
        
        if (!str_contains($path, '/')) {
            // Это одиночное слово - может быть shortName
            if (!in_array($path, $knownRoutes)) {
                // Проверяем, есть ли роут с именем mini-app.show
                if ($request->route() && $request->route()->getName() === 'mini-app.show') {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Проверить, является ли AJAX запрос запросом основной страницы
     */
    private function isMainPageAjax(Request $request)
    {
        // Можем отслеживать некоторые важные AJAX запросы
        $allowedAjaxPaths = [
            'cart/add',
            'products',
        ];

        $path = $request->path();
        foreach ($allowedAjaxPaths as $allowedPath) {
            if (str_contains($path, $allowedPath)) {
                return true;
            }
        }

        return false;
    }
}
