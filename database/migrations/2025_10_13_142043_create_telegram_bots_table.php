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
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bot_name');
            $table->string('bot_username')->unique();
            $table->text('bot_token');
            $table->string('api_id')->nullable();
            $table->string('api_hash')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('mini_app_url')->nullable();
            $table->string('mini_app_short_name')->nullable();
            $table->json('menu_button')->nullable(); // Для настройки кнопки меню
            $table->json('commands')->nullable(); // Для хранения команд бота
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bots');
    }
};
