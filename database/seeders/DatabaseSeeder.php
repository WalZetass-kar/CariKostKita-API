<?php

namespace Database\Seeders;

use App\Models\Kost;
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

        // 4. Sample Kost Approved
        $kost1 = Kost::firstOrCreate(
            ['name' => 'Kost Mawar Asri Eksklusif', 'owner_id' => $pemilik->id],
            [
                'description' => 'Kost nyaman, tenang, dan aman dekat kampus dan pusat kota. Dilengkapi fasilitas lengkap dan parkir luas.',
                'address' => 'Jl. Mawar No. 15, Tangkerang Labuai',
                'province' => 'Riau',
                'city' => 'Pekanbaru',
                'district' => 'Bukit Raya',
                'village' => 'Tangkerang Labuai',
                'latitude' => 0.48512340,
                'longitude' => 101.44567890,
                'price_min' => 750000,
                'price_max' => 1500000,
                'gender_type' => 'campur',
                'facilities' => ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Kasur Springbed', 'Lemari', 'Parkir Motor/Mobil'],
                'status' => 'available',
                'verification_status' => 'approved',
            ]
        );

        // Kamar untuk Kost 1
        $kost1->rooms()->firstOrCreate(
            ['name' => 'Kamar Tipe Standar AC'],
            [
                'room_type' => 'Standar',
                'price' => 850000,
                'size' => '3x3 meter',
                'facilities' => ['AC', 'Kasur', 'Lemari', 'Kamar Mandi Luar'],
                'total_rooms' => 8,
                'available_rooms' => 3,
                'status' => 'available',
            ]
        );

        $kost1->rooms()->firstOrCreate(
            ['name' => 'Kamar Tipe VIP KM Dalam'],
            [
                'room_type' => 'VIP',
                'price' => 1350000,
                'size' => '4x4 meter',
                'facilities' => ['AC', 'Kamar Mandi Dalam', 'Water Heater', 'Meja Belajar', 'Kasur Queen'],
                'total_rooms' => 4,
                'available_rooms' => 1,
                'status' => 'available',
            ]
        );

        // 5. Sample Kost Pending (untuk Admin review)
        Kost::firstOrCreate(
            ['name' => 'Kost Melati Indah Mahasiswa', 'owner_id' => $pemilik->id],
            [
                'description' => 'Kost baru dibuka khusus putra, dekat fasilitas umum.',
                'address' => 'Jl. Manyar Sakti No. 22',
                'province' => 'Riau',
                'city' => 'Pekanbaru',
                'district' => 'Tampan',
                'price_min' => 500000,
                'price_max' => 900000,
                'gender_type' => 'putra',
                'facilities' => ['WiFi', 'Dapur Bersama', 'Parkir Motor'],
                'status' => 'available',
                'verification_status' => 'pending',
            ]
        );
    }
}
