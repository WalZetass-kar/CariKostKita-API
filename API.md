# CariKostKita — Single API Gateway Specification

**Version:** 1.0.0 (Unified)  
**Base URL:** `http://127.0.0.1:8000/api` atau `http://127.0.0.1:8000/api/v1`  
**Control Center Dashboard:** `http://127.0.0.1:8000/`  
**Interactive Documentation:** `http://127.0.0.1:8000/docs`  
**REST API Tester:** `http://127.0.0.1:8000/docs/tester`  

---

## 1. Authentication & Security
Semua endpoint terproteksi mewajibkan HTTP Bearer Token yang diterbitkan via Laravel Sanctum.
```http
Authorization: Bearer <sanctum_plain_text_token>
Accept: application/json
Content-Type: application/json
```

Role yang didukung sistem (dengan alias otomatis):
- `pencari` (alias: `user`)
- `pemilik` (alias: `owner`, `pemilik_kost`)
- `admin` (alias: `developer`)
- `moderator`

---

## 2. Public & App Client Endpoints (Mobile & Landing Page)

| Method | Endpoint | Keterangan & Klien Pengguna |
| :--- | :--- | :--- |
| `GET` | `/api/v1/health` | Status operasional API, Database, Storage, dan Cache |
| `GET` | `/api/v1/app/latest-release` | Informasi rilis APK Android terbaru untuk Landing Page & Mobile auto-update |
| `GET` | `/api/v1/app/settings` | Pengaturan platform, kontak WhatsApp bantuan, kontak pendaftaran pemilik |
| `GET` | `/api/v1/app/ads/active` | Banner promosi & pop-up iklan aktif untuk beranda Mobile App |
| `POST`| `/api/v1/app/ads/{id}/click` | Pencatatan klik iklan untuk telemetri CTR admin |
| `POST`| `/api/v1/app/crashes` | Pelaporan crash Android otomatis untuk telemetri sistem |
| `POST`| `/api/v1/app/events` | Pelaporan event analitik (search, view_detail, chat_start, survey) |

---

## 3. Katalog Kost & Pencarian (Pencari Kost)

| Method | Endpoint | Parameter / Body | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/kosts` | `search`, `area`, `gender`, `min_price`, `max_price`, `available_only`, `sort` | Menampilkan kost terverifikasi aktif dengan paginasi dan filter |
| `GET` | `/api/v1/kosts/map` | `latitude`, `longitude`, `radius` (km) | Pencarian kost berbasis radius koordinat pada peta interaktif |
| `GET` | `/api/v1/kosts/{id}` | - | Detail lengkap kost, ulasan, foto, aturan sewa, dan WhatsApp pemilik |
| `GET` | `/api/v1/kosts/{kost}/rooms` | - | Daftar tipe kamar pada kost |

---

## 4. Autentikasi & Akun Pengguna

| Method | Endpoint | Body Utama | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | `name`, `email`, `password`, `role`, `phone` | Registrasi akun baru |
| `POST` | `/api/v1/auth/login` | `email`, `password` | Login dan penerbitan Sanctum Bearer Token |
| `POST` | `/api/v1/auth/google` | `id_token` / `email`, `google_id`, `name`, `avatar_url` | Autentikasi Google Sign-In & penerbitan token |
| `POST` | `/api/v1/auth/logout` | - | Revoke token saat ini |
| `GET`  | `/api/v1/user/profile` | - | Profil pengguna login beserta status akun |
| `PUT`  | `/api/v1/user/profile` | `name`, `phone`/`no_hp`, `avatar`, `bio` | Pembaruan data profil |
| `PUT`  | `/api/v1/user/password` | `current_password`, `new_password` | Ubah kata sandi |
| `DELETE`| `/api/v1/user/account` | - | Hapus akun mandiri (syarat Google Play Store) |
| `POST` | `/api/v1/user/verification` | `ktp`, `selfie`, `kepemilikan` (multipart) | Unggah berkas verifikasi pemilik ke storage privat |

---

## 5. Interaksi: Favorit, Chat, Survei & Review

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET`   | `/api/v1/favorites` | Daftar kost tersimpan/favorit |
| `POST`  | `/api/v1/favorites/{kost}` | Simpan kost ke favorit |
| `DELETE`| `/api/v1/favorites/{kost}` | Hapus kost dari favorit |
| `GET`   | `/api/v1/chats` | Daftar obrolan aktif dengan badge unread dan pesan terakhir |
| `POST`  | `/api/v1/chats` | Mulai percakapan baru dengan pemilik kost |
| `GET`   | `/api/v1/chats/{chat}/messages` | Riwayat pesan obrolan (reset unread counter) |
| `POST`  | `/api/v1/chats/{chat}/messages` | Kirim pesan teks baru |
| `PATCH` | `/api/v1/chats/{chat}/read` | Tandai percakapan telah dibaca |
| `POST`  | `/api/v1/surveys` | Pencari mengajukan jadwal survei kost |
| `GET`   | `/api/v1/surveys` | Daftar jadwal survei milik pencari |
| `PATCH` | `/api/v1/surveys/{id}/cancel` | Batalkan jadwal survei |
| `POST`  | `/api/v1/reviews` | Kirim rating 1-5 bintang & ulasan setelah survei selesai |
| `POST`  | `/api/v1/safety/block` | Blokir akun pengguna lain |
| `POST`  | `/api/v1/safety/report-user` | Laporkan pelanggaran pengguna di chat |
| `POST`  | `/api/v1/reports/kost` | Laporkan data kost tidak valid |

