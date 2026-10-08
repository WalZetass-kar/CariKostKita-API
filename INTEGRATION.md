# Panduan Integrasi Klien — CariKostKita Single API Gateway

Dokumen ini menjelaskan cara menyambungkan ketiga project frontend existing:
1. **Mobile App** (`CariKostKita mobile app` — Native Android Java)
2. **Landing Page** (`Landing Page` — Next.js)
3. **Admin Dashboard** (`Admin-Gue` — Next.js)

ke satu backend pusat Laravel CariKostKita yang terhubung ke satu project Supabase.

---

## 1. Arsitektur Gateway Terpusat

```
                                      +--------------------------------+
                                      |   Supabase Single Project      |
                                      |  (PostgreSQL & Storage Bucket) |
                                      +--------------------------------+
                                                      ^
                                                      | (Koneksi Internal Aman)
                                                      v
                                      +--------------------------------+
                                      |  Laravel Single API Gateway    |
                                      |   (api.carikostkita.com / :8000)|
                                      +--------------------------------+
                                        /             |              \
                                       /              |               \
                                      v               v                v
                       +----------------+   +-------------------+   +--------------------+
                       |   Mobile App   |   |   Landing Page    |   |  Admin Dashboard   |
                       | (Android Java) |   | (Next.js Publik)  |   | (Next.js Admin-Gue)|
                       +----------------+   +-------------------+   +--------------------+
```

> [!IMPORTANT]
> **Tidak ada client yang memanggil Supabase secara langsung**. Semua panggilan harus melewati endpoint `/api/v1/...` di Laravel Backend. Mock repository di Mobile App tetap dipertahankan untuk kebutuhan UI development offline.

---

## 2. Integrasi Mobile App (Android Java)

### 2.1 Konfigurasi Base URL Retrofit
Ganti Base URL dari URL PostgREST langsung menjadi URL Laravel API Gateway:
```java
public class ApiConfig {
    // Development emulator Android: 10.0.2.2 menuju localhost PC
    public static final String BASE_URL = "http://10.0.2.2:8000/api/v1/";
    // Atau jika menggunakan HP fisik dalam satu WiFi:
    // public static final String BASE_URL = "http://192.168.x.x:8000/api/v1/";
}
```

### 2.2 Penyesuaian Endpoint Retrofit
- **Login**: `POST /api/v1/auth/login` (kembalikan `token` Sanctum dan simpan di `EncryptedSharedPreferences`).
- **Katalog Kost**: `GET /api/v1/kosts` (mendukung filter `area`, `gender`, `min_price`, `max_price`, `available_only`).
- **Peta Interaktif**: `GET /api/v1/kosts/map?latitude={lat}&longitude={lng}&radius={km}`.
- **Iklan Banner**: `GET /api/v1/app/ads/active` & `POST /api/v1/app/ads/{id}/click`.
- **Survei**: `POST /api/v1/surveys` & `GET /api/v1/surveys`.
- **Ulasan**: `POST /api/v1/reviews` (setelah survei berstatus `COMPLETED`).
- **Keamanan**: `POST /api/v1/safety/block` & `POST /api/v1/safety/report-user`.
- **Crash & Event Telemetri**: `POST /api/v1/app/crashes` & `POST /api/v1/app/events`.
- **Hapus Akun**: `DELETE /api/v1/user/account`.

---

## 3. Integrasi Landing Page (Next.js)

### 3.1 Mengambil Rilis APK Terbaru Dinamis
Ganti tautan hardcoded APK di tombol unduh dengan fetch dinamis dari API:
```ts
// Contoh pemanggilan di Next.js:
const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/app/latest-release`, {
  next: { revalidate: 3600 } // ISR 1 jam
});
const release = await response.json();

// release.data.apk_url -> URL unduh APK langsung
// release.data.version_name -> misal "v1.0.0-beta"
// release.data.file_size -> misal "18.4 MB"
```

### 3.2 Mengambil Pengaturan Platform & Kontak
```ts
const settingsRes = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/app/settings`);
const settings = await settingsRes.json();

// settings.data.contact_whatsapp -> Nomor WhatsApp CS Bantuan
// settings.data.owner_registration_whatsapp -> Nomor WhatsApp Mitra Pemilik
// settings.data.coverage_areas -> Daftar 15 Kecamatan Pekanbaru
```

---

## 4. Integrasi Admin Dashboard (`Admin-Gue` Next.js)

`Admin-Gue` saat ini menggunakan mock state di `app/page.jsx`. Untuk menghubungkan ke backend nyata:

### 4.1 Login Admin
Kirim request ke `POST /api/v1/auth/login`:
```javascript
const res = await fetch('http://127.0.0.1:8000/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: adminEmail, password: adminPassword })
});
const data = await res.json();
localStorage.setItem('admin_token', data.data.token);
```

### 4.2 Memuat Dashboard & Metrik
Panggil `GET /api/v1/admin/dashboard` dengan header:
```javascript
headers: {
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
  'Accept': 'application/json'
}
```
Respons mencakup:
- `metrics.total_kost`, `kost_aktif`, `menunggu_verifikasi`, `ditolak`, `nonaktif`
- `metrics.total_owners`, `total_users`, `laporan_baru`, `iklan_aktif`
- `area_distribution` (data untuk chart sebaran kecamatan)
- `ads_overview` (impresi, klik, average CTR)

### 4.3 Moderasi Kost
- **Verifikasi**: `PATCH /api/v1/admin/kosts/{id}/status` dengan body `{ status: 'Aktif' }`
- **Tolak**: `PATCH /api/v1/admin/kosts/{id}/status` dengan body `{ status: 'Ditolak', catatan_revisi: 'Alasan penolakan' }`
- **Nonaktifkan**: `PATCH /api/v1/admin/kosts/{id}/status` dengan body `{ status: 'Nonaktif' }`

### 4.4 Manajemen Iklan & Promosi
- List: `GET /api/v1/admin/ads`
- Tambah: `POST /api/v1/admin/ads` (multipart form dengan `banner_file`)
- Toggle status: `PATCH /api/v1/admin/ads/{id}/status`
- Hapus: `DELETE /api/v1/admin/ads/{id}`

---

## 5. Menjalankan Server & Monitoring

Jalankan Laravel API server:
```powershell
php artisan serve
```

Buka antarmuka monitoring:
- **API Control Center**: `http://127.0.0.1:8000/`
- **Dokumentasi Lengkap**: `http://127.0.0.1:8000/docs`
- **REST API Tester**: `http://127.0.0.1:8000/docs/tester`
- **Live Request Telemetry Logs**: `http://127.0.0.1:8000/docs/logs`
