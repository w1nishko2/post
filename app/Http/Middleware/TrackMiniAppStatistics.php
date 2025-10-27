<?php

namespace App\Http\Middleware;

use App\Models\TelegramBot;
use App\Models\VisitorStatistics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackMiniAppStatistics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Отслеживаем только успешные GET запросы к Mini App
        if ($request->isMethod('GET') && $response->getStatusCode() == 200) {
            $this->trackMiniAppVisit($request);
        }

        return $response;
    }

    /**
     * Отследить посещение Mini App
     */
    private function trackMiniAppVisit(Request $request)
    {
        try {
            $shortName = $request->route('shortName');
            if (!$shortName) {
                return;
            }

            // Находим бота по shortName
            $bot = TelegramBot::where('mini_app_short_name', $shortName)
                             ->where('is_active', true)
                             ->first();

            if (!$bot) {
                return;
            }

            // Извлекаем данные Telegram
            $telegramData = $this->extractTelegramData($request);

            // Создаем запись статистики
            VisitorStatistics::create([
                'user_id' => $bot->user_id,
                'telegram_bot_id' => $bot->id,
                'session_id' => $request->session()->getId(),
                'telegram_chat_id' => $telegramData['chat_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('Referer'),
                'page_url' => $request->fullUrl(),
                'visited_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при отслеживании Mini App: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
        }
    }

    /**
     * Извлечь данные Telegram из запроса
     */
    private function extractTelegramData(Request $request)
    {
        $data = [];

        // Из параметров URL
        if ($request->has('tgChatId')) {
            $data['chat_id'] = $request->get('tgChatId');
        }

        if ($request->has('chat_id')) {
            $data['chat_id'] = $request->get('chat_id');
        }

        // Из Telegram WebApp Init Data в заголовках
        $initData = $request->header('X-Telegram-Web-App-Init-Data') ?? $request->input('_auth');
        if ($initData) {
            parse_str($initData, $parsedData);
            if (isset($parsedData['user'])) {
                $userData = json_decode($parsedData['user'], true);
                if ($userData && isset($userData['id'])) {
                    $data['chat_id'] = $userData['id'];
                }
            }
        }

        // Логируем для отладки, если chat_id найден
        if (isset($data['chat_id'])) {
            Log::info('Mini App: Telegram Chat ID extracted', [
                'chat_id' => $data['chat_id'],
                'source' => $request->has('tgChatId') ? 'URL' : 'InitData',
                'url' => $request->fullUrl()
            ]);
        } else {
            Log::debug('Mini App: No Telegram Chat ID found', [
                'url' => $request->fullUrl(),
                'has_tgChatId' => $request->has('tgChatId'),
                'has_init_data' => !empty($initData)
            ]);
        }

        return $data;
    }
}
