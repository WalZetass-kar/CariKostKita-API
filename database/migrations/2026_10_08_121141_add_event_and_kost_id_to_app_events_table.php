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
        if (Schema::hasTable('app_events')) {
            Schema::table('app_events', function (Blueprint $table) {
                if (! Schema::hasColumn('app_events', 'event')) {
                    $table->string('event')->nullable()->index();
                }
                if (! Schema::hasColumn('app_events', 'kost_id')) {
                    $table->string('kost_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('app_events')) {
            Schema::table('app_events', function (Blueprint $table) {
                if (Schema::hasColumn('app_events', 'event')) {
                    $table->dropColumn('event');
                }
                if (Schema::hasColumn('app_events', 'kost_id')) {
                    $table->dropColumn('kost_id');
                }
            });
        }
    }
};
