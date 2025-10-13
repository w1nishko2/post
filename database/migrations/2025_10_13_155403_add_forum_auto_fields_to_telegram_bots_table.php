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
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->string('forum_auto_login')->nullable()->after('mini_app_short_name');
            $table->text('forum_auto_pass')->nullable()->after('forum_auto_login');
            $table->boolean('forum_auto_enabled')->default(false)->after('forum_auto_pass');
            $table->timestamp('forum_auto_last_check')->nullable()->after('forum_auto_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->dropColumn([
                'forum_auto_login',
                'forum_auto_pass', 
                'forum_auto_enabled',
                'forum_auto_last_check'
            ]);
        });
    }
};
