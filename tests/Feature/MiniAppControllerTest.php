<?php

namespace Tests\Feature;

use App\Models\TelegramBot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MiniAppControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $bot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->bot = TelegramBot::factory()->create([
            'user_id' => $this->user->id,
            'mini_app_short_name' => 'testbot',
            'is_active' => true,
            'bot_token' => 'test_token'
        ]);

        // Мокируем config для получения токена
        config(['services.telegram.bot_token' => 'test_token']);
    }

    /** @test */
    public function it_validates_telegram_webapp_data_with_correct_hash()
    {
        $testData = [
            'user' => '{"id":123456,"first_name":"Test","username":"testuser"}',
            'auth_date' => time(),
        ];

        // Создаем корректную подпись
        ksort($testData);
        $data_check_string = implode("\n", array_map(fn($k, $v) => "$k=$v", array_keys($testData), $testData));
        $secret_key = hash('sha256', 'test_token', true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        
        $initData = http_build_query(array_merge($testData, ['hash' => $hash]));

        $response = $this->postJson("/testbot/api/user-data", [], [
            'X-Telegram-Web-App-Init-Data' => $initData
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_telegram_webapp_data_with_invalid_hash()
    {
        $testData = [
            'user' => '{"id":123456,"first_name":"Test","username":"testuser"}',
            'auth_date' => time(),
            'hash' => 'invalid_hash'
        ];

        $initData = http_build_query($testData);

        $response = $this->postJson("/testbot/api/user-data", [], [
            'X-Telegram-Web-App-Init-Data' => $initData
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_rejects_old_telegram_webapp_data()
    {
        $testData = [
            'user' => '{"id":123456,"first_name":"Test","username":"testuser"}',
            'auth_date' => time() - (25 * 60 * 60), // 25 часов назад
        ];

        ksort($testData);
        $data_check_string = implode("\n", array_map(fn($k, $v) => "$k=$v", array_keys($testData), $testData));
        $secret_key = hash('sha256', 'test_token', true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        $initData = http_build_query(array_merge($testData, ['hash' => $hash]));

        $response = $this->postJson("/testbot/api/user-data", [], [
            'X-Telegram-Web-App-Init-Data' => $initData
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function validate_cart_optimizes_database_queries()
    {
        $products = \App\Models\Product::factory(3)->create([
            'telegram_bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'quantity' => 10,
            'price' => 100
        ]);

        $cartItems = $products->map(fn($p) => ['id' => $p->id, 'quantity' => 2])->toArray();

        // Включаем отслеживание запросов
        DB::enableQueryLog();

        $response = $this->postJson("/testbot/api/validate-cart", [
            'cart' => $cartItems
        ]);

        $queries = DB::getQueryLog();
        
        // Должно быть не более 3 запросов: 1 для поиска бота, 1 для товаров, возможно 1 для связанных данных
        $this->assertLessThanOrEqual(3, count($queries));

        $response->assertStatus(200)
                 ->assertJson([
                     'has_issues' => false,
                     'total_amount' => 600 // 3 товара * 2 шт * 100 руб
                 ]);
    }

    /** @test */
    public function validate_cart_handles_insufficient_quantity()
    {
        $product = \App\Models\Product::factory()->create([
            'telegram_bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'price' => 100
        ]);

        $response = $this->postJson("/testbot/api/validate-cart", [
            'cart' => [
                ['id' => $product->id, 'quantity' => 10] // Запрашиваем больше чем есть
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'has_issues' => true,
                     'issues' => [
                         [
                             'type' => 'insufficient_quantity',
                             'product_id' => $product->id,
                             'requested' => 10,
                             'available' => 5
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function validate_cart_handles_inactive_products()
    {
        $product = \App\Models\Product::factory()->create([
            'telegram_bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'is_active' => false
        ]);

        $response = $this->postJson("/testbot/api/validate-cart", [
            'cart' => [
                ['id' => $product->id, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'has_issues' => true,
                     'issues' => [
                         [
                             'type' => 'product_inactive',
                             'product_id' => $product->id
                         ]
                     ]
                 ]);
    }
}