<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TelegramBot;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'telegram_bot_id' => TelegramBot::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'article' => strtoupper($this->faker->unique()->bothify('??###')),
            'photo_url' => $this->faker->imageUrl(400, 400, 'products'),
            'specifications' => [
                'weight' => $this->faker->randomFloat(2, 0.1, 10) . ' kg',
                'dimensions' => $this->faker->randomNumber(2) . 'x' . $this->faker->randomNumber(2) . 'x' . $this->faker->randomNumber(2) . ' cm',
                'material' => $this->faker->randomElement(['Plastic', 'Metal', 'Wood', 'Glass']),
            ],
            'quantity' => $this->faker->numberBetween(0, 100),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn() => ['quantity' => 0]);
    }
}