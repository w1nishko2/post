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
        Schema::create('import_queue', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36)->index(); // UUID сессии импорта
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('telegram_bot_id')->constrained()->onDelete('cascade');
            
            // Данные из Excel строки (JSON)
            $table->json('row_data'); // Все данные из строки Excel
            
            // Настройки импорта
            $table->boolean('update_existing')->default(false);
            $table->boolean('download_images')->default(true);
            
            // Статус обработки
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable(); // Сообщение об ошибке
            $table->integer('attempts')->default(0); // Количество попыток
            $table->integer('max_attempts')->default(3); // Максимум попыток
            
            // ID созданного товара (после обработки)
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            // Временные метки
            $table->timestamp('processed_at')->nullable(); // Когда обработано
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['session_id', 'status']);
            $table->index(['status', 'attempts']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_queue');
    }
};
