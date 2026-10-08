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
        Schema::create('survey_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained('kosts')->cascadeOnDelete();
            $table->foreignId('pencari_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('survey_date');
            $table->string('status')->default('PENDING'); // PENDING, CONFIRMED, REJECTED, COMPLETED, CANCELLED
            $table->text('notes')->nullable();
            $table->text('owner_notes')->nullable();
            $table->timestamps();

            $table->index('kost_id');
            $table->index('pencari_id');
            $table->index('owner_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_requests');
    }
};
