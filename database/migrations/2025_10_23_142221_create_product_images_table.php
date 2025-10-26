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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('file_path'); // Путь к оригинальному файлу
            $table->string('thumbnail_path'); // Путь к миниатюре 200x200 WebP
            $table->boolean('is_main')->default(false); // Главная фотография
            $table->integer('order')->default(0); // Порядок отображения
            $table->string('original_name')->nullable(); // Оригинальное имя файла
            $table->integer('file_size')->nullable(); // Размер файла в байтах
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index(['product_id', 'is_main']);
            $table->index(['product_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
