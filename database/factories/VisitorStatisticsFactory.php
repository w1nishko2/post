<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitorStatistics>
 */
class VisitorStatisticsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
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

        return [
            'user_id' => \App\Models\User::factory(),
            'telegram_bot_id' => \App\Models\TelegramBot::factory(),
            'session_id' => fake()->uuid(),
            'telegram_chat_id' => fake()->optional(0.3)->randomNumber(9, true),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->randomElement($userAgents),
            'referer' => fake()->optional(0.7)->randomElement($referers),
            'page_url' => fake()->url() . fake()->randomElement($pages),
            'visited_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
