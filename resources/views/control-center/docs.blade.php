@extends('control-center.layout')

@section('title', 'CariKostKita API — Documentation')

@push('styles')
<style>
    .doc-section {
        margin-bottom: 2.5rem;
    }
    .doc-section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-heading);
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .endpoint-item {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .endpoint-header {
        padding: 0.85rem 1.25rem;
        background: #fcfcfd;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .endpoint-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-heading);
    }
    .endpoint-body {
        padding: 1.25rem;
    }
    .endpoint-desc {
        color: var(--text-body);
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    .code-box {
        background: #0f172a;
        color: #e2e8f0;
        font-family: var(--font-mono);
        font-size: 0.775rem;
        padding: 0.85rem 1rem;
        border-radius: 6px;
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
        margin-top: 0.35rem;
        margin-bottom: 0.85rem;
        border: 1px solid #1e293b;
    }
    .field-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 0.75rem;
        display: block;
    }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 1.5rem; display: flex; align-items: flex-end; justify-content: space-between;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); letter-spacing: -0.02em;">API Documentation</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.2rem;">Complete RESTful API specifications and reference for CariKostKita clients</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="/docs/tester" class="btn btn-primary" style="font-size: 0.8rem;">Open REST Tester &rarr;</a>
        </div>
    </div>

    <!-- Quick Overview Box -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 600; color: var(--text-heading); margin-bottom: 0.35rem;">Base URL</h3>
                    <code style="font-family: var(--font-mono); font-size: 0.825rem; color: var(--primary); background: var(--primary-bg); padding: 0.2rem 0.5rem; border-radius: 4px;">{{ url('/api') }}</code>
                </div>
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 600; color: var(--text-heading); margin-bottom: 0.35rem;">Headers Standar</h3>
                    <div style="font-family: var(--font-mono); font-size: 0.775rem; color: var(--text-muted);">
                        Content-Type: application/json<br>
                        Accept: application/json<br>
                        Authorization: Bearer {token}
                    </div>
                </div>
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 600; color: var(--text-heading); margin-bottom: 0.35rem;">Status Response</h3>
                    <div style="font-size: 0.775rem; color: var(--text-muted);">
                        200 OK &middot; 201 Created &middot; 401 Unauthorized &middot; 403 Forbidden &middot; 404 Not Found &middot; 422 Validation Error
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped Endpoints -->
    @foreach($groupedRoutes as $category => $routes)
        <div class="doc-section" id="{{ strtolower(str_replace(' ', '-', $category)) }}">
            <div class="doc-section-title">
                <span>{{ $category }}</span>
                <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted); font-family: var(--font-mono);">({{ count($routes) }} endpoints)</span>
            </div>

            @foreach($routes as $endpoint)
                <div class="endpoint-item">
                    <div class="endpoint-header">
                        <div class="endpoint-title">
                            <span class="badge badge-{{ strtolower($endpoint['method']) }}">{{ $endpoint['method'] }}</span>
                            <span>{{ $endpoint['uri'] }}</span>
                        </div>
                        <div style="display: flex; gap: 0.4rem; align-items: center;">
                            <span class="badge badge-auth">{{ $endpoint['auth'] }}</span>
                            @if($endpoint['roles'] !== 'Semua Pengguna')
                                <span class="badge" style="background-color: #f1f5f9; color: var(--text-body); border-color: var(--border);">
                                    Role: {{ $endpoint['roles'] }}
                                </span>
                            @endif
                            <a href="/docs/tester?method={{ $endpoint['method'] }}&path={{ urlencode($endpoint['uri']) }}" 
                               class="btn btn-secondary" 
                               style="padding: 0.2rem 0.5rem; font-size: 0.725rem;">
                               Test
                            </a>
                        </div>
                    </div>

                    <div class="endpoint-body">
                        <p class="endpoint-desc">{{ $endpoint['description'] }}</p>

                        @if(!empty($endpoint['parameters']))
                            <span class="field-label">Query / Path Parameters</span>
                            <div class="table-responsive" style="margin-top: 0.35rem; margin-bottom: 0.85rem;">
                                <table class="clean-table" style="border: 1px solid var(--border);">
                                    <thead>
                                        <tr>
                                            <th style="width: 150px;">Parameter</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($endpoint['parameters'] as $param => $desc)
                                            <tr>
                                                <td><code class="code-inline">{{ $param }}</code></td>
                                                <td>{{ $desc }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if(!empty($endpoint['request_body']))
                            <span class="field-label">Request Body Example</span>
                            <div class="code-box">{{ json_encode($endpoint['request_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        @endif

                        @if(!empty($endpoint['response_example']))
                            <span class="field-label">Success Response Example (HTTP 200 / 201)</span>
                            <div class="code-box">{{ json_encode($endpoint['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        @endif

                        @if(!empty($endpoint['error_example']))
                            <span class="field-label">Error Response Example</span>
                            <div class="code-box" style="border-color: #451a03;">{{ json_encode($endpoint['error_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
