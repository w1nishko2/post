<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VisitorStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Проверяем, есть ли пользователи и боты для создания статистики
        $users = \App\Models\User::all();
        $bots = \App\Models\TelegramBot::all();
        
        if ($users->isEmpty()) {
            $this->command->info('Нет пользователей для создания статистики. Создайте сначала пользователей.');
            return;
        }

        $this->command->info('Создание тестовых данных статистики посещений...');

        // Создаем статистику для каждого пользователя
        foreach ($users as $user) {
            $userBots = $user->telegramBots;
            
            if ($userBots->isEmpty()) {
                // Если у пользователя нет ботов, создаем статистику без бота
                $this->createVisitorStatistics($user->id, null, 50);
            } else {
                // Создаем статистику для каждого бота пользователя
                foreach ($userBots as $bot) {
                    $this->createVisitorStatistics($user->id, $bot->id, 100);
                }
            }
        }

        $this->command->info('Тестовые данные статистики созданы успешно!');
    }

    /**
     * Создать статистику посещений для пользователя/бота
     */
    private function createVisitorStatistics($userId, $botId, $count)
    {
        $pages = [
            '/',
            '/products',
            '/products/1',
            '/products/2', 
            '/products/3',
            '/cart',
            '/orders',
            '/about',
        ];

        $referers = [
            null, // Прямой переход
            'https://google.com/search',
            'https://yandex.ru/search',
            'https://vk.com',
            'https://facebook.com',
            'https://t.me',
            'https://instagram.com',
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 14; Mobile; rv:119.0) Gecko/119.0 Firefox/119.0',
            'TelegramBot (like TwitterBot)',
        ];

        // Создаем несколько уникальных сессий
        $sessionIds = [];
        for ($i = 0; $i < rand(10, 25); $i++) {
            $sessionIds[] = fake()->uuid();
        }

        for ($i = 0; $i < $count; $i++) {
            $visitedAt = fake()->dateTimeBetween('-30 days', 'now');
            $sessionId = fake()->randomElement($sessionIds);
            $isTelegram = $botId && fake()->boolean(30); // 30% шанс что это Telegram

            \App\Models\VisitorStatistics::create([
                'user_id' => $userId,
                'telegram_bot_id' => $botId,
                'session_id' => $sessionId,
                'telegram_chat_id' => $isTelegram ? fake()->randomNumber(9, true) : null,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->randomElement($userAgents),
                'referer' => $isTelegram ? 'https://t.me' : fake()->optional(0.7)->randomElement($referers),
                'page_url' => config('app.url') . fake()->randomElement($pages),
                'visited_at' => $visitedAt,
                'created_at' => $visitedAt,
                'updated_at' => $visitedAt,
            ]);
        }
    }
}
