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
            // Удаляем поля, связанные с Яндекс.Диском
            $table->dropColumn(['yandex_disk_folder_url', 'photos_gallery', 'main_photo_index']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Восстанавливаем поля в случае отката миграции
            $table->text('yandex_disk_folder_url')->nullable()->after('photo_url');
            $table->json('photos_gallery')->nullable()->after('yandex_disk_folder_url');
            $table->unsignedTinyInteger('main_photo_index')->default(0)->after('photos_gallery');
        });
    }
};
