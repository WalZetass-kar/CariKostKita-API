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
        Schema::create('crash_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('exception_type')->nullable();
            $table->longText('stack_trace');
            $table->text('logs')->nullable();
            $table->string('status')->default('unresolved'); // unresolved, investigated, resolved
            $table->timestamps();

            $table->index('app_version');
            $table->index('status');
        });

        Schema::create('app_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_name')->nullable(); // search, view_detail, chat_start, survey_submit
            $table->string('event')->nullable();
            $table->string('kost_id')->nullable();
            $table->json('event_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_name');
            $table->index('event');
            $table->index('kost_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_events');
        Schema::dropIfExists('crash_reports');
    }
};
