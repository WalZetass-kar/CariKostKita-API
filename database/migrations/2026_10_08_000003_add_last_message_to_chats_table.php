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
        Schema::table('chats', function (Blueprint $table) {
            $table->text('last_message')->nullable()->after('kost_id');
            $table->timestamp('last_message_at')->nullable()->after('last_message');
            $table->integer('unread_count_user')->default(0)->after('last_message_at');
            $table->integer('unread_count_owner')->default(0)->after('unread_count_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn([
                'last_message',
                'last_message_at',
                'unread_count_user',
                'unread_count_owner',
            ]);
        });
    }
};
