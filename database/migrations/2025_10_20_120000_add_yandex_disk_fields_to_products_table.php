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
        Schema::table('products', function (Blueprint $table) {
            // Ссылка на папку Яндекс.Диска
            $table->text('yandex_disk_folder_url')->nullable()->after('photo_url');
            
            // JSON массив с URL фотографий из папки Яндекс.Диска
            $table->json('photos_gallery')->nullable()->after('yandex_disk_folder_url');
            
            // Индекс главной фотографии в галерее (по умолчанию 0 - первая)
            $table->unsignedTinyInteger('main_photo_index')->default(0)->after('photos_gallery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'yandex_disk_folder_url',
                'photos_gallery',
                'main_photo_index'
            ]);
        });
    }
};