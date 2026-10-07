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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kost_id')->constrained('kosts')->cascadeOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, resolved
            $table->timestamps();

            $table->index('reporter_id');
            $table->index('kost_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
