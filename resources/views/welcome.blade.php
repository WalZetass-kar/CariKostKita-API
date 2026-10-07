<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CariKostKita — Platform Pencarian Kost Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --accent: #f43f5e;
            --dark: #0f172a;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --font: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font);
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Navbar */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.85rem 1.5rem;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -0.03em;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.75rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.925rem;
            transition: color 0.15s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.55rem 1.15rem;
            border-radius: 0.6rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
        }

        .btn-outline {
            border: 1px solid var(--border);
            background: white;
            color: var(--text-dark);
        }

        .btn-outline:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
            padding: 3.5rem 1.5rem 2.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .hero-container {
            max-width: 820px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.825rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }

        .hero-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
        }

        .hero-title span {
            color: var(--primary);
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* Search Filter Box */
        .search-card {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            display: grid;
            grid-template-columns: 2fr 1.2fr 1fr auto;
            gap: 0.75rem;
            align-items: center;
            text-align: left;
        }

        .search-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0 0.5rem;
        }

        .search-input-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .search-input-group input,
        .search-input-group select {
            border: none;
            outline: none;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            background: transparent;
            font-family: inherit;
            width: 100%;
        }

        /* Catalog Section */
        .section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 0.25rem;
        }

        /* Grid Kartu Kost */
        .kost-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.75rem;
        }

        .kost-card {
            background: white;
            border-radius: 1rem;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .kost-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
        }

        .kost-thumb {
            height: 200px;
            background: linear-gradient(135deg, #1e293b, #334155);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .kost-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .kost-thumb-placeholder {
            font-size: 3rem;
            opacity: 0.4;
        }

        .badge-gender {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-campur { background: #8b5cf6; color: white; }
        .badge-putri { background: #ec4899; color: white; }
        .badge-putra { background: #0284c7; color: white; }

        .badge-verified {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 0.3rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            backdrop-filter: blur(4px);
        }

        .kost-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .kost-location {
            font-size: 0.825rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .kost-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.35;
        }

        .kost-facilities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
        }

        .facility-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 0.2rem 0.55rem;
            border-radius: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .kost-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .kost-price {
            display: flex;
            flex-direction: column;
        }

        .price-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .price-amount {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary);
        }

        .price-amount span {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* Ecosystem Info Banner */
        .ecosystem-banner {
            background: #0f172a;
            color: white;
            border-radius: 1.25rem;
            padding: 2.5rem;
            margin: 2rem 0;
        }

        .ecosystem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .ecosystem-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.85rem;
            padding: 1.25rem;
        }

        .ecosystem-card h3 {
            font-size: 1.05rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ecosystem-card p {
            color: #94a3b8;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Modal Detail */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 1.25rem;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .modal-close {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .room-item {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Footer */
        footer {
            background: white;
            border-top: 1px solid var(--border);
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero-title { font-size: 2rem; }
            .search-card { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="brand">
                <div class="brand-icon">🏠</div>
                <span>CariKostKita</span>
            </a>

            <ul class="nav-links">
                <li><a href="#katalog">Jelajah Kost</a></li>
                <li><a href="#ekosistem">Ekosistem Platform</a></li>
                <li><a href="/api/kosts" target="_blank">Endpoint API ↗</a></li>
            </ul>

            <div class="nav-actions">
                <a href="/api/kosts" target="_blank" class="btn btn-outline">Test REST API</a>
                <button onclick="openApiDocsModal()" class="btn btn-primary">Akun & Auth</button>
            </div>
        </div>
    </nav>

    <!-- Hero Search -->
    <header class="hero">
        <div class="hero-container">
            <div class="hero-badge">✨ Platform Kost Modern #1 Pekanbaru</div>
            <h1 class="hero-title">Temukan Kost Nyaman & Strategis <span>Tanpa Ribet</span></h1>
            <p class="hero-desc">Pilihan kost terlengkap dengan verifikasi langsung, informasi ketersediaan kamar real-time, dan harga transparan.</p>

            <!-- Search Filter Bar -->
            <div class="search-card">
                <div class="search-input-group">
                    <label>Nama Kost / Lokasi</label>
                    <input type="text" id="filterSearch" placeholder="Cth: Mawar, Panam, Sidomulyo..." oninput="filterKosts()">
                </div>

                <div class="search-input-group" style="border-left: 1px solid var(--border);">
                    <label>Kecamatan / Area</label>
                    <select id="filterDistrict" onchange="filterKosts()">
                        <option value="">Semua Kecamatan</option>
                        <option value="Bukit Raya">Bukit Raya</option>
                        <option value="Tampan">Tampan (Panam)</option>
                        <option value="Marpoyan Damai">Marpoyan Damai</option>
                        <option value="Sukajadi">Sukajadi</option>
                    </select>
                </div>

                <div class="search-input-group" style="border-left: 1px solid var(--border);">
                    <label>Tipe Kost</label>
                    <select id="filterGender" onchange="filterKosts()">
                        <option value="">Semua Tipe</option>
                        <option value="campur">Campur</option>
                        <option value="putri">Putri</option>
                        <option value="putra">Putra</option>
                    </select>
                </div>

                <button class="btn btn-primary" onclick="filterKosts()" style="padding: 0.85rem 1.5rem; height: 100%;">
                    🔍 Cari Kost
                </button>
            </div>
        </div>
    </header>

    <!-- Catalog Section -->
    <main class="section" id="katalog">
        <div class="section-header">
            <div>
                <h2 class="section-title">Rekomendasi Kost Terverifikasi</h2>
                <p class="section-subtitle">Kost berkualitas yang telah disetujui dan siap huni</p>
            </div>
            <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted);" id="kostCountLabel">
                Menampilkan {{ $kosts->count() }} Kost
            </span>
        </div>

        <!-- Kost Grid -->
        <div class="kost-grid" id="kostContainer">
            @forelse ($kosts as $kost)
                <div class="kost-card" 
                     data-name="{{ strtolower($kost->name) }}" 
                     data-district="{{ strtolower($kost->district) }}" 
                     data-gender="{{ strtolower($kost->gender_type) }}"
                     data-address="{{ strtolower($kost->address) }}">
                    
                    <div class="kost-thumb">
                        @if($kost->primaryImage)
                            <img src="{{ $kost->primaryImage->url }}" alt="{{ $kost->name }}">
                        @else
                            <div class="kost-thumb-placeholder">🏢</div>
                        @endif

                        <span class="badge-gender badge-{{ $kost->gender_type }}">
                            {{ ucfirst($kost->gender_type) }}
                        </span>

                        <span class="badge-verified">
                            ✓ Terverifikasi
                        </span>
                    </div>

                    <div class="kost-body">
                        <div class="kost-location">
                            📍 {{ $kost->district }}, {{ $kost->city }}
                        </div>
                        <h3 class="kost-name">{{ $kost->name }}</h3>

                        <div class="kost-facilities">
                            @if(is_array($kost->facilities))
                                @foreach(array_slice($kost->facilities, 0, 4) as $facility)
                                    <span class="facility-tag">{{ $facility }}</span>
                                @endforeach
                            @endif
                        </div>

                        <div class="kost-footer">
                            <div class="kost-price">
                                <span class="price-label">Mulai dari</span>
                                <div class="price-amount">
                                    Rp {{ number_format($kost->price_min, 0, ',', '.') }}<span>/bln</span>
                                </div>
                            </div>

                            <button onclick="showDetailModal({{ json_encode($kost) }})" class="btn btn-outline" style="font-size: 0.8rem;">
                                Lihat Kamar ↗
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: white; border-radius: 1rem; border: 1px solid var(--border);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--dark);">Belum ada kost tersedia</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">Silakan jalankan database seeder untuk mengisi data kost awal.</p>
                </div>
            @endforelse
        </div>

        <!-- Ecosystem Section -->
        <section class="ecosystem-banner" id="ekosistem">
            <h2 style="font-size: 1.5rem; font-weight: 800;">🌐 Arsitektur Ekosistem CariKostKita</h2>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-top: 0.25rem;">
                Platform ini terhubung secara terpusat melalui Laravel REST API ke seluruh aplikasi klien:
            </p>

            <div class="ecosystem-grid">
                <div class="ecosystem-card">
                    <h3>📱 Android Mobile App</h3>
                    <p>Aplikasi mobile untuk pencari kost (berada di folder <code>CariKostKita mobile app</code>). Terhubung ke endpoint API ini.</p>
                </div>

                <div class="ecosystem-card">
                    <h3>💻 Next.js Landing Page</h3>
                    <p>Website pemasaran dan link download APK (berada di folder <code>Landing Page</code>).</p>
                </div>

                <div class="ecosystem-card">
                    <h3>🛡️ Admin Dashboard</h3>
                    <p>Portal moderasi verifikasi kost dan kelola pengguna (berada di folder <code>Admin-Gue</code>).</p>
                </div>

                <div class="ecosystem-card">
                    <h3>⚡ Laravel REST API</h3>
                    <p>Backend tunggal (server aktif saat ini) yang mengelola PostgreSQL Supabase, otentikasi Sanctum, dan Supabase Storage.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Detail Kost Modal -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span id="modalGenderBadge" class="badge-gender badge-campur" style="position: static; margin-bottom: 0.5rem; display: inline-block;"></span>
                    <h2 id="modalKostName" style="font-size: 1.35rem; font-weight: 800; color: var(--dark);"></h2>
                    <p id="modalKostLocation" style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.2rem;"></p>
                </div>
                <button class="modal-close" onclick="closeDetailModal()">✕</button>
            </div>
            <div class="modal-body">
                <h4 style="font-weight: 700; font-size: 0.95rem; color: var(--dark); margin-bottom: 0.4rem;">Deskripsi Kost</h4>
                <p id="modalKostDesc" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;"></p>

                <h4 style="font-weight: 700; font-size: 0.95rem; color: var(--dark); margin-bottom: 0.4rem;">Fasilitas Kost</h4>
                <div id="modalFacilities" class="kost-facilities" style="margin-bottom: 1.5rem;"></div>

                <h4 style="font-weight: 700; font-size: 0.95rem; color: var(--dark); margin-bottom: 0.4rem;">Pilihan Tipe Kamar</h4>
                <div id="modalRoomsList"></div>

                <div style="margin-top: 1.75rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Pemilik Kost</span>
                        <div id="modalOwnerName" style="font-weight: 700; color: var(--dark);"></div>
                    </div>
                    <a id="modalChatBtn" href="#" class="btn btn-primary" target="_blank">
                        💬 Hubungi Pemilik
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer & Auth Modal -->
    <div class="modal-overlay" id="authModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark);">🔑 Akun Demo & Developer API</h2>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Gunakan akun berikut untuk menguji API di Postman atau Mobile App</p>
                </div>
                <button class="modal-close" onclick="closeApiDocsModal()">✕</button>
            </div>
            <div class="modal-body">
                <div style="background: #f1f5f9; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1rem; font-size: 0.875rem;">
                    <strong>Password seluruh akun demo:</strong> <code style="font-family: var(--font-mono); background: #e2e8f0; padding: 0.2rem 0.4rem; border-radius: 4px;">password123</code>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th style="padding: 0.6rem 0; color: var(--text-muted);">Role</th>
                        <th style="padding: 0.6rem 0; color: var(--text-muted);">Email</th>
                        <th style="padding: 0.6rem 0; color: var(--text-muted);">Hak Akses</th>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.6rem 0;"><strong style="color: #f43f5e;">Admin</strong></td>
                        <td style="padding: 0.6rem 0;"><code>admin@carikostkita.com</code></td>
                        <td style="padding: 0.6rem 0; color: var(--text-muted);">Verifikasi & Reject Kost</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.6rem 0;"><strong style="color: #fb923c;">Pemilik</strong></td>
                        <td style="padding: 0.6rem 0;"><code>pemilik@carikostkita.com</code></td>
                        <td style="padding: 0.6rem 0; color: var(--text-muted);">Pendaftaran Kost & Kamar</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.6rem 0;"><strong style="color: #0284c7;">Pencari</strong></td>
                        <td style="padding: 0.6rem 0;"><code>pencari@carikostkita.com</code></td>
                        <td style="padding: 0.6rem 0; color: var(--text-muted);">Favorit & Chatting</td>
                    </tr>
                </table>

                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <a href="/api/kosts" target="_blank" class="btn btn-outline" style="flex: 1;">Lihat JSON Kost ↗</a>
                    <button onclick="closeApiDocsModal()" class="btn btn-primary" style="flex: 1;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} <strong>CariKostKita</strong>. Platform Pencarian Kost & Backend API Service.</p>
    </footer>

    <!-- Filter & Modal Scripts -->
    <script>
        function filterKosts() {
            const search = document.getElementById('filterSearch').value.toLowerCase();
            const district = document.getElementById('filterDistrict').value.toLowerCase();
            const gender = document.getElementById('filterGender').value.toLowerCase();

            const cards = document.querySelectorAll('.kost-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const cardDistrict = card.getAttribute('data-district');
                const cardGender = card.getAttribute('data-gender');
                const address = card.getAttribute('data-address');

                const matchSearch = !search || name.includes(search) || address.includes(search);
                const matchDistrict = !district || cardDistrict.includes(district);
                const matchGender = !gender || cardGender === gender;

                if (matchSearch && matchDistrict && matchGender) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('kostCountLabel').innerText = `Menampilkan ${visibleCount} Kost`;
        }

        function showDetailModal(kost) {
            document.getElementById('modalKostName').innerText = kost.name;
            document.getElementById('modalKostLocation').innerText = `📍 ${kost.address}, ${kost.district}, ${kost.city}`;
            document.getElementById('modalKostDesc').innerText = kost.description || 'Tidak ada deskripsi.';
            
            const badge = document.getElementById('modalGenderBadge');
            badge.className = `badge-gender badge-${kost.gender_type}`;
            badge.innerText = kost.gender_type.toUpperCase();

            // Facilities
            const facContainer = document.getElementById('modalFacilities');
            facContainer.innerHTML = '';
            if (Array.isArray(kost.facilities)) {
                kost.facilities.forEach(f => {
                    const span = document.createElement('span');
                    span.className = 'facility-tag';
                    span.innerText = f;
                    facContainer.appendChild(span);
                });
            }

            // Rooms
            const roomsContainer = document.getElementById('modalRoomsList');
            roomsContainer.innerHTML = '';
            if (kost.rooms && kost.rooms.length > 0) {
                kost.rooms.forEach(r => {
                    const div = document.createElement('div');
                    div.className = 'room-item';
                    div.innerHTML = `
                        <div>
                            <strong style="color: var(--dark);">${r.name}</strong> (${r.size || 'Ukuran standar'})
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                                Tersedia ${r.available_rooms} dari ${r.total_rooms} kamar
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <strong style="color: var(--primary);">Rp ${new Intl.NumberFormat('id-ID').format(r.price)}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">/bulan</div>
                        </div>
                    `;
                    roomsContainer.appendChild(div);
                });
            } else {
                roomsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.85rem;">Belum ada data tipe kamar.</p>';
            }

            // Owner
            document.getElementById('modalOwnerName').innerText = kost.owner ? kost.owner.name : 'Pemilik Kost';
            const phone = kost.owner && kost.owner.phone ? kost.owner.phone.replace(/[^0-9]/g, '') : '6281234567890';
            document.getElementById('modalChatBtn').href = `https://wa.me/${phone}?text=Halo%20saya%20tertarik%20dengan%20kost%20${encodeURIComponent(kost.name)}`;

            document.getElementById('detailModal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        function openApiDocsModal() {
            document.getElementById('authModal').classList.add('active');
        }

        function closeApiDocsModal() {
            document.getElementById('authModal').classList.remove('active');
        }

        // Close on backdrop click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeDetailModal();
                closeApiDocsModal();
            }
        }
    </script>
</body>
</html>
