# CariKostKita — Backend Design

## 1. Design Goal
Backend harus modular, mudah dikembangkan, aman, dan menjadi single source of truth untuk Android, Landing Page, dan Admin Dashboard.

## 2. High-Level Flow
```text
Client
  ↓ HTTPS
Laravel API
  ↓
Controllers
  ↓
Requests / Authorization
  ↓
Services
  ↓
Models / PostgreSQL
  ↓
Supabase
```

## 3. Laravel Structure
```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Services/
└── Policies/

database/
└── migrations/

routes/
└── api.php

config/
resources/
public/
```

## 4. Responsibilities
### Controllers
Menerima request dan mengembalikan response. Hindari business logic kompleks.

### Form Requests
Validasi input API.

### Policies
Memastikan user hanya dapat mengakses resource yang berhak diakses.

### Services
Business logic dan integrasi eksternal seperti Supabase Storage.

### Models
Relasi dan akses data.

### Resources
Format response API yang konsisten.

## 5. Data Ownership
Pemilik hanya dapat mengubah kost yang `owner_id`-nya sama dengan user yang login.

Admin memiliki akses moderasi.

Pencari hanya dapat mengubah data miliknya sendiri.

## 6. Storage Design
```text
Supabase Storage
└── kost-images/
    └── {kost_id}/
        ├── image-1.webp
        ├── image-2.webp
        └── image-3.webp
```

Gunakan database untuk metadata/path, bukan menyimpan binary gambar di PostgreSQL.

## 7. API Versioning
Untuk MVP gunakan:
`/api/...`

Jika breaking change diperlukan, gunakan:
`/api/v2/...`

## 8. Production Principles
- APP_DEBUG=false
- HTTPS wajib
- Credential hanya melalui environment variables
- Logging aktif
- Database migration terkontrol
- Queue digunakan jika pekerjaan menjadi berat
