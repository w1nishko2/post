<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\TelegramBot;

class TestTimezoneSettings extends Command
{
    protected $signature = 'test:timezone';
    protected $description = 'Тестирует корректность настроек времени в московском часовом поясе';

    public function handle()
    {
        $this->info('=== Проверка настроек времени (МСК) ===');
        
        // 1. Проверяем конфигурацию приложения
        $this->info('1. Проверка конфигурации приложения:');
        $appTimezone = config('app.timezone');
        $this->line("   Часовой пояс приложения: {$appTimezone}");
        
        if ($appTimezone === 'Europe/Moscow') {
            $this->info("   ✅ Часовой пояс корректно настроен на Москву");
        } else {
            $this->error("   ❌ Часовой пояс должен быть 'Europe/Moscow', а не '{$appTimezone}'");
        }
        
        // 2. Проверяем текущее время
        $this->info('2. Проверка текущего времени:');
        $utcNow = Carbon::now('UTC');
        $moscowNow = Carbon::now('Europe/Moscow');
        $appNow = Carbon::now();
        
        $this->line("   UTC время:      {$utcNow->format('Y-m-d H:i:s T')}");
        $this->line("   Московское:     {$moscowNow->format('Y-m-d H:i:s T')}");
        $this->line("   Приложение:     {$appNow->format('Y-m-d H:i:s T')}");
        
        // Проверяем, что приложение использует московское время
        if ($appNow->format('H:i') === $moscowNow->format('H:i')) {
            $this->info("   ✅ Приложение использует московское время");
        } else {
            $this->error("   ❌ Время приложения не соответствует московскому");
        }
        
        // 3. Проверяем базу данных
        $this->info('3. Проверка базы данных:');
        try {
            // Получаем настройки времени базы данных
            $dbTimezone = DB::select("SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz")[0];
            $this->line("   Глобальный часовой пояс БД: {$dbTimezone->global_tz}");
            $this->line("   Сессионный часовой пояс БД: {$dbTimezone->session_tz}");
            
            // Проверяем время в БД
            $dbTime = DB::selectOne("SELECT NOW() as db_time");
            $this->line("   Время в БД: {$dbTime->db_time}");
            
            if (strpos($dbTimezone->session_tz, '+03:00') !== false || 
                strpos($dbTimezone->session_tz, 'Europe/Moscow') !== false) {
                $this->info("   ✅ База данных настроена на московское время");
            } else {
                $this->warn("   ⚠️  База данных может не использовать московское время");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Ошибка проверки БД: " . $e->getMessage());
        }
        
        // 4. Тестируем создание записей
        $this->info('4. Тест создания записей:');
        
        // Находим пользователя для теста
        $user = User::first();
        if (!$user) {
            $this->error("   ❌ Пользователь для теста не найден");
            return;
        }
        
        $bot = $user->telegramBots()->first();
        if (!$bot) {
            $this->error("   ❌ Бот для теста не найден");
            return;
        }
        
        // Создаем тестовый товар
        $product = Product::create([
            'user_id' => $user->id,
            'telegram_bot_id' => $bot->id,
            'name' => 'Тестовый товар для проверки времени',
            'article' => 'TEST_TIME_' . time(),
            'price' => 100,
            'quantity' => 10,
            'is_active' => true
        ]);
        
        $this->line("   Создан тестовый товар: {$product->name}");
        $this->line("   Время создания (UTC): {$product->created_at->utc()->format('Y-m-d H:i:s T')}");
        $this->line("   Время создания (МСК): {$product->created_at->setTimezone('Europe/Moscow')->format('Y-m-d H:i:s T')}");
        $this->line("   Время создания (локальное): {$product->formatted_created_at}");
        
        // Создаем тестовый заказ
        $order = Order::create([
            'user_id' => $user->id,
            'telegram_bot_id' => $bot->id,
            'customer_name' => 'Тест МСК времени',
            'total_amount' => 100,
            'status' => Order::STATUS_PENDING,
            'expires_at' => Carbon::now('Europe/Moscow')->addHours(5)
        ]);
        
        $this->line("   Создан тестовый заказ: {$order->order_number}");
        $this->line("   Время создания: {$order->formatted_created_at}");
        $this->line("   Время истечения: {$order->formatted_expires_at}");
        $this->line("   До истечения: {$order->time_until_expiration}");
        
        // 5. Проверяем Scopes с датами
        $this->info('5. Проверка фильтрации по датам:');
        
        $todayProducts = Product::createdTodayMoscow()->count();
        $thisWeekProducts = Product::createdThisWeekMoscow()->count();
        $thisMonthProducts = Product::createdThisMonthMoscow()->count();
        
        $this->line("   Товаров создано сегодня (МСК): {$todayProducts}");
        $this->line("   Товаров создано на этой неделе (МСК): {$thisWeekProducts}");
        $this->line("   Товаров создано в этом месяце (МСК): {$thisMonthProducts}");
        
        // 6. Проверяем истекшие заказы
        $this->info('6. Проверка истекших заказов:');
        
        $expiredOrders = Order::expired()->count();
        $pendingOrders = Order::pendingPayment()->count();
        
        $this->line("   Истекших заказов: {$expiredOrders}");
        $this->line("   Ожидающих оплаты: {$pendingOrders}");
        
        // 7. Очистка тестовых данных
        if ($this->confirm('Удалить тестовые данные?', true)) {
            $product->delete();
            $order->delete();
            $this->info("   ✅ Тестовые данные удалены");
        }
        
        // 8. Итоговая сводка
        $this->info('7. Итоговая проверка:');
        
        $allGood = true;
        
        if ($appTimezone !== 'Europe/Moscow') {
            $this->error("   ❌ Исправьте timezone в config/app.php");
            $allGood = false;
        }
        
        if ($appNow->format('H:i') !== $moscowNow->format('H:i')) {
            $this->error("   ❌ Время приложения не соответствует московскому");
            $allGood = false;
        }
        
        if ($allGood) {
            $this->info("   ✅ Все настройки времени корректны!");
        } else {
            $this->error("   ❌ Обнаружены проблемы с настройками времени");
        }
        
        $this->info('=== Проверка завершена ===');
    }
}