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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained('kosts')->cascadeOnDelete();
            $table->string('name');
            $table->string('room_type')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('size')->nullable();
            $table->json('facilities')->nullable();
            $table->integer('total_rooms')->default(1);
            $table->integer('available_rooms')->default(1);
            $table->string('status')->default('available');
            $table->timestamps();

            $table->index('kost_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
