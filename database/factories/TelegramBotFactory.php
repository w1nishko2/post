<?php

namespace Database\Factories;

use App\Models\TelegramBot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegramBotFactory extends Factory
{
    protected $model = TelegramBot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bot_name' => $this->faker->company . ' Bot',
            'bot_username' => $this->faker->userName . '_bot',
            'bot_token' => $this->faker->uuid,
            'api_id' => $this->faker->randomNumber(8),
            'api_hash' => $this->faker->md5,
            'webhook_url' => $this->faker->url,
            'mini_app_url' => $this->faker->url,
            'mini_app_short_name' => $this->faker->slug,
            'menu_button' => [
                'type' => 'web_app',
                'text' => 'Open Shop',
                'web_app' => ['url' => $this->faker->url]
            ],
            'commands' => [
                ['command' => 'start', 'description' => 'Start the bot'],
                ['command' => 'help', 'description' => 'Show help']
            ],
            'is_active' => true,
            'last_updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }
}