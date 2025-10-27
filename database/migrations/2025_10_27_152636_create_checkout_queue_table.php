<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkout_queue', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36)->unique()->index(); // UUID сессии оформления
            
            // Данные пользователя и бота
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_cart_id')->nullable(); // Session ID корзины
            $table->bigInteger('telegram_user_id')->unsigned()->index(); // Telegram User ID
            $table->foreignId('telegram_bot_id')->constrained()->onDelete('cascade');
            
            // Данные корзины и заказа (JSON)
            $table->json('cart_data'); // Снимок корзины (товары + количество)
            $table->json('user_data'); // Данные пользователя из Telegram
            $table->text('notes')->nullable(); // Примечания к заказу
            
            // Статус обработки
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            
            // ID созданного заказа (после обработки)
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            
            // Временные метки
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['status', 'attempts']);
            $table->index(['telegram_user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_queue');
    }
};
