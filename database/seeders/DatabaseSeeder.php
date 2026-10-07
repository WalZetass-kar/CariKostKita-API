<?php

namespace Database\Seeders;

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
        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@carikostkita.com'],
            [
                'name' => 'Administrator CariKostKita',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
            ]
        );
        $admin->profile()->firstOrCreate([], [
            'bio' => 'Admin Utama Sistem',
            'city' => 'Jakarta',
            'district' => 'Pusat',
        ]);

        // 2. Pemilik Kost
        $pemilik = User::firstOrCreate(
            ['email' => 'pemilik@carikostkita.com'],
            [
                'name' => 'Juragan Kost Mawar',
                'password' => Hash::make('password123'),
                'role' => 'pemilik',
                'phone' => '081298765432',
            ]
        );
        $pemilik->profile()->firstOrCreate([], [
            'bio' => 'Pemilik Kost Mawar Asri',
            'city' => 'Pekanbaru',
            'district' => 'Bukit Raya',
        ]);

        // 3. Pencari Kost
        $pencari = User::firstOrCreate(
            ['email' => 'pencari@carikostkita.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'pencari',
                'phone' => '081311223344',
            ]
        );
        $pencari->profile()->firstOrCreate([], [
            'bio' => 'Mahasiswa Teknik Informatika',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
        ]);
    }
}
