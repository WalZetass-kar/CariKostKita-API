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
        Schema::create('application_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version_name'); // e.g. v1.0.0-beta
            $table->integer('version_code'); // e.g. 100
            $table->string('apk_url');
            $table->string('file_size')->nullable(); // e.g. 18.4 MB
            $table->string('min_android')->default('Android 8.0 (Oreo)');
            $table->boolean('is_force_update')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->text('changelog')->nullable();
            $table->date('release_date');
            $table->integer('download_count')->default(0);
            $table->timestamps();

            $table->index('version_name');
            $table->index('version_code');
            $table->index('is_latest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_releases');
    }
};
