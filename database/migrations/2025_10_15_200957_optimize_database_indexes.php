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
        Schema::table('orders', function (Blueprint $table) {
            // Индекс для быстрого поиска недавних заказов по telegram_chat_id
            $table->index(['telegram_chat_id', 'created_at'], 'orders_telegram_chat_created_idx');
            
            // Индекс для order_number (уникальный)
            $table->index('order_number', 'orders_order_number_idx');
            
            // Составной индекс для быстрых выборок по боту и статусу
            $table->index(['telegram_bot_id', 'status', 'created_at'], 'orders_bot_status_created_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            // Оптимизируем существующие индексы
            $table->index(['user_id', 'telegram_bot_id', 'is_active', 'quantity'], 'products_user_bot_active_qty_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            // Составной индекс для быстрой очистки корзины
            $table->index(['session_id', 'user_id', 'telegram_user_id'], 'carts_session_user_telegram_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_telegram_chat_created_idx');
            $table->dropIndex('orders_order_number_idx');
            $table->dropIndex('orders_bot_status_created_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_user_bot_active_qty_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_session_user_telegram_idx');
        });
    }
};
