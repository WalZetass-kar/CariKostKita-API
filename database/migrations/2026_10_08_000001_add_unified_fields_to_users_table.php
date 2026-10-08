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
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('avatar_url')->nullable()->after('avatar');
            $table->string('verification_status')->default('unverified')->after('role'); // unverified, pending, verified, rejected, revision_required
            $table->text('catatan_revisi')->nullable()->after('verification_status');
            $table->boolean('is_active')->default(true)->after('verification_status');
            $table->string('ktp_path')->nullable()->after('catatan_revisi');
            $table->string('selfie_path')->nullable()->after('ktp_path');
            $table->string('kepemilikan_path')->nullable()->after('selfie_path');
            $table->timestamp('pengajuan_at')->nullable()->after('kepemilikan_path');
            $table->string('auth_provider')->default('email')->after('remember_token'); // email, google
            $table->string('google_id')->nullable()->after('auth_provider');

            $table->index('verification_status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'no_hp',
                'bio',
                'avatar_url',
                'verification_status',
                'catatan_revisi',
                'is_active',
                'ktp_path',
                'selfie_path',
                'kepemilikan_path',
                'pengajuan_at',
                'auth_provider',
                'google_id',
            ]);
        });
    }
};
