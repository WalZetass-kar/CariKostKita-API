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

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                $methods = array_diff($route->methods(), ['HEAD']);
                $method = reset($methods);

                $apiRoutes[] = [
                    'method' => $method,
                    'uri' => '/'.$uri,
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->gatherMiddleware(),
                ];
            }
        }

        // Define rich metadata mapping for each endpoint
        $metadata = $this->getEndpointMetadata();

        $grouped = [
            'Authentication' => [],
            'Users & Profile' => [],
            'Kost' => [],
            'Rooms' => [],
            'Favorites' => [],
            'Chat & Messages' => [],
            'Admin' => [],
            'System' => [],
        ];

        foreach ($apiRoutes as $r) {
            $key = $r['method'].' '.$r['uri'];
            $meta = $metadata[$key] ?? $this->inferMetadata($r);

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
                'description' => $meta['description'] ?? 'API Endpoint',
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
        if (str_contains($uri, '/register') || str_contains($uri, '/login') || str_contains($uri, '/logout')) {
            return 'Authentication';
        }
        if (str_contains($uri, '/user')) {
            return 'Users & Profile';
        }
        if (str_contains($uri, '/admin/')) {
            return 'Admin';
        }
        if (str_contains($uri, '/favorites')) {
            return 'Favorites';
        }
        if (str_contains($uri, '/chats')) {
            return 'Chat & Messages';
        }
        if (str_contains($uri, '/rooms')) {
            return 'Rooms';
        }
        if (str_contains($uri, '/kosts')) {
            return 'Kost';
        }
        if (str_contains($uri, '/health')) {
            return 'System';
        }

        return 'Kost';
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
                'response_example' => [
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'token' => '1|sanctum_plain_text_token',
                        'user' => [
                            'id' => 1,
                            'name' => 'Budi Santoso',
                            'email' => 'budi@example.com',
                            'role' => 'pencari',
                        ],
                    ],
                ],
                'error_example' => [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['email' => ['Email sudah terdaftar.']],
                ],
            ],
            'POST /api/login' => [
                'category' => 'Authentication',
                'description' => 'Melakukan autentikasi email & password dan mendapatkan Bearer token Sanctum.',
                'request_body' => [
                    'email' => 'budi@example.com (string, required)',
                    'password' => 'secret1234 (string, required)',
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Login berhasil',
                    'data' => [
                        'token' => '2|token_jwt_bearer',
                        'user' => ['id' => 1, 'email' => 'budi@example.com', 'role' => 'pencari'],
                    ],
                ],
            ],
            'POST /api/logout' => [
                'category' => 'Authentication',
                'description' => 'Mencabut dan menghapus token autentikasi saat ini.',
                'response_example' => [
                    'success' => true,
                    'message' => 'Logout berhasil',
                    'data' => null,
                ],
            ],
            'GET /api/user' => [
                'category' => 'Users & Profile',
                'description' => 'Mendapatkan data akun dan profil pengguna yang sedang login.',
                'response_example' => [
                    'success' => true,
                    'message' => 'Data pengguna berhasil diambil',
                    'data' => [
                        'id' => 1,
                        'name' => 'Budi Santoso',
                        'email' => 'budi@example.com',
                        'profile' => ['bio' => 'Mahasiswa', 'city' => 'Pekanbaru'],
                    ],
                ],
            ],
            'PUT /api/user/profile' => [
                'category' => 'Users & Profile',
                'description' => 'Memperbarui nama, nomor telepon, avatar, bio, atau alamat pengguna.',
                'request_body' => [
                    'name' => 'Budi Santoso Baru (string, optional)',
                    'phone' => '0812999888 (string, optional)',
                    'bio' => 'Deskripsi profil (string, optional)',
                    'city' => 'Pekanbaru (string, optional)',
                    'district' => 'Tampan (string, optional)',
                ],
            ],
            'GET /api/kosts' => [
                'category' => 'Kost',
                'description' => 'Menampilkan daftar kost publik yang sudah disetujui (approved) dengan filter dan paginasi.',
                'parameters' => [
                    'search' => 'Pencarian nama, deskripsi, atau alamat kost (string)',
                    'city' => 'Filter kota tertentu (string)',
                    'district' => 'Filter kecamatan tertentu (string)',
                    'gender' => 'Filter gender: putra | putri | campur',
                    'min_price' => 'Filter harga minimum (numeric)',
                    'max_price' => 'Filter harga maksimum (numeric)',
                    'per_page' => 'Jumlah per halaman, default: 15 (integer, max: 50)',
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Data kost berhasil ditemukan',
                    'data' => [
                        [
                            'id' => 1,
                            'name' => 'Kost Mawar Asri',
                            'price_min' => 750000,
                            'gender_type' => 'campur',
                            'verification_status' => 'approved',
                        ],
                    ],
                    'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
                ],
            ],
            'GET /api/kosts/{id}' => [
                'category' => 'Kost',
                'description' => 'Menampilkan detail lengkap sebuah kost beserta kamar, galeri foto, dan data pemilik.',
                'parameters' => [
                    'id' => 'ID kost yang dicari (integer, required path)',
                ],
            ],
            'GET /api/owner/kosts' => [
                'category' => 'Kost',
                'description' => 'Menampilkan daftar seluruh kost milik pemilik yang sedang login (semua status verifikasi).',
            ],
            'POST /api/owner/kosts' => [
                'category' => 'Kost',
                'description' => 'Mendaftarkan kost baru (otomatis berstatus pending untuk diverifikasi admin).',
                'request_body' => [
                    'name' => 'Kost Kenanga Indah (required)',
                    'address' => 'Jl. Kenanga No. 12 (required)',
                    'city' => 'Pekanbaru (required)',
                    'district' => 'Sukajadi (required)',
                    'price_min' => 600000,
                    'price_max' => 1200000,
                    'gender_type' => 'putri',
                    'facilities' => ['WiFi', 'AC'],
                ],
            ],
            'PUT /api/owner/kosts/{kost}' => [
                'category' => 'Kost',
                'description' => 'Memperbarui data kost milik sendiri (validasi kepemilikan).',
            ],
            'DELETE /api/owner/kosts/{kost}' => [
                'category' => 'Kost',
                'description' => 'Menghapus kost milik sendiri dan seluruh berkas foto terkait.',
            ],
            'GET /api/kosts/{kost}/rooms' => [
                'category' => 'Rooms',
                'description' => 'Menampilkan daftar tipe kamar pada kost tertentu.',
            ],
            'POST /api/owner/kosts/{kost}/rooms' => [
                'category' => 'Rooms',
                'description' => 'Menambahkan tipe kamar baru pada kost milik sendiri.',
                'request_body' => [
                    'name' => 'Kamar Standar (required)',
                    'price' => 750000,
                    'total_rooms' => 5,
                    'available_rooms' => 3,
                ],
            ],
            'PUT /api/owner/rooms/{room}' => [
                'category' => 'Rooms',
                'description' => 'Memperbarui informasi dan harga kamar pada kost milik sendiri.',
            ],
            'DELETE /api/owner/rooms/{room}' => [
                'category' => 'Rooms',
                'description' => 'Menghapus tipe kamar pada kost milik sendiri.',
            ],
            'POST /api/owner/kosts/{kost}/images' => [
                'category' => 'Kost',
                'description' => 'Mengunggah foto kost ke Supabase Storage (validasi MIME image, max 2MB).',
            ],
            'DELETE /api/owner/images/{image}' => [
                'category' => 'Kost',
                'description' => 'Menghapus foto dari Supabase Storage dan database.',
            ],
            'PUT /api/owner/images/{image}/primary' => [
                'category' => 'Kost',
                'description' => 'Menetapkan foto tertentu sebagai foto utama (thumbnail).',
            ],
            'GET /api/favorites' => [
                'category' => 'Favorites',
                'description' => 'Mendapatkan daftar kost yang difavoritkan oleh pengguna saat ini.',
            ],
            'POST /api/favorites/{kost}' => [
                'category' => 'Favorites',
                'description' => 'Menambahkan kost ke daftar favorit pengguna.',
            ],
            'DELETE /api/favorites/{kost}' => [
                'category' => 'Favorites',
                'description' => 'Menghapus kost dari daftar favorit pengguna.',
            ],
            'GET /api/chats' => [
                'category' => 'Chat & Messages',
                'description' => 'Menampilkan seluruh percakapan obrolan pengguna saat ini.',
            ],
            'POST /api/chats' => [
                'category' => 'Chat & Messages',
                'description' => 'Memulai atau membuka obrolan dengan pemilik kost.',
                'request_body' => [
                    'kost_id' => '1 (integer, required)',
                    'message' => 'Halo apakah masih ada kamar? (string, optional)',
                ],
            ],
            'GET /api/chats/{chat}/messages' => [
                'category' => 'Chat & Messages',
                'description' => 'Mendapatkan riwayat pesan dalam percakapan (otomatis mengubah status pesan belum dibaca menjadi is_read = true).',
            ],
            'POST /api/chats/{chat}/messages' => [
                'category' => 'Chat & Messages',
                'description' => 'Mengirim pesan baru ke dalam percakapan.',
                'request_body' => [
                    'message' => 'Teks pesan (string, required)',
                ],
            ],
            'GET /api/admin/users' => [
                'category' => 'Admin',
                'description' => 'Menampilkan seluruh daftar pengguna (khusus role admin).',
            ],
            'GET /api/admin/kosts' => [
                'category' => 'Admin',
                'description' => 'Menampilkan seluruh data kost semua status verifikasi (khusus admin).',
            ],
            'GET /api/admin/kosts/pending' => [
                'category' => 'Admin',
                'description' => 'Menampilkan daftar kost yang menunggu verifikasi admin.',
            ],
            'PUT /api/admin/kosts/{id}/approve' => [
                'category' => 'Admin',
                'description' => 'Menyetujui pendaftaran kost sehingga tampil di publik.',
            ],
            'PUT /api/admin/kosts/{id}/reject' => [
                'category' => 'Admin',
                'description' => 'Menolak pendaftaran kost.',
            ],
            'DELETE /api/admin/kosts/{id}' => [
                'category' => 'Admin',
                'description' => 'Menghapus kost oleh admin.',
            ],
            'GET /api/admin/reports' => [
                'category' => 'Admin',
                'description' => 'Menampilkan seluruh laporan pengguna terkait kost.',
            ],
            'PUT /api/admin/reports/{report}/status' => [
                'category' => 'Admin',
                'description' => 'Mengubah status laporan (pending, reviewed, resolved).',
            ],
            'GET /api/health' => [
                'category' => 'System',
                'description' => 'Memeriksa kesehatan sistem (API, PostgreSQL, Supabase Storage, Cache).',
            ],
        ];
    }
}
