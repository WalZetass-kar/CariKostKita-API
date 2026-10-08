-- ==============================================================================
-- CariKostKita - Supabase Project A (Database) SQL Schema
-- Generated from Laravel Migrations
-- Target Database: PostgreSQL (Supabase)
-- ==============================================================================
-- Instruksi:
-- 1. Jalankan script ini di SQL Editor pada dashboard Supabase Project A.
-- 2. Pastikan tabel-tabel default Laravel (seperti migrations) tidak bentrok.
--    (Jika menggunakan `php artisan migrate`, Laravel dapat melakukan ini 
--     secara otomatis tanpa perlu menjalankan script manual ini).
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. Tabel Default Laravel (users, tokens, sessions, jobs, cache)
-- ------------------------------------------------------------------------------

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP WITH TIME ZONE NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'pencari',
    phone VARCHAR(20) NULL,
    avatar VARCHAR(2048) NULL,
    remember_token VARCHAR(100) NULL,
    -- Tambahan Unified Fields
    no_hp VARCHAR(20) NULL,
    avatar_url VARCHAR(2048) NULL,
    google_id VARCHAR(255) NULL,
    auth_provider VARCHAR(50) DEFAULT 'email',
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP WITH TIME ZONE NULL,
    expires_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX pat_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX jobs_queue_index ON jobs (queue);

-- ------------------------------------------------------------------------------
-- 2. Modul Autentikasi & Profil (Pencari & Pemilik)
-- ------------------------------------------------------------------------------

CREATE TABLE profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    jenis_kelamin VARCHAR(20) NULL,
    tanggal_lahir DATE NULL,
    pekerjaan VARCHAR(100) NULL,
    bio TEXT NULL,
    alamat_asal TEXT NULL,
    kontak_darurat VARCHAR(20) NULL,
    ktp_path VARCHAR(2048) NULL,
    selfie_path VARCHAR(2048) NULL,
    kepemilikan_path VARCHAR(2048) NULL,
    pengajuan_at TIMESTAMP WITH TIME ZONE NULL,
    catatan_revisi TEXT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 3. Modul Properti / Kost & Kamar
-- ------------------------------------------------------------------------------

