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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // Для неавторизованных пользователей
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Для авторизованных
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // Цена на момент добавления
            $table->timestamps();
            
            // Уникальный индекс для предотвращения дубликатов
            $table->unique(['session_id', 'product_id', 'user_id']);
            $table->index(['user_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
