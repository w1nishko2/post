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
            $table->json('photos_gallery')->nullable()->after('photo_url')->comment('Галерея фотографий из Яндекс.Диска');
            $table->integer('main_photo_index')->default(0)->after('photos_gallery')->comment('Индекс главной фотографии в галерее');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['photos_gallery', 'main_photo_index']);
        });
    }
};
