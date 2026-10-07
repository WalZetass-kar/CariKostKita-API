# CariKostKita — Backend API

Backend RESTful API CariKostKita berbasis **Laravel 12** yang melayani:
- **Android Mobile App** (`CariKostKita mobile app`)
- **Landing Page** (`Landing Page`)
- **Admin Dashboard** (`Admin-Gue`)

Database utama menggunakan **Supabase PostgreSQL** dan berkas gambar disimpan di **Supabase Storage**.

---

## 🚀 Fitur Utama & Fase Selesai

### Phase 1 — Foundation
- Framework Laravel 12 dengan struktur modular.
- Standardized API Response (`success`, `message`, `data`/`errors`).
- Global exception handling untuk endpoint API (`/api/*`).
- Konfigurasi environment variables terpusat untuk Supabase PostgreSQL & Storage.

### Phase 2 — Database, Authentication & Roles
- Migrasi 10 tabel database lengkap sesuai spesifikasi (`users`, `profiles`, `kosts`, `kost_images`, `rooms`, `favorites`, `chats`, `messages`, `reports`, `notifications`).
- Model Eloquent lengkap dengan relasi, cascade delete, casts, dan indexes.
- Autentikasi token berbasis **Laravel Sanctum** (`POST /api/register`, `POST /api/login`, `POST /api/logout`).
- Role-based authorization (`pencari`, `pemilik`, `admin`) via `RoleMiddleware`.
- Endpoint profil pengguna (`GET /api/user`, `PUT /api/user/profile`).
- Seeder bawaan untuk pengujian role:
  - **Admin**: `admin@carikostkita.com` / `password123`
  - **Pemilik**: `pemilik@carikostkita.com` / `password123`
  - **Pencari**: `pencari@carikostkita.com` / `password123`

### Phase 3 — Kost, Kamar & Upload Foto (Storage)
- **Public Kost Browsing**: Hanya menampilkan kost berstatus `approved` dengan pagination.
- **Search & Filter**: Pencarian teks (`search`), filter kota (`city`), kecamatan (`district`), tipe jenis kelamin (`gender`), rentang harga (`min_price`, `max_price`).
- **Owner Kost Management**: Pemilik dapat membuat kost baru (status default `pending`), memperbarui, dan menghapus kost miliknya dengan validasi kepemilikan (`KostPolicy`).
- **Room Management**: Pemilik dapat menambah, memperbarui, dan menghapus tipe kamar di kost miliknya. Publik dapat melihat daftar kamar.
- **Supabase Storage Integration**: Unggah foto kost ke Supabase Storage bucket `kost-images` dengan sanitasi UUID, validasi MIME/ukuran maksimal 2MB, pengaturan foto utama (`primary`), serta fallback lokal aman.

### Phase 4 — Favorite & Chat Percakapan
- **Favorite System**: Pengguna dapat menambahkan kost ke daftar favorit (`POST /api/favorites/{kost}`), melihat daftar favorit (`GET /api/favorites`), dan menghapus dari favorit (`DELETE /api/favorites/{kost}`).
- **Private Chatting**: Percakapan privat antara pencari kost dan pemilik kost (`POST /api/chats`, `GET /api/chats`).
- **Messaging & Read Status**: Kirim pesan (`POST /api/chats/{chat}/messages`), baca pesan (`GET /api/chats/{chat}/messages`), dan auto update status `is_read = true` saat pesan dibuka oleh penerima. Proteksi akses percakapan via `ChatPolicy`.

### Phase 5 — Admin Moderation & Management
- **User Management**: Admin dapat melihat seluruh daftar pengguna dengan paginasi dan filter role (`GET /api/admin/users`).
- **Kost Verification**: Admin dapat meninjau kost pending (`GET /api/admin/kosts/pending`), menyetujui (`PUT /api/admin/kosts/{id}/approve`), menolak (`PUT /api/admin/kosts/{id}/reject`), atau menghapus kost (`DELETE /api/admin/kosts/{id}`).
- **Reports Moderation**: Admin dapat memoderasi laporan pelanggaran kost (`GET /api/admin/reports`, `PUT /api/admin/reports/{report}/status`).

---

