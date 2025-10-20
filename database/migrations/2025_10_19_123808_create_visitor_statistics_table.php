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
        Schema::create('visitor_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('telegram_bot_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->index();
            $table->string('telegram_chat_id')->nullable()->index();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->text('page_url');
            $table->timestamp('visited_at')->index();
            $table->timestamps();
            
            // Индексы для оптимизации запросов статистики
            $table->index(['user_id', 'visited_at']);
            $table->index(['telegram_bot_id', 'visited_at']);
            $table->index(['session_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_statistics');
    }
};
