# CariKostKita — Deployment

## Local
```bash
composer create-project laravel/laravel carikostkita-api
cd carikostkita-api
php artisan serve
```

## GitHub
```bash
git init
git add .
git commit -m "Initial Laravel API"
git branch -M main
git remote add origin <repository>
git push -u origin main
```

## Environment
Production harus menyediakan:
- APP_KEY
- APP_ENV
- APP_DEBUG=false
- APP_URL
- DB_CONNECTION=pgsql
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD
- Supabase credentials sesuai integrasi

## Deployment Checklist
1. Clone repository.
2. Install Composer dependencies.
3. Set production environment.
4. Set document root ke `/public`.
5. Run migrations.
6. Cache configuration/routes jika sesuai.
7. Set storage/link jika digunakan.
8. Configure HTTPS.
9. Test health endpoint.
10. Test authentication dan database.

## Important
Database production menggunakan Supabase. Jangan membuat PostgreSQL kedua di server deployment kecuali memang diperlukan.
