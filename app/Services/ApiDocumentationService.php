<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class ApiDocumentationService
{
    /**
     * Get all registered API routes grouped with metadata.
     */
    public function getGroupedRoutes(): array
    {
        $allRoutes = Route::getRoutes()->getRoutes();
        $apiRoutes = [];
        $seen = [];

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                // To keep documentation concise, prioritize v1 route representation
                $methods = array_diff($route->methods(), ['HEAD']);
                $method = reset($methods);
                $key = $method.' '.$uri;

                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $apiRoutes[] = [
                        'method' => $method,
                        'uri' => '/'.$uri,
                        'name' => $route->getName(),
                        'action' => $route->getActionName(),
                        'middleware' => $route->gatherMiddleware(),
                    ];
                }
            }
        }

        // Define rich metadata mapping for each endpoint
        $metadata = $this->getEndpointMetadata();

        $grouped = [
            'App & Releases' => [],
            'Authentication' => [],
            'Users & Profile' => [],
            'Kost Catalog' => [],
            'Rooms' => [],
            'Surveys & Reviews' => [],
            'Favorites' => [],
            'Chat & Messages' => [],
            'Safety & Reports' => [],
            'Owner Properti' => [],
            'Admin Dashboard' => [],
            'System & Health' => [],
        ];

        foreach ($apiRoutes as $r) {
            // Check both verbatim URI and stripped /v1 uri for metadata match
            $cleanUri = preg_replace('#^/api/v1/#', '/api/', $r['uri']);
            $key = $r['method'].' '.$cleanUri;
            $meta = $metadata[$key] ?? $metadata[$r['method'].' '.$r['uri']] ?? $this->inferMetadata($r);

            $requiresAuth = false;
            $roles = 'Semua Pengguna';

            foreach ($r['middleware'] as $mw) {
                if (str_contains($mw, 'auth:sanctum') || str_contains($mw, 'Authenticate')) {
                    $requiresAuth = true;
                }
                if (str_starts_with($mw, 'role:')) {
                    $roles = str_replace('role:', '', $mw);
                }
            }

            $endpointData = [
                'method' => $r['method'],
                'uri' => $r['uri'],
                'auth' => $requiresAuth ? 'Bearer Token' : 'Public',
                'roles' => $roles,
                'description' => $meta['description'] ?? 'API Endpoint untuk '.$r['uri'],
                'parameters' => $meta['parameters'] ?? [],
                'request_body' => $meta['request_body'] ?? null,
                'response_example' => $meta['response_example'] ?? null,
                'error_example' => $meta['error_example'] ?? null,
            ];

            $category = $meta['category'] ?? $this->inferCategory($r['uri']);
            $grouped[$category][] = $endpointData;
        }

        return array_filter($grouped, fn ($items) => ! empty($items));
    }

    protected function inferCategory(string $uri): string
    {
        if (str_contains($uri, '/app/')) {
            return 'App & Releases';
        }
        if (str_contains($uri, '/register') || str_contains($uri, '/login') || str_contains($uri, '/logout')) {
            return 'Authentication';
        }
        if (str_contains($uri, '/user')) {
            return 'Users & Profile';
        }
        if (str_contains($uri, '/admin/')) {
            return 'Admin Dashboard';
        }
        if (str_contains($uri, '/owner/')) {
            return 'Owner Properti';
        }
        if (str_contains($uri, '/favorites')) {
            return 'Favorites';
        }
        if (str_contains($uri, '/chats')) {
            return 'Chat & Messages';
        }
        if (str_contains($uri, '/surveys') || str_contains($uri, '/reviews')) {
            return 'Surveys & Reviews';
        }
        if (str_contains($uri, '/safety') || str_contains($uri, '/reports')) {
            return 'Safety & Reports';
        }
        if (str_contains($uri, '/rooms')) {
            return 'Rooms';
        }
        if (str_contains($uri, '/health')) {
            return 'System & Health';
        }

        return 'Kost Catalog';
    }

    protected function inferMetadata(array $route): array
    {
        return [
            'category' => $this->inferCategory($route['uri']),
            'description' => 'Endpoint operasi '.$route['uri'],
        ];
    }

    protected function getEndpointMetadata(): array
    {
        return [
            // App & Releases
            'GET /api/app/latest-release' => [
                'category' => 'App & Releases',
                'description' => 'Mendapatkan informasi rilis APK Android terbaru, tautan unduhan langsung, dan changelog untuk Landing Page & Mobile auto-update.',
                'response_example' => [
                    'success' => true,
                    'message' => 'Informasi rilis aplikasi terbaru berhasil diambil',
                    'data' => [
                        'version_name' => 'v1.0.0-beta',
                        'version_code' => 1,
                        'apk_url' => 'https://github.com/g-423/KosKita/releases/download/v1.0.0-beta/CariKostKita-v1.0.0-beta.apk',
                        'file_size' => '18.4 MB',
                        'is_force_update' => false,
                        'changelog' => 'Rilis Beta Perdana CariKostKita Pekanbaru',
                    ],
                ],
            ],
            'GET /api/app/settings' => [
                'category' => 'App & Releases',
                'description' => 'Mendapatkan pengaturan platform publik: WhatsApp bantuan, WhatsApp pendaftaran pemilik, repo GitHub, dan daftar kecamatan Pekanbaru.',
            ],
            'GET /api/app/ads/active' => [
                'category' => 'App & Releases',
                'description' => 'Mengambil banner & pop-up iklan aktif untuk ditampilkan di beranda Mobile App (otomatis merekam impression).',
            ],
            'POST /api/app/ads/{id}/click' => [
                'category' => 'App & Releases',
                'description' => 'Mencatat event klik banner iklan untuk kalkulasi metrik CTR di Admin Dashboard.',
            ],
            'POST /api/app/crashes' => [
                'category' => 'App & Releases',
                'description' => 'Menerima rekaman crash aplikasi Android secara otomatis untuk monitoring stabilitas sistem.',
            ],
            'POST /api/app/events' => [
                'category' => 'App & Releases',
                'description' => 'Mencatat event analitik dari aplikasi (pencarian, buka detail kost, mulai chat, ajukan survei).',
            ],

            // Auth
            'POST /api/register' => [
                'category' => 'Authentication',
                'description' => 'Mendaftarkan pengguna baru sebagai pencari kost atau pemilik kost.',
                'request_body' => [
                    'name' => 'Budi Santoso (string, required)',
                    'email' => 'budi@example.com (string, email, unique, required)',
                    'password' => 'secret1234 (string, min: 8, required)',
                    'role' => 'pencari | pemilik (string, default: pencari)',
                    'phone' => '08123456789 (string, nullable)',
                ],
            ],
            'POST /api/login' => [
                'category' => 'Authentication',
                'description' => 'Melakukan autentikasi email & password dan mendapatkan Bearer token Sanctum.',
                'request_body' => [
                    'email' => 'budi@example.com (string, required)',
                    'password' => 'secret1234 (string, required)',
                ],
            ],
            'POST /api/logout' => [
                'category' => 'Authentication',
                'description' => 'Mencabut dan menghapus token autentikasi saat ini.',
            ],

            // Users & Profile
            'GET /api/user' => [
                'category' => 'Users & Profile',
                'description' => 'Mendapatkan profil pengguna terautentikasi lengkap dengan status verifikasi pemilik.',
            ],
            'PUT /api/user/profile' => [
                'category' => 'Users & Profile',
                'description' => 'Memperbarui nama, nomor HP, bio, alamat, dan foto profil pengguna.',
            ],
            'PUT /api/user/password' => [
                'category' => 'Users & Profile',
                'description' => 'Mengubah password pengguna saat ini dengan validasi password lama.',
            ],
            'DELETE /api/user/account' => [
                'category' => 'Users & Profile',
                'description' => 'Menghapus akun pengguna secara mandiri (syarat wajib Google Play Policy).',
            ],
            'POST /api/user/verification' => [
                'category' => 'Users & Profile',
                'description' => 'Mengunggah berkas verifikasi pemilik kost (KTP, selfie, dokumen kepemilikan) ke storage privat.',
            ],

            // Kost Catalog
            'GET /api/kosts' => [
                'category' => 'Kost Catalog',
                'description' => 'Menampilkan daftar kost publik terverifikasi (filter kecamatan, gender, harga, sorting termurah/terpopuler).',
            ],
            'GET /api/kosts/map' => [
                'category' => 'Kost Catalog',
                'description' => 'Pencarian kost interaktif berbasis radius koordinat (latitude, longitude, radius_km) di Pekanbaru.',
            ],
            'GET /api/kosts/{id}' => [
                'category' => 'Kost Catalog',
                'description' => 'Menampilkan detail lengkap properti kost, spesifikasi kamar, galeri foto, kontak WhatsApp, dan ulasan.',
            ],

            // Surveys & Reviews
            'POST /api/surveys' => [
                'category' => 'Surveys & Reviews',
                'description' => 'Pencari mengajukan jadwal kunjungan/survei langsung ke kost.',
            ],
            'GET /api/surveys' => [
                'category' => 'Surveys & Reviews',
                'description' => 'Melihat daftar jadwal survei yang telah diajukan pencari.',
            ],
            'PATCH /api/surveys/{id}/cancel' => [
                'category' => 'Surveys & Reviews',
                'description' => 'Membatalkan jadwal survei oleh pencari.',
            ],
            'POST /api/reviews' => [
                'category' => 'Surveys & Reviews',
                'description' => 'Mengirim rating (1-5) dan testimoni ulasan kost (otomatis memperbarui nilai rating_avg kost).',
            ],

            // Favorites
            'GET /api/favorites' => [
                'category' => 'Favorites',
                'description' => 'Mendapatkan daftar kost yang disimpan/difavoritkan oleh pengguna saat ini.',
            ],
            'POST /api/favorites/{kost}' => [
                'category' => 'Favorites',
                'description' => 'Menambahkan kost ke daftar simpanan favorit pengguna.',
            ],
            'DELETE /api/favorites/{kost}' => [
                'category' => 'Favorites',
                'description' => 'Menghapus kost dari daftar simpanan favorit pengguna.',
            ],

            // Chat & Messages
            'GET /api/chats' => [
                'category' => 'Chat & Messages',
                'description' => 'Menampilkan seluruh percakapan obrolan pengguna saat ini dengan counter unread & pesan terakhir.',
            ],
            'POST /api/chats' => [
                'category' => 'Chat & Messages',
                'description' => 'Memulai atau membuka percakapan obrolan langsung dengan pemilik kost.',
            ],
            'GET /api/chats/{chat}/messages' => [
                'category' => 'Chat & Messages',
                'description' => 'Mendapatkan riwayat pesan percakapan (otomatis mereset unread counter).',
            ],
            'POST /api/chats/{chat}/messages' => [
                'category' => 'Chat & Messages',
                'description' => 'Mengirim pesan teks baru ke lawan bicara di chat room.',
            ],
            'PATCH /api/chats/{chat}/read' => [
                'category' => 'Chat & Messages',
                'description' => 'Menandai seluruh pesan dalam obrolan telah dibaca.',
            ],

            // Safety
            'POST /api/safety/block' => [
                'category' => 'Safety & Reports',
                'description' => 'Memblokir pengguna lain di fitur perpesanan chat.',
            ],
            'POST /api/safety/report-user' => [
                'category' => 'Safety & Reports',
                'description' => 'Melaporkan pengguna/chat yang melanggar aturan ke admin.',
            ],
            'POST /api/reports/kost' => [
                'category' => 'Safety & Reports',
                'description' => 'Melaporkan iklan kost yang tidak valid/palsu ke tim moderasi.',
            ],

            // Owner
            'GET /api/owner/kosts' => [
                'category' => 'Owner Properti',
                'description' => 'Menampilkan daftar seluruh properti kost milik pemilik (aktif, menunggu verifikasi, ditolak).',
            ],
            'POST /api/owner/kosts' => [
                'category' => 'Owner Properti',
                'description' => 'Mendaftarkan properti kost baru milik pemilik (otomatis pending verifikasi).',
            ],
            'PUT /api/owner/kosts/{kost}' => [
                'category' => 'Owner Properti',
                'description' => 'Memperbarui data & spesifikasi kamar kost milik sendiri.',
            ],
            'DELETE /api/owner/kosts/{kost}' => [
                'category' => 'Owner Properti',
                'description' => 'Menghapus properti kost milik sendiri.',
            ],
            'POST /api/owner/kosts/{kost}/resubmit' => [
                'category' => 'Owner Properti',
                'description' => 'Mengajukan ulang verifikasi kost setelah melakukan perbaikan revisi.',
            ],
            'GET /api/owner/analytics' => [
                'category' => 'Owner Properti',
                'description' => 'Melihat ringkasan metrik 30 hari (total views, favorit, chat masuk, dan survei).',
            ],
            'GET /api/owner/surveys' => [
                'category' => 'Owner Properti',
                'description' => 'Melihat daftar permintaan survei masuk dari calon penyewa kost.',
            ],
            'PATCH /api/owner/surveys/{id}' => [
                'category' => 'Owner Properti',
                'description' => 'Mengonfirmasi, menolak, atau menandai survei telah selesai dilakukan.',
            ],

            // Admin
            'GET /api/admin/dashboard' => [
                'category' => 'Admin Dashboard',
                'description' => 'Mengambil seluruh metrik analitik platform, chart sebaran kecamatan, dan performa iklan untuk Admin-Gue.',
            ],
            'GET /api/admin/users' => [
                'category' => 'Admin Dashboard',
                'description' => 'Menampilkan daftar pengguna pencari kost beserta status aktif/nonaktif akun.',
            ],
            'PATCH /api/admin/users/{id}/status' => [
                'category' => 'Admin Dashboard',
                'description' => 'Mengaktifkan atau menonaktifkan (suspend) akun pengguna.',
            ],
            'GET /api/admin/owners' => [
                'category' => 'Admin Dashboard',
                'description' => 'Menampilkan daftar mitra pemilik kost beserta jumlah kost yang dimiliki.',
            ],
            'GET /api/admin/kosts' => [
                'category' => 'Admin Dashboard',
                'description' => 'Daftar semua kost untuk moderasi (filter status Menunggu Verifikasi, Aktif, Ditolak, Nonaktif).',
            ],
            'PATCH /api/admin/kosts/{id}/status' => [
                'category' => 'Admin Dashboard',
                'description' => 'Memoderasi kost: Menyetujui (Aktif), Menolak (dengan catatan revisi), atau Menonaktifkan.',
            ],
            'GET /api/admin/reports' => [
                'category' => 'Admin Dashboard',
                'description' => 'Daftar kanban laporan kost dari pengguna (Baru, Diproses, Selesai, Ditolak).',
            ],
            'PUT /api/admin/reports/{report}/status' => [
                'category' => 'Admin Dashboard',
                'description' => 'Menindaklanjuti dan mengubah status laporan kost.',
            ],
            'GET /api/admin/ads' => [
                'category' => 'Admin Dashboard',
                'description' => 'Manajemen materi promosi dan iklan banner/pop-up platform.',
            ],
            'POST /api/admin/ads' => [
                'category' => 'Admin Dashboard',
                'description' => 'Menambahkan iklan banner promosi baru.',
            ],
            'GET /api/admin/audit-logs' => [
                'category' => 'Admin Dashboard',
                'description' => 'Riwayat audit trail aktivitas administratif admin.',
            ],
            'GET /api/admin/releases' => [
                'category' => 'Admin Dashboard',
                'description' => 'Daftar rilis versi aplikasi Android.',
            ],
            'POST /api/admin/releases' => [
                'category' => 'Admin Dashboard',
                'description' => 'Mempublikasikan rilis APK aplikasi baru untuk Landing Page dan Mobile App.',
            ],
            'GET /api/admin/settings' => [
                'category' => 'Admin Dashboard',
                'description' => 'Mengambil seluruh variabel konfigurasi platform.',
            ],
            'PUT /api/admin/settings' => [
                'category' => 'Admin Dashboard',
                'description' => 'Memperbarui variabel konfigurasi platform.',
            ],

            // System
            'GET /api/health' => [
                'category' => 'System & Health',
                'description' => 'Memeriksa kesehatan sistem (API, PostgreSQL, Supabase Storage, Cache).',
            ],
        ];
    }
}
