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

        // 4. Sample Kost Approved #1
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
                'facilities' => ['WiFi Cepat', 'AC', 'Kamar Mandi Dalam', 'Kasur Springbed', 'Lemari Pakaian', 'Parkir Mobil/Motor', 'CCTV 24 Jam'],
                'status' => 'available',
                'verification_status' => 'approved',
            ]
        );

        $kost1->rooms()->firstOrCreate(
            ['name' => 'Kamar Tipe Standar AC'],
            [
                'room_type' => 'Standar AC',
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

        // 5. Sample Kost Approved #2
        $kost2 = Kost::firstOrCreate(
            ['name' => 'Kost Putri Muslimah Melati', 'owner_id' => $pemilik->id],
            [
                'description' => 'Khusus putri mahasiswi/karyawati. Lingkungan sangat asri, aman dengan gerbang otomatis, dekat kampus UNRI & UIN Suska.',
                'address' => 'Jl. Manyar Sakti KM 2, Simpang Baru',
                'province' => 'Riau',
                'city' => 'Pekanbaru',
                'district' => 'Tampan',
                'village' => 'Simpang Baru',
                'latitude' => 0.47120000,
                'longitude' => 101.37890000,
                'price_min' => 600000,
                'price_max' => 1100000,
                'gender_type' => 'putri',
                'facilities' => ['WiFi', 'Dapur Bersama', 'Kulkas Bersama', 'Kamar Mandi Dalam', 'Penjaga 24 Jam', 'Ruang Tamu'],
                'status' => 'available',
                'verification_status' => 'approved',
            ]
        );

        $kost2->rooms()->firstOrCreate(
            ['name' => 'Kamar Putri Reguler'],
            [
                'room_type' => 'Reguler Non-AC',
                'price' => 650000,
                'size' => '3x3.5 meter',
                'facilities' => ['Kipas Angin', 'Kasur Busa Tebal', 'Lemari 2 Pintu', 'KM Luar'],
                'total_rooms' => 10,
                'available_rooms' => 4,
                'status' => 'available',
            ]
        );

        $kost2->rooms()->firstOrCreate(
            ['name' => 'Kamar Putri Deluxe AC'],
            [
                'room_type' => 'Deluxe AC',
                'price' => 1050000,
                'size' => '3.5x4 meter',
                'facilities' => ['AC', 'KM Dalam', 'Kasur Single Springbed', 'Meja Rias', 'WiFi'],
                'total_rooms' => 6,
                'available_rooms' => 2,
                'status' => 'available',
            ]
        );

        // 6. Sample Kost Approved #3
        $kost3 = Kost::firstOrCreate(
            ['name' => 'Cendana Executive Residence Putra', 'owner_id' => $pemilik->id],
            [
                'description' => 'Kost eksekutif pria dengan suasana modern minimalis. Dekat bandara SSK II dan pusat perbelanjaan Sudirman.',
                'address' => 'Jl. Cendana No. 88, Sidomulyo Timur',
                'province' => 'Riau',
                'city' => 'Pekanbaru',
                'district' => 'Marpoyan Damai',
                'village' => 'Sidomulyo Timur',
                'latitude' => 0.46340000,
                'longitude' => 101.43210000,
                'price_min' => 1100000,
                'price_max' => 2200000,
                'gender_type' => 'putra',
                'facilities' => ['WiFi 100Mbps', 'AC Inverter', 'Smart TV', 'Water Heater', 'Housekeeping', 'Laundry', 'Parkir Mobil Luas'],
                'status' => 'available',
                'verification_status' => 'approved',
            ]
        );

        $kost3->rooms()->firstOrCreate(
            ['name' => 'Studio Suite Room'],
            [
                'room_type' => 'Studio Suite',
                'price' => 1650000,
                'size' => '4x5 meter',
                'facilities' => ['AC', 'Smart TV', 'Water Heater', 'Balkon Pribadi', 'Workdesk Ergonomis'],
                'total_rooms' => 5,
                'available_rooms' => 2,
                'status' => 'available',
            ]
        );

        // 7. Sample Kost Pending (untuk Admin review)
        Kost::firstOrCreate(
            ['name' => 'Kost Flamboyan Pending Review', 'owner_id' => $pemilik->id],
            [
                'description' => 'Kost baru didaftarkan, menunggu verifikasi oleh tim admin CariKostKita.',
                'address' => 'Jl. Flamboyan No. 7, Sukajadi',
                'province' => 'Riau',
                'city' => 'Pekanbaru',
                'district' => 'Sukajadi',
                'price_min' => 500000,
                'price_max' => 900000,
                'gender_type' => 'putra',
                'facilities' => ['WiFi', 'Parkir Motor'],
                'status' => 'available',
                'verification_status' => 'pending',
            ]
        );
    }
}
