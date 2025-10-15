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
            $table->foreignId('telegram_bot_id')->nullable()->constrained()->onDelete('set null')->after('user_id');
            $table->index(['user_id', 'telegram_bot_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['telegram_bot_id']);
            $table->dropIndex(['user_id', 'telegram_bot_id', 'is_active']);
            $table->dropColumn('telegram_bot_id');
        });
    }
};
