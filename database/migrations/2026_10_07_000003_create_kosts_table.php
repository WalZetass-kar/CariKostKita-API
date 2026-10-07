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
        Schema::create('kosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('province')->nullable();
            $table->string('city');
            $table->string('district');
            $table->string('village')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price_min', 12, 2)->default(0);
            $table->decimal('price_max', 12, 2)->default(0);
            $table->string('gender_type'); // putra, putri, campur
            $table->json('facilities')->nullable();
            $table->string('status')->default('available'); // available, full
            $table->string('verification_status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->index('owner_id');
            $table->index('city');
            $table->index('district');
            $table->index('gender_type');
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kosts');
    }
};
