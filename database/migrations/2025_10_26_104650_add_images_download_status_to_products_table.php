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
            // Статус загрузки изображений: null (не требуется), pending (в очереди), processing (загружаются), completed (загружено), partial (частично), failed (ошибка)
            $table->string('images_download_status', 20)->nullable()->after('is_active');
            
            // Сообщение об ошибке при загрузке изображений
            $table->text('images_download_error')->nullable()->after('images_download_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['images_download_status', 'images_download_error']);
        });
    }
};