---

## 6. Fitur Pemilik Properti (`role: pemilik, admin`)

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET`   | `/api/v1/owner/kosts` | Seluruh properti kost milik sendiri |
| `POST`  | `/api/v1/owner/kosts` | Daftarkan kost baru (otomatis pending verifikasi) |
| `PUT`   | `/api/v1/owner/kosts/{kost}` | Edit data properti kost milik sendiri |
| `DELETE`| `/api/v1/owner/kosts/{kost}` | Hapus kost milik sendiri |
| `POST`  | `/api/v1/owner/kosts/{kost}/resubmit` | Ajukan ulang verifikasi setelah revisi |
| `GET`   | `/api/v1/owner/analytics` | Metrik 30 hari (views, saves, chats, survei) |
| `GET`   | `/api/v1/owner/surveys` | Permintaan survei masuk dari pencari kost |
| `PATCH` | `/api/v1/owner/surveys/{id}` | Konfirmasi (`CONFIRMED`), tolak, atau selesaikan (`COMPLETED`) survei |
| `POST`  | `/api/v1/owner/kosts/{kost}/images` | Unggah foto galeri kost |
| `DELETE`| `/api/v1/owner/images/{image}` | Hapus foto kost |
| `PUT`   | `/api/v1/owner/images/{image}/primary` | Tetapkan foto utama / thumbnail kost |

---

## 7. Admin Dashboard (`Admin-Gue` — `role: admin`)

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET`   | `/api/v1/admin/dashboard` | Statistik metrik platform, sebaran kecamatan, dan performa CTR iklan |
| `GET`   | `/api/v1/admin/users` | Daftar pengguna pencari kost |
| `PATCH` | `/api/v1/admin/users/{id}/status` | Aktifkan / Nonaktifkan (suspend) akun pengguna |
| `GET`   | `/api/v1/admin/owners` | Daftar pemilik kost & jumlah propertinya |
| `PATCH` | `/api/v1/admin/owners/{id}/status` | Aktifkan / Nonaktifkan akun pemilik kost |
| `GET`   | `/api/v1/admin/kosts` | Seluruh data kost (filter: Menunggu Verifikasi, Aktif, Ditolak, Nonaktif) |
| `PATCH` | `/api/v1/admin/kosts/{id}/status` | Moderasi: Verifikasi (Aktif), Tolak (dengan catatan revisi), Nonaktifkan |
| `GET`   | `/api/v1/admin/reports` | Kanban laporan pengguna |
| `PUT`   | `/api/v1/admin/reports/{report}/status` | Tindak lanjut laporan (Baru, Diproses, Selesai, Ditolak) |
| `GET`   | `/api/v1/admin/ads` | Manajemen materi iklan banner & promo |
| `POST`  | `/api/v1/admin/ads` | Tambah iklan baru (dengan unggah gambar) |
| `PUT`   | `/api/v1/admin/ads/{id}` | Edit iklan |
| `DELETE`| `/api/v1/admin/ads/{id}` | Hapus iklan |
| `PATCH` | `/api/v1/admin/ads/{id}/status` | Toggle aktif/nonaktif iklan |
| `GET`   | `/api/v1/admin/ads/analytics` | Telemetri performa iklan (impresi, klik, CTR) |
| `GET`   | `/api/v1/admin/audit-logs` | Riwayat aktivitas administratif admin |
| `GET`   | `/api/v1/admin/releases` | Daftar rilis versi APK aplikasi |
| `POST`  | `/api/v1/admin/releases` | Publikasikan rilis APK baru |
| `GET`   | `/api/v1/admin/settings` | Daftar variabel konfigurasi platform |
| `PUT`   | `/api/v1/admin/settings` | Perbarui konfigurasi platform |
