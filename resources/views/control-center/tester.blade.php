@extends('control-center.layout')

@section('title', 'CariKostKita API — REST API Tester')

@push('styles')
<style>
    .tester-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-label {
        display: block;
        font-size: 0.775rem;
        font-weight: 600;
        color: var(--text-heading);
        margin-bottom: 0.35rem;
    }
    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.825rem;
        font-family: inherit;
        background: #ffffff;
        color: var(--text-heading);
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-bg);
    }
    .code-editor {
        font-family: var(--font-mono);
        font-size: 0.8rem;
        background: #0f172a;
        color: #f8fafc;
        border: 1px solid #1e293b;
        border-radius: 6px;
        padding: 0.75rem;
        width: 100%;
        min-height: 140px;
        resize: vertical;
    }
    .response-pane {
        background: #0f172a;
        border: 1px solid #1e293b;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        min-height: 480px;
        overflow: hidden;
    }
    .response-header {
        padding: 0.65rem 1rem;
        background: #1e293b;
        border-bottom: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .response-content {
        padding: 1rem;
        flex: 1;
        overflow: auto;
        color: #e2e8f0;
        font-family: var(--font-mono);
        font-size: 0.775rem;
        white-space: pre-wrap;
        word-break: break-all;
    }
    @media (max-width: 900px) {
        .tester-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 1.5rem; display: flex; align-items: flex-end; justify-content: space-between;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); letter-spacing: -0.02em;">REST API Tester</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.2rem;">Live interactive API console to test and debug endpoints directly</p>
        </div>
        <div>
            <button onclick="fillAuthToken()" class="btn btn-secondary" style="font-size: 0.75rem;">
                Quick Login Token (Pencari)
            </button>
        </div>
    </div>

    <div class="tester-grid">
        <!-- Request Builder Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <span class="card-title">Request Builder</span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Client-side execution</span>
            </div>
            <div class="card-body">
                <!-- Preset Selector -->
                <div class="form-group">
                    <label class="form-label">Pilih Endpoint Preset</label>
                    <select id="presetSelect" class="form-control" onchange="loadPreset(this.value)">
                        <option value="">-- Pilih dari route terdaftar --</option>
                        <option value="GET|/api/kosts|">GET /api/kosts (List Kost)</option>
                        <option value="GET|/api/kosts/1|">GET /api/kosts/1 (Detail Kost)</option>
                        <option value="POST|/api/login|{&quot;email&quot;:&quot;pencari@carikostkita.com&quot;,&quot;password&quot;:&quot;password123&quot;}">POST /api/login</option>
                        <option value="GET|/api/user|">GET /api/user (Profile)</option>
                        <option value="GET|/api/favorites|">GET /api/favorites</option>
                        <option value="GET|/api/chats|">GET /api/chats</option>
                        <option value="GET|/api/health|">GET /api/health</option>
                    </select>
                </div>

                <!-- Method & URL -->
                <div class="form-group">
                    <label class="form-label">HTTP Method & Path</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="reqMethod" class="form-control" style="width: 120px; font-weight: 600;">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                            <option value="PATCH">PATCH</option>
                        </select>
                        <input type="text" id="reqUrl" class="form-control" value="/api/kosts" style="font-family: var(--font-mono); font-weight: 500;">
                    </div>
                </div>

                <!-- Headers -->
                <div class="form-group">
                    <label class="form-label">Bearer Token (Authorization)</label>
                    <input type="text" id="authToken" class="form-control" placeholder="1|sanctum_plain_text_token..." style="font-family: var(--font-mono);">
                </div>

                <!-- Request Body -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label class="form-label" style="margin-bottom: 0;">Request Body (JSON)</label>
                        <button type="button" onclick="formatBodyJson()" style="background: none; border: none; color: var(--primary); font-size: 0.725rem; cursor: pointer; font-weight: 600;">Format JSON</button>
                    </div>
                    <textarea id="reqBody" class="code-editor" placeholder="{ &quot;key&quot;: &quot;value&quot; }"></textarea>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 0.5rem; justify-content: space-between; align-items: center; margin-top: 1.25rem;">
                    <button type="button" onclick="clearTester()" class="btn btn-secondary">Clear</button>
                    <button type="button" id="sendBtn" onclick="sendApiRequest()" class="btn btn-primary" style="padding: 0.55rem 1.5rem;">
                        <span>Send Request</span> &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- Response Pane -->
        <div class="response-pane">
            <div class="response-header">
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span id="resStatus" style="font-weight: 700; color: #94a3b8;">NO RESPONSE</span>
                    <span id="resTime" style="color: #64748b;">0ms</span>
                </div>
                <div>
                    <button onclick="copyResponse()" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; background: #334155; color: #f8fafc; border: none;">
                        Copy JSON
                    </button>
                </div>
            </div>
            <div id="resBody" class="response-content">
// Tekan "Send Request" untuk menjalankan pemanggilan API...
// Respon JSON, status HTTP, dan latency akan ditampilkan di sini.
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Load pre-selected params from URL if any
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('method')) {
            document.getElementById('reqMethod').value = urlParams.get('method');
        }
        if (urlParams.has('path')) {
            document.getElementById('reqUrl').value = urlParams.get('path');
        }
    });

    function loadPreset(val) {
        if (!val) return;
        const [method, path, body] = val.split('|');
        document.getElementById('reqMethod').value = method;
        document.getElementById('reqUrl').value = path;
        document.getElementById('reqBody').value = body || '';
    }

    function formatBodyJson() {
        const textarea = document.getElementById('reqBody');
        try {
            if (textarea.value.trim()) {
                const parsed = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(parsed, null, 2);
            }
        } catch (e) {
            alert('JSON tidak valid: ' + e.message);
        }
    }

    function clearTester() {
        document.getElementById('reqUrl').value = '/api/kosts';
        document.getElementById('reqBody').value = '';
        document.getElementById('authToken').value = '';
        document.getElementById('resStatus').innerText = 'NO RESPONSE';
        document.getElementById('resStatus').style.color = '#94a3b8';
        document.getElementById('resTime').innerText = '0ms';
        document.getElementById('resBody').innerText = '// Siap untuk request baru...';
    }

    async function fillAuthToken() {
        const btn = event.target;
        btn.innerText = 'Logging in...';
        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: 'pencari@carikostkita.com', password: 'password123' })
            });
            const data = await res.json();
            if (data.success && data.data && data.data.token) {
                document.getElementById('authToken').value = data.data.token;
                btn.innerText = 'Token Loaded ✓';
                setTimeout(() => btn.innerText = 'Quick Login Token (Pencari)', 2500);
            } else {
                alert('Login gagal');
                btn.innerText = 'Quick Login Token (Pencari)';
            }
        } catch (e) {
            alert('Gagal mengambil token: ' + e.message);
            btn.innerText = 'Quick Login Token (Pencari)';
        }
    }

    async function sendApiRequest() {
        const method = document.getElementById('reqMethod').value;
        let path = document.getElementById('reqUrl').value.trim();
        if (!path.startsWith('/')) path = '/' + path;

        const token = document.getElementById('authToken').value.trim();
        const bodyContent = document.getElementById('reqBody').value.trim();
        const sendBtn = document.getElementById('sendBtn');
        const resStatus = document.getElementById('resStatus');
        const resTime = document.getElementById('resTime');
        const resBody = document.getElementById('resBody');

        sendBtn.disabled = true;
        sendBtn.innerText = 'Sending...';
        resBody.innerText = 'Loading response...';

        const headers = {
            'Accept': 'application/json'
        };

        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        const options = {
            method: method,
            headers: headers
        };

        if (method !== 'GET' && method !== 'HEAD' && bodyContent) {
            try {
                JSON.parse(bodyContent);
                headers['Content-Type'] = 'application/json';
                options.body = bodyContent;
            } catch (e) {
                alert('Request Body harus berupa format JSON yang valid.');
                sendBtn.disabled = false;
                sendBtn.innerText = 'Send Request →';
                return;
            }
        }

        const startTime = performance.now();

        try {
            const response = await fetch(path, options);
            const duration = Math.round(performance.now() - startTime);

            const contentType = response.headers.get('content-type') || '';
            let payloadText;

            if (contentType.includes('application/json')) {
                const json = await response.json();
                payloadText = JSON.stringify(json, null, 2);
            } else {
                payloadText = await response.text();
            }

            resStatus.innerText = `HTTP ${response.status} ${response.statusText || ''}`;
            resTime.innerText = `${duration}ms`;

            if (response.ok) {
                resStatus.style.color = '#34d399';
            } else if (response.status >= 400 && response.status < 500) {
                resStatus.style.color = '#fbbf24';
            } else {
                resStatus.style.color = '#f87171';
            }

            resBody.innerText = payloadText;
        } catch (error) {
            const duration = Math.round(performance.now() - startTime);
            resStatus.innerText = 'NETWORK / CORS ERROR';
            resStatus.style.color = '#f87171';
            resTime.innerText = `${duration}ms`;
            resBody.innerText = error.message;
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerText = 'Send Request →';
        }
    }

    function copyResponse() {
        const text = document.getElementById('resBody').innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('Response berhasil disalin ke clipboard!');
        });
    }
</script>
@endpush
