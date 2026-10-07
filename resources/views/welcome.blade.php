<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CariKostKita — Backend RESTful API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #090d16;
            --surface: #111726;
            --surface-border: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --emerald: #10b981;
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
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 860px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(16, 185, 129, 0.12);
            color: var(--emerald);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            background-color: var(--emerald);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--emerald);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--surface);
            border: 1px solid var(--surface-border);
            border-radius: 1rem;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        .card h2 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #e2e8f0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .info-pill {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--surface-border);
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
        }

        .info-pill-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .info-pill-val {
            font-size: 0.95rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-top: 0.15rem;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            text-align: left;
        }

        th {
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--surface-border);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(30, 41, 59, 0.5);
            color: #cbd5e1;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .code-tag {
            font-family: var(--font-mono);
            background: #0f172a;
            border: 1px solid #1e293b;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.825rem;
            color: #38bdf8;
        }

        .method {
            font-family: var(--font-mono);
            font-weight: 700;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.35rem;
            display: inline-block;
        }

        .method-get { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .method-post { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .method-put { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .method-delete { background: rgba(239, 68, 68, 0.2); color: #f87171; }

        .btn-link {
            color: #38bdf8;
            text-decoration: none;
            transition: color 0.15s;
        }

        .btn-link:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="badge-status">
                <span class="badge-dot"></span>
                API Server Active & Operational
            </div>
            <h1>CariKostKita API</h1>
            <p class="subtitle">Backend RESTful API pusat untuk Android App, Landing Page, dan Admin Dashboard CariKostKita.</p>
        </header>

        <!-- System Architecture Pill Cards -->
        <div class="card">
            <h2>⚙️ System Overview</h2>
            <div class="grid">
                <div class="info-pill">
                    <div class="info-pill-label">Framework & Runtime</div>
                    <div class="info-pill-val">Laravel 12 (PHP 8.2)</div>
                </div>
                <div class="info-pill">
                    <div class="info-pill-label">Database Engine</div>
                    <div class="info-pill-val">PostgreSQL / Supabase</div>
                </div>
                <div class="info-pill">
                    <div class="info-pill-label">Storage Provider</div>
                    <div class="info-pill-val">Supabase Storage</div>
                </div>
                <div class="info-pill">
                    <div class="info-pill-label">Authentication</div>
                    <div class="info-pill-val">Laravel Sanctum Bearer Token</div>
                </div>
            </div>
        </div>

        <!-- Default Test Accounts -->
        <div class="card">
            <h2>🔑 Akun Uji Coba Bawaan (Default Password: <span class="code-tag">password123</span>)</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Email Login</th>
                            <th>Akses Fitur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: #f43f5e;">Admin</strong></td>
                            <td><span class="code-tag">admin@carikostkita.com</span></td>
                            <td>Verifikasi/Approval Kost, Moderasi Laporan, Kelola User</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #fb923c;">Pemilik Kost</strong></td>
                            <td><span class="code-tag">pemilik@carikostkita.com</span></td>
                            <td>Pendaftaran Kost, CRUD Kamar, Upload Galeri Foto</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #38bdf8;">Pencari Kost</strong></td>
                            <td><span class="code-tag">pencari@carikostkita.com</span></td>
                            <td>Cari/Filter Kost, Favorit, Chat Pemilik Kost</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Endpoint Testing -->
        <div class="card">
            <h2>🚀 Quick Test Endpoints</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint URL</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><span class="code-tag">/api/kosts</span></td>
                            <td>Daftar kost publik yang sudah disetujui</td>
                            <td><a class="btn-link" href="{{ url('/api/kosts') }}" target="_blank">Buka JSON ↗</a></td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><span class="code-tag">/api/kosts/1</span></td>
                            <td>Detail kamar & fasilitas kost ID 1</td>
                            <td><a class="btn-link" href="{{ url('/api/kosts/1') }}" target="_blank">Buka JSON ↗</a></td>
                        </tr>
                        <tr>
                            <td><span class="method method-post">POST</span></td>
                            <td><span class="code-tag">/api/login</span></td>
                            <td>Autentikasi akun & perolehan token</td>
                            <td><span style="color: #64748b; font-size: 0.8rem;">via Postman / Mobile</span></td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><span class="code-tag">/api/user</span></td>
                            <td>Profil akun terautentikasi (Bearer Token)</td>
                            <td><span style="color: #64748b; font-size: 0.8rem;">Auth Required</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="footer">
            &copy; {{ date('Y') }} CariKostKita Team. Single Source of Truth for Android, Landing Page, & Admin.
        </footer>
    </div>
</body>
</html>
