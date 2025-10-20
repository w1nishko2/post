<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TelegramBot;
use App\Models\VisitorStatistics;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TelegramBot $bot;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->bot = TelegramBot::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function statistics_page_requires_authentication()
    {
        $response = $this->get('/statistics');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_statistics()
    {
        $response = $this->actingAs($this->user)->get('/statistics');
        $response->assertOk();
        $response->assertViewIs('statistics.index');
    }

    /** @test */
    public function statistics_shows_only_user_data()
    {
        // Создаём данные текущего пользователя
        VisitorStatistics::factory()->create([
            'user_id' => $this->user->id,
            'telegram_bot_id' => $this->bot->id,
            'visited_at' => Carbon::today()
        ]);

        // Создаём данные другого пользователя
        $otherUser = User::factory()->create();
        $otherBot = TelegramBot::factory()->create(['user_id' => $otherUser->id]);
        VisitorStatistics::factory()->create([
            'user_id' => $otherUser->id,
            'telegram_bot_id' => $otherBot->id,
            'visited_at' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)->get('/statistics');
        
        $response->assertOk();
        
        // Проверяем, что в статистике только данные текущего пользователя
        $generalStats = $response->viewData('generalStats');
        $this->assertEquals(1, $generalStats['total_visits']);
    }

    /** @test */
    public function user_cannot_access_other_user_bot_statistics()
    {
        $otherUser = User::factory()->create();
        $otherBot = TelegramBot::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get("/statistics?bot_id={$otherBot->id}");

        $response->assertRedirect('/statistics');
        $response->assertSessionHas('error');
    }

    /** @test */
    public function chart_data_api_returns_valid_json()
    {
        VisitorStatistics::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'telegram_bot_id' => $this->bot->id,
            'visited_at' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)
            ->get('/statistics/chart-data?chart_type=visits');

        $response->assertOk();
        $response->assertJsonStructure([
            'labels',
            'visits',
            'unique_visitors'
        ]);
    }

    /** @test */
    public function statistics_handles_empty_data_gracefully()
    {
        $response = $this->actingAs($this->user)->get('/statistics');
        
        $response->assertOk();
        
        $generalStats = $response->viewData('generalStats');
        $this->assertEquals(0, $generalStats['total_visits']);
        $this->assertEquals(0, $generalStats['total_orders']);
        $this->assertEquals(0, $generalStats['total_revenue']);
    }

    /** @test */
    public function statistics_calculates_conversion_rate_correctly()
    {
        // Создаём 10 уникальных посетителей
        for ($i = 0; $i < 10; $i++) {
            VisitorStatistics::factory()->create([
                'user_id' => $this->user->id,
                'telegram_bot_id' => $this->bot->id,
                'session_id' => "session_{$i}",
                'visited_at' => Carbon::today()
            ]);
        }

        // Создаём 2 завершённых заказа
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'telegram_bot_id' => $this->bot->id,
            'status' => Order::STATUS_COMPLETED,
            'created_at' => Carbon::today()
        ]);

        $response = $this->actingAs($this->user)->get('/statistics');
        
        $generalStats = $response->viewData('generalStats');
        $this->assertEquals(20.0, $generalStats['conversion_rate']); // 2/10 * 100
    }

    /** @test */
    public function statistics_filters_by_period_correctly()
    {
        // Данные за сегодня
        VisitorStatistics::factory()->create([
            'user_id' => $this->user->id,
            'visited_at' => Carbon::today()
        ]);

        // Данные за прошлую неделю (не должны попасть в "сегодня")
        VisitorStatistics::factory()->create([
            'user_id' => $this->user->id,
            'visited_at' => Carbon::today()->subWeek()
        ]);

        $response = $this->actingAs($this->user)
            ->get('/statistics?period=today');
        
        $generalStats = $response->viewData('generalStats');
        $this->assertEquals(1, $generalStats['total_visits']);
    }
}