## 🛠️ Prasyarat
- **PHP**: >= 8.2 (ekstensi `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `curl`, `mbstring`, `fileinfo`)
- **Composer**: >= 2.x

---

## ⚙️ Instalasi & Konfigurasi

1. **Clone repository & Pasang Dependensi**:
   ```bash
   composer install
   ```

2. **Setup File Environment**:
   Salin file template `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   # Atau pada Windows PowerShell:
   Copy-Item .env.example .env
   ```
   Generate application key:
   ```bash
   php artisan key:generate
   ```

3. **Konfigurasi Supabase PostgreSQL**:
   Buka file `.env` dan masukkan kredensial Supabase Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres.your-project-ref
   DB_PASSWORD=your-supabase-db-password

   SUPABASE_URL=https://your-project.supabase.co
   SUPABASE_KEY=your-supabase-key
   SUPABASE_STORAGE_BUCKET=kost-images
   ```

4. **Jalankan Migrasi & Seeding**:
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Local Development Server**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Automated Testing

Semua test suite menggunakan SQLite in-memory, sehingga dapat dijalankan tanpa koneksi internet:
```bash
php artisan test
```

Pengecekan code style dengan Laravel Pint:
```bash
php vendor/bin/pint
```

---

## 📡 Daftar Endpoint API (Saat Ini)

### Public Endpoints
| Method | URI | Deskripsi |
|---|---|---|
| `POST` | `/api/register` | Mendaftarkan pengguna baru (`role`: `pencari` atau `pemilik`) |
| `POST` | `/api/login` | Login dan mendapatkan Bearer Token |
| `GET` | `/api/kosts` | Menampilkan daftar kost publik (approved, filter, search, paginasi) |
| `GET` | `/api/kosts/{id}` | Detail lengkap kost, foto, dan kamar |
| `GET` | `/api/kosts/{kost}/rooms` | Daftar kamar pada suatu kost |

### Authenticated Endpoints (Header `Authorization: Bearer {token}`)
| Method | URI | Deskripsi |
|---|---|---|
| `GET` | `/api/user` | Mendapatkan data profil akun yang sedang login |
| `PUT` | `/api/user/profile` | Memperbarui nama, telepon, avatar, bio, atau alamat |
| `POST` | `/api/logout` | Mencabut dan menghapus Bearer Token aktif |
| `GET` | `/api/favorites` | Menampilkan daftar kost favorit pengguna |
| `POST` | `/api/favorites/{kost}` | Menambahkan kost ke daftar favorit |
| `DELETE` | `/api/favorites/{kost}` | Menghapus kost dari daftar favorit |
| `GET` | `/api/chats` | Menampilkan seluruh percakapan chat pengguna |
| `POST` | `/api/chats` | Memulai percakapan baru dengan pemilik kost |
| `GET` | `/api/chats/{chat}/messages` | Menampilkan daftar pesan dalam suatu chat (auto-read) |
| `POST` | `/api/chats/{chat}/messages` | Mengirim pesan baru ke dalam chat |

### Owner Endpoints (`role: pemilik, admin`)
| Method | URI | Deskripsi |
|---|---|---|
| `GET` | `/api/owner/kosts` | Daftar kost milik user yang sedang login |
| `POST` | `/api/owner/kosts` | Membuat kost baru (status pending verifikasi) |
| `PUT` | `/api/owner/kosts/{id}` | Mengubah data kost milik sendiri |
| `DELETE` | `/api/owner/kosts/{id}` | Menghapus kost milik sendiri dan seluruh fotonya |
| `POST` | `/api/owner/kosts/{kost}/rooms` | Menambah kamar pada kost milik sendiri |
| `PUT` | `/api/owner/rooms/{room}` | Mengubah kamar pada kost milik sendiri |
| `DELETE` | `/api/owner/rooms/{room}` | Menghapus kamar pada kost milik sendiri |
| `POST` | `/api/owner/kosts/{kost}/images` | Mengunggah foto ke Supabase Storage |
| `DELETE` | `/api/owner/images/{image}` | Menghapus foto dari Supabase Storage |
| `PUT` | `/api/owner/images/{image}/primary` | Menetapkan foto sebagai foto utama |

### Admin Endpoints (`role: admin`)
| Method | URI | Deskripsi |
|---|---|---|
| `GET` | `/api/admin/users` | Menampilkan seluruh daftar pengguna (filter & search) |
| `GET` | `/api/admin/kosts` | Menampilkan seluruh daftar kost semua status |
| `GET` | `/api/admin/kosts/pending` | Menampilkan daftar kost yang menunggu verifikasi |
| `PUT` | `/api/admin/kosts/{id}/approve` | Menyetujui pendaftaran kost |
| `PUT` | `/api/admin/kosts/{id}/reject` | Menolak pendaftaran kost |
| `DELETE` | `/api/admin/kosts/{id}` | Menghapus kost dan fotonya oleh admin |
| `GET` | `/api/admin/reports` | Menampilkan daftar laporan pelanggaran pengguna |
| `PUT` | `/api/admin/reports/{report}/status` | Mengubah status laporan (pending/reviewed/resolved) |

---

## 🔒 Format Standar Respons API

### Response Sukses (HTTP 200 / 201)
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": { ... }
}
```

### Response Validasi Gagal (HTTP 422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email sudah terdaftar."]
  }
}
```

### Response Tidak Terautentikasi (HTTP 401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

---

## 🚢 Panduan Deployment Produksi

1. Pastikan server produksi memiliki PHP 8.2+ dan ekstensi `pdo_pgsql`.
2. Arahkan Document Root web server (Nginx/Apache) ke direktori `/public`.
3. Set environment variable:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
4. Jalankan perintah optimasi:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   ```
