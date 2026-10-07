# CariKostKita — Environment Variables

Contoh `.env`:

```env
APP_NAME=CariKostKita
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SUPABASE_URL=
SUPABASE_KEY=
SUPABASE_STORAGE_BUCKET=
```

## Rules
- `.env` tidak boleh masuk Git.
- Credential production berbeda dari local.
- Service role/secret key tidak boleh dikirim ke Android, browser, atau repository.
- Gunakan `.env.example` tanpa secret.
