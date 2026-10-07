@extends('control-center.layout')

@section('title', 'CariKostKita API — Overview')

@section('content')
    <!-- Top Hero Header -->
    <div style="margin-bottom: 1.75rem;">
        <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.25rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); letter-spacing: -0.02em;">CariKostKita API</h1>
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: var(--emerald); font-size: 0.775rem; font-weight: 600;">
                <span style="width: 7px; height: 7px; background-color: var(--emerald); border-radius: 50%;"></span>
                API Online
            </span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem;">REST API & Backend Infrastructure</p>
    </div>

    <!-- System Status Metadata Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-bottom: 1.5rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Framework</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-heading); margin-top: 0.2rem;">Laravel {{ $system['laravel_version'] }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">PHP {{ $system['php_version'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Environment</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-heading); margin-top: 0.2rem;">{{ ucfirst($system['environment']) }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">App Mode: {{ config('app.debug') ? 'Debug' : 'Production' }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Database</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: {{ $health['database']['status'] === 'ok' ? 'var(--emerald)' : 'var(--rose)' }}; margin-top: 0.2rem;">
                    {{ $health['database']['status'] === 'ok' ? 'Connected' : 'Disconnected' }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                    Driver: {{ strtoupper($health['database']['driver']) }}
                    @if($health['database']['latency_ms']) &middot; {{ $health['database']['latency_ms'] }} ms @endif
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Storage Provider</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: {{ $health['storage']['status'] === 'ok' ? 'var(--emerald)' : 'var(--rose)' }}; margin-top: 0.2rem;">
                    {{ $health['storage']['message'] }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Bucket: {{ $health['storage']['bucket'] }}</div>
            </div>
        </div>
    </div>

    <!-- System Health Panel -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">System Health Monitoring</h2>
                <p class="card-subtitle">Real-time status check of infrastructure components</p>
            </div>
            <a href="/api/health" target="_blank" class="btn btn-secondary" style="font-size: 0.75rem;">
                JSON Health Endpoint ↗
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="clean-table">
                    <thead>
                        <tr>
                            <th style="width: 220px;">Component</th>
                            <th style="width: 140px;">Status</th>
                            <th>Diagnostics / Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>API Gateway</strong></td>
                            <td>
                                <span class="status-pill" style="background-color: var(--emerald-bg); color: var(--emerald); border-color: #d1fae5;">
                                    <span class="status-dot"></span> Operational
                                </span>
                            </td>
                            <td style="color: var(--text-muted);">Serving requests on <span class="code-inline">{{ $system['app_url'] }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Database Engine</strong></td>
                            <td>
                                @if($health['database']['status'] === 'ok')
                                    <span class="status-pill" style="background-color: var(--emerald-bg); color: var(--emerald); border-color: #d1fae5;">
                                        <span class="status-dot"></span> Connected
                                    </span>
                                @else
                                    <span class="status-pill" style="background-color: var(--rose-bg); color: var(--rose); border-color: #fecdd3;">
                                        <span class="status-dot" style="background-color: var(--rose);"></span> Connection Failed
                                    </span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted);">
                                Driver: <strong>{{ strtoupper($health['database']['driver']) }}</strong>
                                @if($health['database']['latency_ms']) &middot; Latency: <strong>{{ $health['database']['latency_ms'] }} ms</strong> @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Supabase Storage</strong></td>
                            <td>
                                @if($health['storage']['status'] === 'ok')
                                    <span class="status-pill" style="background-color: var(--emerald-bg); color: var(--emerald); border-color: #d1fae5;">
                                        <span class="status-dot"></span> Connected
                                    </span>
                                @else
                                    <span class="status-pill" style="background-color: var(--rose-bg); color: var(--rose); border-color: #fecdd3;">
                                        <span class="status-dot" style="background-color: var(--rose);"></span> Storage Unavailable
                                    </span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted);">
                                Provider: <strong>{{ $health['storage']['provider'] }}</strong> &middot; Target Bucket: <span class="code-inline">{{ $health['storage']['bucket'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Cache Subsystem</strong></td>
                            <td>
                                @if($health['cache']['status'] === 'ok')
                                    <span class="status-pill" style="background-color: var(--emerald-bg); color: var(--emerald); border-color: #d1fae5;">
                                        <span class="status-dot"></span> Operational
                                    </span>
                                @else
                                    <span class="status-pill" style="background-color: var(--rose-bg); color: var(--rose); border-color: #fecdd3;">
                                        <span class="status-dot" style="background-color: var(--rose);"></span> Cache Failed
                                    </span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted);">Store: <strong>{{ config('cache.default') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- API Statistics -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Database Records & Statistics</h2>
                <p class="card-subtitle">Live counts from database tables (no mock data)</p>
            </div>
            <span style="font-size: 0.775rem; color: var(--text-muted); font-family: var(--font-mono);">
                Server Time: {{ $system['server_time'] }}
            </span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; text-align: center;">
                <div style="padding: 1rem; background-color: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono);">{{ $stats['users'] }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 0.25rem;">Users</div>
                </div>

                <div style="padding: 1rem; background-color: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono);">{{ $stats['kosts'] }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 0.25rem;">Kost</div>
                </div>

                <div style="padding: 1rem; background-color: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono);">{{ $stats['rooms'] }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 0.25rem;">Rooms</div>
                </div>

                <div style="padding: 1rem; background-color: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono);">{{ $stats['favorites'] }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 0.25rem;">Favorites</div>
                </div>

                <div style="padding: 1rem; background-color: #f8fafc; border-radius: 6px; border: 1px solid var(--border);">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono);">{{ $stats['chats'] }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 0.25rem;">Chats</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Endpoint Explorer -->
    <div class="card" id="endpoints">
        <div class="card-header">
            <div>
                <h2 class="card-title">Endpoint Explorer</h2>
                <p class="card-subtitle">Showing {{ $totalEndpoints }} active REST API routes categorized by domain</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/docs/tester" class="btn btn-primary" style="font-size: 0.75rem;">Open API Tester</a>
                <a href="/docs" class="btn btn-secondary" style="font-size: 0.75rem;">Full Documentation</a>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            @foreach($groupedRoutes as $category => $routes)
                <div style="background-color: #f8fafc; padding: 0.65rem 1.25rem; font-weight: 600; font-size: 0.8rem; color: var(--text-muted); border-bottom: 1px solid var(--border); border-top: {{ !$loop->first ? '1px solid var(--border)' : 'none' }}; text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ $category }} ({{ count($routes) }})
                </div>
                <div class="table-responsive">
                    <table class="clean-table">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Method</th>
                                <th style="width: 280px;">URL</th>
                                <th>Description</th>
                                <th style="width: 120px;">Auth</th>
                                <th style="width: 120px;">Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routes as $endpoint)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ strtolower($endpoint['method']) }}">{{ $endpoint['method'] }}</span>
                                    </td>
                                    <td>
                                        <code style="font-family: var(--font-mono); font-weight: 600; color: var(--text-heading); font-size: 0.8rem;">
                                            {{ $endpoint['uri'] }}
                                        </code>
                                    </td>
                                    <td style="color: var(--text-body);">
                                        {{ $endpoint['description'] }}
                                    </td>
                                    <td>
                                        <span class="badge badge-auth">{{ $endpoint['auth'] }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                                            {{ $endpoint['roles'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
