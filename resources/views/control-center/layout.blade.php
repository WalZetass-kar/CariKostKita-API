<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CariKostKita API Control Center')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --border-subtle: #f1f5f9;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-bg: #eff6ff;
            --emerald: #059669;
            --emerald-bg: #ecfdf5;
            --rose: #e11d48;
            --rose-bg: #fff1f2;
            --amber: #d97706;
            --amber-bg: #fffbeb;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-page);
            color: var(--text-body);
            line-height: 1.5;
            font-size: 0.875rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Header */
        .header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .brand-link {
            display: flex;
            align-items: baseline;
            gap: 0.4rem;
            text-decoration: none;
            color: var(--text-heading);
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: -0.01em;
        }

        .brand-link .sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
        }

        .nav-item a {
            display: inline-block;
            padding: 0.4rem 0.75rem;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.825rem;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .nav-item a:hover {
            color: var(--text-heading);
            background-color: #f1f5f9;
        }

        .nav-item a.active {
            color: var(--primary);
            background-color: var(--primary-bg);
            font-weight: 600;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background-color: var(--emerald-bg);
            color: var(--emerald);
            border: 1px solid #d1fae5;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background-color: var(--emerald);
            border-radius: 50%;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 1.75rem 1.5rem 3rem;
            flex: 1;
        }

        /* Cards & Panels */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }

        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 0.925rem;
            font-weight: 600;
            color: var(--text-heading);
        }

        .card-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Typography & Utility */
        h1, h2, h3, h4 {
            color: var(--text-heading);
            font-weight: 600;
        }

        .code-inline {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 0.15rem 0.35rem;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.45rem;
            font-size: 0.725rem;
            font-weight: 600;
            border-radius: 4px;
            font-family: var(--font-mono);
            line-height: 1.2;
        }

        .badge-get { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-post { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-put { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-delete { background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .badge-patch { background-color: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

        .badge-auth {
            background-color: #f8fafc;
            color: var(--text-muted);
            border: 1px solid #e2e8f0;
            font-size: 0.7rem;
            font-family: var(--font-sans);
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            font-family: inherit;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--text-body);
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Clean Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table.clean-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.825rem;
        }

        table.clean-table th {
            padding: 0.65rem 1rem;
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border);
        }

        table.clean-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-subtle);
            color: var(--text-body);
            vertical-align: middle;
        }

        table.clean-table tr:last-child td {
            border-bottom: none;
        }

        table.clean-table tr:hover td {
            background-color: #fcfcfd;
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--border);
            background-color: #ffffff;
            padding: 1.25rem 1.5rem;
            font-size: 0.775rem;
            color: var(--text-muted);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            .header-container { height: auto; padding: 0.75rem 1rem; flex-direction: column; gap: 0.75rem; align-items: flex-start; }
            .header-left { flex-direction: column; align-items: flex-start; gap: 0.5rem; width: 100%; }
            .nav-menu { width: 100%; overflow-x: auto; padding-bottom: 0.25rem; }
            .main-container { padding: 1.25rem 1rem; }
            .footer-container { flex-direction: column; gap: 0.5rem; align-items: flex-start; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <a href="/" class="brand-link">
                    <span>CariKostKita API</span>
                    <span class="sub">Control Center</span>
                </a>

                <nav>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a href="/#endpoints">Endpoints</a>
                        </li>
                        <li class="nav-item">
                            <a href="/docs" class="{{ request()->is('docs') ? 'active' : '' }}">Docs</a>
                        </li>
                        <li class="nav-item">
                            <a href="/docs/tester" class="{{ request()->is('docs/tester') ? 'active' : '' }}">Tester</a>
                        </li>
                        <li class="nav-item">
                            <a href="/docs/logs" class="{{ request()->is('docs/logs') ? 'active' : '' }}">Logs</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="header-right">
                <div class="status-pill">
                    <span class="status-dot"></span>
                    <span>API Status: Online</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div>
                CariKostKita REST API & Backend Infrastructure &middot; Single source of truth
            </div>
            <div>
                <a href="/api/health" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 500;">Health Check API (JSON)</a> &middot;
                <span>Version 1.0.0</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
