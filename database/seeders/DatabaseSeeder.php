<?php

namespace Database\Seeders;

use App\Models\ApplicationRelease;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@carikostkita.com'],
            [
                'name' => 'Administrator CariKostKita',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'no_hp' => '081234567890',
                'verification_status' => 'verified',
                'is_active' => true,
            ]
        );
        $admin->profile()->firstOrCreate([], [
            'bio' => 'Admin Utama Sistem CariKostKita',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
        ]);

        // 2. Official Application Release (For Landing Page APK Download & Mobile Update Check)
        ApplicationRelease::firstOrCreate(
            ['version_name' => 'v1.0.0-beta'],
            [
                'version_code' => 1,
                'apk_url' => 'https://github.com/g-423/KosKita/releases/download/v1.0.0-beta/CariKostKita-v1.0.0-beta.apk',
                'file_size' => '18.4 MB',
                'min_android' => 'Android 8.0 (Oreo)',
                'is_force_update' => false,
                'is_latest' => true,
                'changelog' => "• Rilis Beta Perdana CariKostKita untuk wilayah Pekanbaru\n• Fitur pencarian kost berbasis radius & filter fasilitas lengkap\n• Fitur chat langsung dengan pemilik kost\n• Penjadwalan survei dan ulasan terverifikasi",
                'release_date' => now()->subDays(7)->toDateString(),
                'download_count' => 0,
            ]
        );

        // 3. Platform Settings
        PlatformSetting::set('app_name', 'CariKostKita', 'string', 'general', 'Nama Aplikasi');
        PlatformSetting::set('contact_whatsapp', '6281270008899', 'string', 'contact', 'Nomor WhatsApp Layanan Bantuan');
        PlatformSetting::set('owner_registration_whatsapp', '6281270008899', 'string', 'contact', 'Nomor WhatsApp Pendaftaran Mitra Pemilik');
        PlatformSetting::set('github_repo_url', 'https://github.com/g-423/KosKita', 'string', 'general', 'Repository GitHub Resmi');
        PlatformSetting::set('default_city', 'Pekanbaru', 'string', 'general', 'Kota Fokus Layanan');
        PlatformSetting::set('platform_maintenance_mode', false, 'boolean', 'general', 'Status Pemeliharaan Sistem');
    }
}
