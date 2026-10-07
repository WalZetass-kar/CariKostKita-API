# CariKostKita — Product Requirements Document

## 1. Overview
CariKostKita adalah platform pencarian kost dengan Android App, Landing Page, dan Admin Dashboard. Laravel menjadi backend/API pusat. Supabase digunakan untuk PostgreSQL dan Storage.

## 2. Architecture
```text
Android App ───────┐
Landing Page ──────┼──> Laravel API ──> Supabase PostgreSQL
Admin Dashboard ───┘                    └─> Supabase Storage
```

Client tidak mengakses database secara langsung.

## 3. Roles
### Pencari Kost
Register, login, profil, melihat/mencari/filter kost, favorit, dan chat pemilik.

### Pemilik Kost
Mengelola profil, kost, kamar, foto, dan percakapan dengan pencari.

### Admin
Mengelola pengguna, memverifikasi/menolak kost, moderasi konten, dan melihat laporan.

## 4. Core Features
- Authentication dan authorization
- Role-based access
- CRUD kost
- CRUD kamar
- Upload foto kost
- Search dan filter
- Favorite
- Chat
- Admin moderation
- Profile management
- API validation dan security

## 5. Database
Gunakan satu Supabase PostgreSQL untuk data utama:
- users
- profiles
- kosts
- kost_images
- rooms
- favorites
- chats
- messages
- reports
- notifications

Foto disimpan di Supabase Storage; database hanya menyimpan metadata/path.

## 6. API
### Authentication
- POST /api/register
- POST /api/login
- POST /api/logout
- GET /api/user
- PUT /api/user/profile

### Kost
- GET /api/kosts
- GET /api/kosts/{id}
- POST /api/owner/kosts
- PUT /api/owner/kosts/{id}
- DELETE /api/owner/kosts/{id}

### Rooms
- GET /api/kosts/{kost}/rooms
- POST /api/owner/kosts/{kost}/rooms
- PUT /api/owner/rooms/{room}
- DELETE /api/owner/rooms/{room}

### Favorites
- GET /api/favorites
- POST /api/favorites/{kost}
- DELETE /api/favorites/{kost}

### Chat
- GET /api/chats
- POST /api/chats
- GET /api/chats/{chat}/messages
- POST /api/chats/{chat}/messages

### Admin
- GET /api/admin/users
- GET /api/admin/kosts
- GET /api/admin/kosts/pending
- PUT /api/admin/kosts/{id}/approve
- PUT /api/admin/kosts/{id}/reject
- DELETE /api/admin/kosts/{id}

## 7. API Response Standard
```json
{
  "success": true,
  "message": "Data berhasil ditemukan",
  "data": {}
}
```

Error:
```json
{
  "success": false,
  "message": "Data tidak ditemukan",
  "errors": {}
}
```

Gunakan HTTP status yang sesuai: 200, 201, 400, 401, 403, 404, 422, 500.

## 8. Security
- Hash password
- Authentication middleware
- Role middleware
- Authorization berdasarkan ownership
- Validation
- Rate limiting
- CORS configuration
- Secure upload validation
- Jangan expose credential database/Supabase secret
- Jangan commit `.env`

## 9. Development Phases
1. Laravel + GitHub + Supabase
2. Database migrations
3. Authentication
4. Kost dan kamar
5. Upload foto
6. Search/filter/favorite
7. Chat
8. Admin
9. Testing
10. Deployment

## 10. Definition of Done
Backend dianggap siap MVP jika authentication, role, kost, kamar, foto, favorite, dan endpoint admin utama sudah dapat diuji melalui API client dan digunakan oleh client aplikasi.