CREATE TABLE kosts (
    id BIGSERIAL PRIMARY KEY,
    owner_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    nama_kost VARCHAR(255) NULL,
    description TEXT NULL,
    address TEXT NOT NULL,
    patokan VARCHAR(255) NULL,
    no_whatsapp VARCHAR(20) NULL,
    province VARCHAR(100) NULL,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    village VARCHAR(100) NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    price_min DECIMAL(12,2) DEFAULT 0,
    price_max DECIMAL(12,2) DEFAULT 0,
    deposit DECIMAL(12,2) DEFAULT 0,
    gender_type VARCHAR(20) NOT NULL,
    ukuran_kamar VARCHAR(50) NULL,
    total_kamar INTEGER DEFAULT 1,
    kamar_tersedia INTEGER DEFAULT 1,
    thumbnail_url VARCHAR(2048) NULL,
    image_urls JSONB NULL,
    facilities JSONB NULL,
    aturan JSONB NULL,
    rating_avg DECIMAL(3,2) DEFAULT 0.00,
    rating_count INTEGER DEFAULT 0,
    views_count INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'available',
    verification_status VARCHAR(50) DEFAULT 'pending',
    location_verification VARCHAR(50) DEFAULT 'UNVERIFIED',
    catatan_revisi TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX kosts_city_district_idx ON kosts (city, district);
CREATE INDEX kosts_price_min_idx ON kosts (price_min);
CREATE INDEX kosts_gender_type_idx ON kosts (gender_type);
CREATE INDEX kosts_status_idx ON kosts (status);

CREATE TABLE rooms (
    id BIGSERIAL PRIMARY KEY,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    size VARCHAR(50) NULL,
    facilities JSONB NULL,
    total_rooms INTEGER DEFAULT 1,
    available_rooms INTEGER DEFAULT 1,
    status VARCHAR(50) DEFAULT 'available',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kost_images (
    id BIGSERIAL PRIMARY KEY,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    file_path VARCHAR(2048) NOT NULL,
    url VARCHAR(2048) NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 4. Modul Pencari (Favorit & Survei)
-- ------------------------------------------------------------------------------

CREATE TABLE favorites (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, kost_id)
);

CREATE TABLE survey_requests (
    id BIGSERIAL PRIMARY KEY,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    pencari_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    owner_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    survey_date TIMESTAMP WITH TIME ZONE NOT NULL,
    status VARCHAR(50) DEFAULT 'PENDING',
    notes TEXT NULL,
    owner_notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reviews (
    id BIGSERIAL PRIMARY KEY,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    survey_request_id BIGINT NULL REFERENCES survey_requests(id) ON DELETE SET NULL,
    rating SMALLINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(kost_id, user_id)
);

-- ------------------------------------------------------------------------------
-- 5. Modul Komunikasi (Chatting & Notifikasi)
-- ------------------------------------------------------------------------------

CREATE TABLE chats (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    owner_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    kost_id BIGINT NULL REFERENCES kosts(id) ON DELETE SET NULL,
    last_message TEXT NULL,
    last_message_at TIMESTAMP WITH TIME ZONE NULL,
    unread_count_user INTEGER DEFAULT 0,
    unread_count_owner INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    chat_id BIGINT NOT NULL REFERENCES chats(id) ON DELETE CASCADE,
    sender_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT false,
    data JSONB NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 6. Modul Keamanan & Moderasi
-- ------------------------------------------------------------------------------

CREATE TABLE user_blocks (
    id BIGSERIAL PRIMARY KEY,
    blocker_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blocked_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(blocker_id, blocked_id)
);

CREATE TABLE user_reports (
    id BIGSERIAL PRIMARY KEY,
    reporter_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reported_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason VARCHAR(255) NOT NULL,
    details TEXT NULL,
    status VARCHAR(50) DEFAULT 'Baru',
    admin_notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
    id BIGSERIAL PRIMARY KEY,
    reporter_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    kost_id BIGINT NOT NULL REFERENCES kosts(id) ON DELETE CASCADE,
    reason VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    admin_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(100) NULL,
    target_id BIGINT NULL,
    details JSONB NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE api_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    method VARCHAR(10) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    status_code INTEGER NOT NULL,
    duration INTEGER NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 7. Modul Administrasi, Promosi & Telemetri
-- ------------------------------------------------------------------------------

CREATE TABLE advertisements (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'Banner',
    image_url VARCHAR(2048) NOT NULL,
    target_url VARCHAR(2048) NULL,
    kost_id BIGINT NULL REFERENCES kosts(id) ON DELETE SET NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT true,
    impressions INTEGER DEFAULT 0,
    clicks INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ad_analytics (
    id BIGSERIAL PRIMARY KEY,
    advertisement_id BIGINT NOT NULL REFERENCES advertisements(id) ON DELETE CASCADE,
    event_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE application_releases (
    id BIGSERIAL PRIMARY KEY,
    version_name VARCHAR(50) NOT NULL,
    version_code INTEGER NOT NULL,
    apk_url VARCHAR(2048) NOT NULL,
    file_size VARCHAR(50) NULL,
    min_android VARCHAR(100) DEFAULT 'Android 8.0 (Oreo)',
    is_force_update BOOLEAN DEFAULT false,
    is_latest BOOLEAN DEFAULT false,
    changelog TEXT NULL,
    release_date DATE NOT NULL,
    download_count INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE platform_settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NULL,
    type VARCHAR(50) DEFAULT 'string',
    group_name VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE app_events (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    event_name VARCHAR(100) NOT NULL,
    screen_name VARCHAR(100) NULL,
    event_data JSONB NULL,
    platform VARCHAR(50) DEFAULT 'android',
    app_version VARCHAR(50) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE crash_reports (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    error_message TEXT NOT NULL,
    stack_trace TEXT NOT NULL,
    device_info JSONB NULL,
    app_version VARCHAR(50) NULL,
    platform VARCHAR(50) DEFAULT 'android',
    is_resolved BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================================================
-- END OF SCHEMA
-- ==============================================================================
