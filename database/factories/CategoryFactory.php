<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\TelegramBot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'telegram_bot_id' => TelegramBot::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'photo_url' => $this->faker->imageUrl(300, 300, 'categories'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }
}