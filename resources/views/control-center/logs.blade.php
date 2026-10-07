@extends('control-center.layout')

@section('title', 'CariKostKita API — Request Logs')

@section('content')
    <div style="margin-bottom: 1.5rem; display: flex; align-items: flex-end; justify-content: space-between;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); letter-spacing: -0.02em;">Request Logs & Telemetry</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.2rem;">Live telemetry and latency monitoring for incoming API traffic</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button onclick="window.location.reload()" class="btn btn-secondary" style="font-size: 0.8rem;">
                Refresh Logs
            </button>
        </div>
    </div>

    <!-- Metrics Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Recorded Requests</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); font-family: var(--font-mono); margin-top: 0.2rem;">
                    {{ number_format($totalLogged) }}
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Average Response Latency</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); font-family: var(--font-mono); margin-top: 0.2rem;">
                    {{ $avgDuration }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">ms</span>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Privacy & Security Filter</div>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--emerald); margin-top: 0.35rem;">
                    Tokens & Passwords Redacted
                </div>
                <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 0.15rem;">No plaintext credentials stored</div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <div>
                <span class="card-title">Recent API Requests</span>
                <span class="card-subtitle" style="margin-left: 0.5rem;">(Latest 25 entries)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="clean-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Timestamp</th>
                            <th style="width: 90px;">Method</th>
                            <th>Endpoint Path</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 120px;">Latency</th>
                            <th style="width: 130px;">Client IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $log->created_at->format('H:i:s') }} <span style="font-size: 0.7rem; color: #94a3b8;">{{ $log->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ strtolower($log->method) }}">{{ $log->method }}</span>
                                </td>
                                <td>
                                    <code style="font-family: var(--font-mono); font-weight: 600; color: var(--text-heading); font-size: 0.8rem;">
                                        {{ $log->path }}
                                    </code>
                                </td>
                                <td>
                                    @if($log->status >= 200 && $log->status < 300)
                                        <span class="badge" style="background-color: var(--emerald-bg); color: var(--emerald); border: 1px solid #a7f3d0;">
                                            {{ $log->status }} OK
                                        </span>
                                    @elseif($log->status >= 400 && $log->status < 500)
                                        <span class="badge" style="background-color: var(--amber-bg); color: var(--amber); border: 1px solid #fde68a;">
                                            {{ $log->status }}
                                        </span>
                                    @else
                                        <span class="badge" style="background-color: var(--rose-bg); color: var(--rose); border: 1px solid #fecdd3;">
                                            {{ $log->status }} ERR
                                        </span>
                                    @endif
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 0.775rem;">
                                    <span style="color: {{ $log->duration_ms > 300 ? 'var(--amber)' : 'var(--text-body)' }}; font-weight: 600;">
                                        {{ $log->duration_ms }} ms
                                    </span>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    Belum ada log request API. Jalankan request melalui <a href="/docs/tester" style="color: var(--primary);">REST Tester</a> atau API client untuk melihat data telemetri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div style="padding: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
