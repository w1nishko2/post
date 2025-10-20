<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\TelegramBot;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VisitorStatistics;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateShopTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:generate-test-data {--user-id= : ID пользователя для которого создавать данные}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация реальных тестовых данных для магазина (товары, заказы, статистика)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if (!$userId) {
            $userId = $this->ask('Введите ID пользователя (или оставьте пустым для выбора первого)');
            if (!$userId) {
                $user = User::first();
                if (!$user) {
                    $this->error('Пользователи не найдены. Создайте сначала пользователя.');
                    return 1;
                }
                $userId = $user->id;
            }
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Пользователь с ID {$userId} не найден");
            return 1;
        }

        $this->info("Создание тестовых данных для пользователя: {$user->name} (ID: {$user->id})");

        // Создаем/получаем бота
        $bot = $this->ensureBotExists($user);
        
        // Создаем категории
        $categories = $this->createCategories($user, $bot);
        
        // Создаем товары
        $products = $this->createProducts($user, $bot, $categories);
        
        // Создаем заказы
        $orders = $this->createOrders($user, $bot, $products);
        
        // Создаем статистику посещений
        $this->createVisitorStatistics($user, $bot, $products);

        $this->info('✅ Тестовые данные успешно созданы!');
        $this->table(['Тип данных', 'Количество'], [
            ['Категории', $categories->count()],
            ['Товары', $products->count()], 
            ['Заказы', $orders->count()],
            ['Статистика посещений', 'Последние 30 дней']
        ]);

        return 0;
    }

    private function ensureBotExists($user)
    {
        $bot = $user->telegramBots()->first();
        
        if (!$bot) {
            $this->info('Создание Telegram бота...');
            $bot = TelegramBot::create([
                'user_id' => $user->id,
                'name' => 'Тестовый магазин',
                'username' => 'test_shop_bot',
                'token' => 'test_token_' . rand(100000, 999999),
                'short_name' => 'testshop',
                'is_active' => true,
            ]);
        }

        return $bot;
    }

    private function createCategories($user, $bot)
    {
        $this->info('Создание категорий...');
        
        $categoryNames = [
            'Электроника',
            'Одежда и аксессуары', 
            'Дом и сад',
            'Спорт и отдых',
            'Красота и здоровье',
            'Книги и канцелярия'
        ];

        $categories = collect();
        
        foreach ($categoryNames as $name) {
            $category = Category::firstOrCreate([
                'user_id' => $user->id,
                'telegram_bot_id' => $bot->id,
                'name' => $name,
            ], [
                'description' => "Категория {$name}",
                'is_active' => true,
            ]);
            
            $categories->push($category);
        }

        return $categories;
    }

    private function createProducts($user, $bot, $categories)
    {
        $this->info('Создание товаров...');
        
        $productData = [
            ['name' => 'iPhone 15 Pro', 'price' => 89990, 'category' => 'Электроника'],
            ['name' => 'Samsung Galaxy S24', 'price' => 79990, 'category' => 'Электроника'],
            ['name' => 'MacBook Air M2', 'price' => 129990, 'category' => 'Электроника'],
            ['name' => 'Джинсы Levi\'s', 'price' => 5990, 'category' => 'Одежда и аксессуары'],
            ['name' => 'Кроссовки Nike Air Max', 'price' => 12990, 'category' => 'Одежда и аксессуары'],
            ['name' => 'Футболка поло', 'price' => 2990, 'category' => 'Одежда и аксессуары'],
            ['name' => 'Кофеварка Delonghi', 'price' => 24990, 'category' => 'Дом и сад'],
            ['name' => 'Пылесос Dyson', 'price' => 34990, 'category' => 'Дом и сад'],
            ['name' => 'Велосипед горный', 'price' => 45990, 'category' => 'Спорт и отдых'],
            ['name' => 'Теннисная ракетка', 'price' => 8990, 'category' => 'Спорт и отдых'],
            ['name' => 'Крем для лица', 'price' => 1990, 'category' => 'Красота и здоровье'],
            ['name' => 'Витамины группы B', 'price' => 990, 'category' => 'Красота и здоровье'],
        ];

        $products = collect();
        
        foreach ($productData as $data) {
            $category = $categories->firstWhere('name', $data['category']);
            
            $product = Product::create([
                'user_id' => $user->id,
                'telegram_bot_id' => $bot->id,
                'category_id' => $category->id,
                'name' => $data['name'],
                'description' => "Описание товара {$data['name']}",
                'article' => 'ART-' . rand(10000, 99999),
                'price' => $data['price'],
                'quantity' => rand(5, 50),
                'markup_percentage' => rand(10, 30),
                'is_active' => true,
            ]);
            
            $products->push($product);
        }

        return $products;
    }

    private function createOrders($user, $bot, $products)
    {
        $this->info('Создание заказов...');
        
        $orders = collect();
        
        // Создаем заказы за последние 30 дней
        for ($i = 0; $i < 25; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 30));
            
            $order = Order::create([
                'user_id' => $user->id,
                'telegram_bot_id' => $bot->id,
                'session_id' => 'session_' . rand(100000, 999999),
                'telegram_chat_id' => rand(100000000, 999999999),
                'customer_name' => fake()->name(),
                'status' => fake()->randomElement([
                    Order::STATUS_COMPLETED,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_PENDING,
                    Order::STATUS_CANCELLED
                ]),
                'total_amount' => 0, // Пока 0, посчитаем после добавления товаров
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 24)),
            ]);

            // Добавляем товары в заказ
            $orderProducts = $products->random(rand(1, 3));
            $totalAmount = 0;
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price_with_markup;
                $totalPrice = $quantity * $price;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_article' => $product->article,
                    'product_photo_url' => $product->photo_url,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_price' => $totalPrice,
                ]);
                
                $totalAmount += $totalPrice;
            }
            
            // Обновляем общую сумму заказа
            $order->update(['total_amount' => $totalAmount]);
            $orders->push($order);
        }

        return $orders;
    }

    private function createVisitorStatistics($user, $bot, $products)
    {
        $this->info('Создание статистики посещений...');
        
        $sessionIds = [];
        for ($i = 0; $i < 50; $i++) {
            $sessionIds[] = 'session_' . rand(100000, 999999);
        }

        // Создаем записи посещений за последние 30 дней
        for ($i = 0; $i < 300; $i++) {
            $visitedAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Выбираем случайную страницу
            $pages = [
                '/',
                '/products',
                '/cart',
                '/about',
            ];
            
            // Добавляем страницы конкретных товаров
            foreach ($products->random(5) as $product) {
                $pages[] = "/products/{$product->id}";
                $pages[] = "/{$bot->short_name}/api/products/{$product->id}";
            }
            
            $pageUrl = config('app.url') . fake()->randomElement($pages);
            
            VisitorStatistics::create([
                'user_id' => $user->id,
                'telegram_bot_id' => $bot->id,
                'session_id' => fake()->randomElement($sessionIds),
                'telegram_chat_id' => fake()->boolean(40) ? rand(100000000, 999999999) : null,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'referer' => fake()->optional(0.6)->randomElement([
                    'https://google.com/search',
                    'https://yandex.ru/search', 
                    'https://t.me',
                    'https://vk.com',
                    null
                ]),
                'page_url' => $pageUrl,
                'visited_at' => $visitedAt,
                'created_at' => $visitedAt,
                'updated_at' => $visitedAt,
            ]);
        }
    }
}
