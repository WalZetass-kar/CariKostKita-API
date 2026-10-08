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
        Schema::table('kosts', function (Blueprint $table) {
            $table->string('nama_kost')->nullable()->after('name');
            $table->string('no_whatsapp')->nullable()->after('address');
            $table->string('thumbnail_url')->nullable()->after('gender_type');
            $table->json('image_urls')->nullable()->after('thumbnail_url');
            $table->json('fasilitas')->nullable()->after('facilities');
            $table->json('aturan')->nullable()->after('fasilitas');
            $table->decimal('deposit', 12, 2)->default(0)->after('price_max');
            $table->string('ukuran_kamar')->nullable()->after('deposit');
            $table->integer('total_kamar')->default(1)->after('ukuran_kamar');
            $table->integer('kamar_tersedia')->default(1)->after('total_kamar');
            $table->decimal('rating_avg', 3, 2)->default(0.00)->after('kamar_tersedia');
            $table->integer('rating_count')->default(0)->after('rating_avg');
            $table->text('catatan_revisi')->nullable()->after('verification_status');
            $table->text('patokan')->nullable()->after('address');
            $table->string('location_verification')->default('UNVERIFIED')->after('patokan');
            $table->integer('views_count')->default(0)->after('rating_count');

            $table->index('rating_avg');
            $table->index('kamar_tersedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kosts', function (Blueprint $table) {
            $table->dropIndex(['rating_avg']);
            $table->dropIndex(['kamar_tersedia']);
            $table->dropColumn([
                'nama_kost',
                'no_whatsapp',
                'thumbnail_url',
                'image_urls',
                'fasilitas',
                'aturan',
                'deposit',
                'ukuran_kamar',
                'total_kamar',
                'kamar_tersedia',
                'rating_avg',
                'rating_count',
                'catatan_revisi',
                'patokan',
                'location_verification',
                'views_count',
            ]);
        });
    }
};
