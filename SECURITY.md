# CariKostKita — Security

## Secrets
Jangan commit:
- `.env`
- database password
- Supabase service role key
- production API secrets

Gunakan `.env.example` sebagai template.

## Authentication
Password wajib di-hash. Token harus memiliki expiry/revocation strategy yang sesuai.

## Authorization
- Pencari hanya mengelola resource miliknya.
- Pemilik hanya mengelola kost miliknya.
- Admin memakai middleware khusus.
- Jangan hanya menyembunyikan tombol di frontend; authorization wajib ditegakkan di backend.

## File Upload
- Validasi MIME type
- Validasi extension
- Batasi ukuran
- Generate nama/path server-side
- Jangan percaya filename dari client

## API
- HTTPS
- Rate limiting
- CORS dibatasi sesuai client
- Validasi seluruh request
- Jangan mengembalikan password atau secret

## Production
```env
APP_ENV=production
APP_DEBUG=false
```

Review log secara berkala dan jangan menampilkan stack trace ke user production.
