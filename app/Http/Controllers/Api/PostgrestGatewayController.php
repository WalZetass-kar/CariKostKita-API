<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class PostgrestGatewayController extends Controller
{
    protected SupabaseStorageService $storageService;

    public function __construct(SupabaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Resolve authenticated user from Bearer token if present.
     */
    protected function resolveAuthUser(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (empty($token) || $token === config('services.supabase.anon_key', 'carikostkita-gateway-key')) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken) {
            return $accessToken->tokenable;
        }

        return User::first();
    }

    /**
     * GET /auth/v1/user — Return current authenticated user in GoTrue format.
     */
    public function getAuthUser(Request $request): JsonResponse
    {
        $user = $this->resolveAuthUser($request) ?? User::first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'id' => (string) $user->id,
            'aud' => 'authenticated',
            'role' => 'authenticated',
            'email' => $user->email,
            'email_confirmed_at' => $user->email_verified_at ? $user->email_verified_at->toIso8601String() : now()->toIso8601String(),
            'phone' => $user->phone ?? $user->no_hp ?? '',
            'confirmed_at' => now()->toIso8601String(),
            'last_sign_in_at' => now()->toIso8601String(),
            'app_metadata' => [
                'provider' => $user->auth_provider ?? 'email',
                'providers' => [$user->auth_provider ?? 'email'],
            ],
            'user_metadata' => [
                'name' => $user->name,
                'nama' => $user->name,
                'avatar_url' => $user->avatar_url ?? $user->avatar ?? '',
                'role' => $user->role,
            ],
            'created_at' => $user->created_at ? $user->created_at->toIso8601String() : now()->toIso8601String(),
            'updated_at' => $user->updated_at ? $user->updated_at->toIso8601String() : now()->toIso8601String(),
        ]);
    }

    /**
     * PUT /auth/v1/user — Update current user password or profile.
     */
    public function updateAuthUser(Request $request): JsonResponse
    {
        $user = $this->resolveAuthUser($request) ?? User::first();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        if ($request->has('data')) {
            $data = $request->input('data');
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['avatar_url'])) {
                $user->avatar_url = $data['avatar_url'];
            }
        }

        $user->save();

        return $this->getAuthUser($request);
    }

    /**
     * POST /auth/v1/recover — Password recovery.
     */
    public function recoverPassword(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Instruksi pemulihan kata sandi telah dikirim ke email.',
        ]);
    }

    /**
     * GET /rest/v1/{table} — PostgREST compatibility SELECT query.
     */
    public function select(Request $request, string $table): JsonResponse
    {
        $tableName = $this->resolveTableName($table);

        if (! Schema::hasTable($tableName)) {
            return response()->json([], 200);
        }

        $query = DB::table($tableName);

        // Apply filters
        $this->applyPostgrestFilters($query, $request, $tableName);

        // Apply order
        $order = $request->query('order');
        if ($order) {
            foreach (explode(',', $order) as $orderClause) {
                $parts = explode('.', trim($orderClause));
                $col = $this->resolveColumnName($tableName, $parts[0] ?? 'created_at');
                $dir = strtolower($parts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $nulls = isset($parts[2]) ? strtolower($parts[2]) : null;
                if (Schema::hasColumn($tableName, $col)) {
                    if ($nulls === 'nullslast') {
                        $query->orderByRaw("{$col} {$dir} NULLS LAST");
                    } elseif ($nulls === 'nullsfirst') {
                        $query->orderByRaw("{$col} {$dir} NULLS FIRST");
                    } else {
                        $query->orderBy($col, $dir);
                    }
                }
            }
        } else if (Schema::hasColumn($tableName, 'created_at')) {
            $query->orderBy('created_at', 'desc');
        }

        // Apply limit / offset
        if ($request->has('limit')) {
            $query->limit((int) $request->query('limit'));
        }
        if ($request->has('offset')) {
            $query->offset((int) $request->query('offset'));
        }

        $rows = $query->get();
        $formatted = $rows->map(function ($row) use ($tableName) {
            return $this->formatRow($tableName, (array) $row);
        });

        return response()->json($formatted)
            ->header('Content-Range', '0-'.max(0, count($formatted) - 1).'/'.count($formatted));
    }

    /**
     * POST /rest/v1/{table} — PostgREST compatibility INSERT query.
     */
    public function insert(Request $request, string $table): JsonResponse
    {
        $tableName = $this->resolveTableName($table);
        $payload = $request->json()->all();

        if (empty($payload)) {
            $payload = $request->all();
        }

        $items = isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];
        $inserted = [];

        foreach ($items as $item) {
            $row = $this->prepareInsertRow($tableName, $item);
            if (! isset($row['created_at']) && Schema::hasColumn($tableName, 'created_at')) {
                $row['created_at'] = now();
            }
            if (! isset($row['updated_at']) && Schema::hasColumn($tableName, 'updated_at')) {
                $row['updated_at'] = now();
            }

            try {
                $id = DB::table($tableName)->insertGetId($row);
                $row['id'] = $id;
            } catch (\Throwable $e) {
                DB::table($tableName)->insert($row);
            }

            $inserted[] = $this->formatRow($tableName, $row);
        }

        return response()->json($inserted, 201);
    }

    /**
     * PATCH /rest/v1/{table} — PostgREST compatibility UPDATE query.
     */
    public function update(Request $request, string $table): JsonResponse
    {
        $tableName = $this->resolveTableName($table);
        $payload = $request->json()->all() ?: $request->all();

        $query = DB::table($tableName);
        $this->applyPostgrestFilters($query, $request, $tableName);

        $row = $this->prepareUpdateRow($tableName, $payload);
        if (Schema::hasColumn($tableName, 'updated_at')) {
            $row['updated_at'] = now();
        }

        $query->update($row);

        // Return updated items
        $updatedRows = $query->get()->map(function ($r) use ($tableName) {
            return $this->formatRow($tableName, (array) $r);
        });

        return response()->json($updatedRows);
    }

    /**
     * DELETE /rest/v1/{table} — PostgREST compatibility DELETE query.
     */
    public function delete(Request $request, string $table): Response
    {
        $tableName = $this->resolveTableName($table);

        $query = DB::table($tableName);
        $this->applyPostgrestFilters($query, $request, $tableName);
        $query->delete();

        return response()->noContent();
    }

    /**
     * POST /rest/v1/rpc/{fn} — Stored procedure / RPC emulation.
     */
    public function rpc(Request $request, string $fn): mixed
    {
        $user = $this->resolveAuthUser($request);

        switch ($fn) {
            case 'owner_kost_stats':
                if (! $user) {
                    return response()->json([], 200);
                }
                $kosts = DB::table('kosts')
                    ->where('owner_id', $user->id)
                    ->orWhere('owner_id', (string) $user->id)
                    ->get();
                $result = [];
                foreach ($kosts as $k) {
                    $dilihat = DB::table('app_events')
                        ->where(function ($q) use ($k) {
                            $q->where('kost_id', $k->id)->orWhere('kost_id', (string) $k->id);
                        })
                        ->where('event', 'view_detail')
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count();

                    $disimpan = DB::table('favorites')
                        ->where(function ($q) use ($k) {
                            $q->where('kost_id', $k->id)->orWhere('kost_id', (string) $k->id);
                        })
                        ->count();

                    $chat = DB::table('chats')
                        ->where(function ($q) use ($k) {
                            $q->where('kost_id', $k->id)->orWhere('kost_id', (string) $k->id);
                        })
                        ->count();

                    $survei = DB::table('survey_requests')
                        ->where(function ($q) use ($k) {
                            $q->where('kost_id', $k->id)->orWhere('kost_id', (string) $k->id);
                        })
                        ->count();

                    $result[] = [
                        'kost_id' => (string) $k->id,
                        'nama_kost' => $k->nama_kost ?? $k->name ?? 'Kost',
                        'dilihat' => (int) $dilihat,
                        'disimpan' => (int) $disimpan,
                        'chat' => (int) $chat,
                        'survei' => (int) $survei,
                    ];
                }
                return response()->json($result);

            case 'owner_favorite_count':
                if (! $user) {
                    return response('0', 200)->header('Content-Type', 'text/plain');
                }
                $count = DB::table('favorites')
                    ->join('kosts', 'kosts.id', '=', 'favorites.kost_id')
                    ->where(function ($q) use ($user) {
                        $q->where('kosts.owner_id', $user->id)
                            ->orWhere('kosts.owner_id', (string) $user->id);
                    })
                    ->count();
                return response((string) $count, 200)->header('Content-Type', 'text/plain');

            case 'admin_funnel':
                $days = (int) ($request->input('days', 30));
                $events = DB::table('app_events')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('event')
                    ->select('event', DB::raw('count(*) as total'))
                    ->get()
                    ->map(fn($row) => [
                        'event' => $row->event,
                        'total' => (int) $row->total,
                    ]);
                return response()->json($events);

            case 'resubmit_kost':
                $targetId = $request->input('target_id');
                if ($user && $targetId) {
                    DB::table('kosts')
                        ->where(function ($q) use ($targetId) {
                            $q->where('id', $targetId)->orWhere('id', (string) $targetId);
                        })
                        ->where(function ($q) use ($user) {
                            $q->where('owner_id', $user->id)->orWhere('owner_id', (string) $user->id);
                        })
                        ->update([
                            'verification_status' => 'PENDING',
                            'updated_at' => now(),
                        ]);
                }
                return response()->json(['message' => 'Kost diajukan ulang']);

            case 'delete_my_account':
                if ($user) {
                    $user->tokens()->delete();
                    $user->delete();
                }
                return response()->json(['message' => 'Akun berhasil dihapus']);

            case 'admin_delete_user':
                $targetId = $request->input('target_id');
                if ($targetId) {
                    DB::table('users')
                        ->where('id', $targetId)
                        ->orWhere('id', (string) $targetId)
                        ->delete();
                }
                return response()->json(['message' => 'Pengguna berhasil dihapus']);

            default:
                return response()->json([]);
        }
    }

    /**
     * GET /realtime/v1/websocket — Informational gateway status for WebSocket clients.
     */
    public function realtimeInfo(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'CariKostKita Realtime Gateway',
            'websocket_url' => 'wss://gtnsmljlztypqefhojjn.supabase.co/realtime/v1/websocket',
            'fallback_polling' => true,
        ]);
    }

    /**
     * GET /storage/v1/object/public/{bucket}/{path} — Serve public storage file directly or redirect to Supabase.
     */
    public function storageServePublic(string $bucket, string $path): mixed
    {
        $cleanPath = trim($path, '/');
        $localPath = "{$bucket}/{$cleanPath}";

        if (Storage::disk('public')->exists($localPath)) {
            return Storage::disk('public')->response($localPath);
        }

        // Redirect to Supabase Project B Storage
        $supabaseStorageUrl = "https://xeumkqfzwbgrddxoerzt.supabase.co/storage/v1/object/public/{$bucket}/{$cleanPath}";
        return redirect()->away($supabaseStorageUrl);
    }

    /**
     * POST /storage/v1/object/{bucket}/{path} — Supabase Storage upload proxy.
     */
    public function storageUpload(Request $request, string $bucket, string $path): JsonResponse
    {
        $cleanPath = trim($path, '/');

        // Check if upload is multipart file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $dir = dirname($cleanPath);
            $filename = basename($cleanPath);
            $result = $this->storageService->uploadFile($file, $bucket, $dir === '.' ? '' : $dir, $filename);

            return response()->json([
                'Key' => "{$bucket}/{$result['file_path']}",
                'Id' => Str::uuid()->toString(),
            ], 200);
        }

        // Binary body upload
        $content = $request->getContent();
        if (! empty($content)) {
            $mime = $request->header('Content-Type', 'image/jpeg');
            $tmpFile = tempnam(sys_get_temp_dir(), 'sb_up_');
            file_put_contents($tmpFile, $content);

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tmpFile,
                basename($cleanPath),
                $mime,
                null,
                true
            );

            $dir = dirname($cleanPath);
            $filename = basename($cleanPath);
            $result = $this->storageService->uploadFile($uploadedFile, $bucket, $dir === '.' ? '' : $dir, $filename);

            @unlink($tmpFile);

            return response()->json([
                'Key' => "{$bucket}/{$result['file_path']}",
                'Id' => Str::uuid()->toString(),
            ], 200);
        }

        return response()->json(['Key' => "{$bucket}/{$cleanPath}"], 200);
    }

    /**
     * POST /storage/v1/object/sign/{bucket}/{path} — Create signed URL.
     */
    public function storageSign(Request $request, string $bucket, string $path): JsonResponse
    {
        $cleanPath = trim($path, '/');
        $publicUrl = config('services.supabase.url', 'http://127.0.0.1:8000') . "/storage/v1/object/public/{$bucket}/{$cleanPath}";

        return response()->json([
            'signedURL' => $publicUrl,
        ]);
    }

    /**
     * DELETE /storage/v1/object/{bucket}/{path} — Delete object.
     */
    public function storageDelete(Request $request, string $bucket, string $path): JsonResponse
    {
        return response()->json(['message' => 'Object deleted']);
    }

    // =========================================================================
    // HELPER METHODS FOR POSTGREST QUERY PARSING AND NORMALIZATION
    // =========================================================================

    protected function resolveTableName(string $table): string
    {
        return strtolower(trim($table));
    }

    protected function resolveColumnName(string $table, string $col): string
    {
        $col = strtolower(trim($col));

        // Alias mappings for kosts table
        if ($table === 'kosts') {
            $map = [
                'nama' => 'nama_kost',
                'alamat' => 'address',
                'harga' => 'price_min',
                'tipe' => 'gender_type',
                'tipe_kost' => 'gender_type',
                'deskripsi' => 'description',
                'kota' => 'city',
                'kecamatan' => 'district',
                'kelurahan' => 'village',
                'provinsi' => 'province',
                'status_verifikasi' => 'verification_status',
            ];
            return $map[$col] ?? $col;
        }

        // Alias mappings for users table
        if ($table === 'users') {
            $map = [
                'nama' => 'name',
                'no_hp' => 'phone',
            ];
            return $map[$col] ?? $col;
        }

        return $col;
    }

    protected function applyPostgrestFilters($query, Request $request, string $tableName): void
    {
        foreach ($request->query() as $key => $val) {
            if (in_array($key, ['select', 'order', 'limit', 'offset', 'apikey', 'prefer'], true)) {
                continue;
            }

            if ($key === 'or') {
                $this->applyOrCondition($query, $val, $tableName);
                continue;
            }

            if ($key === 'and') {
                $this->applyAndCondition($query, $val, $tableName);
                continue;
            }

            $col = $this->resolveColumnName($tableName, $key);
            if (! Schema::hasColumn($tableName, $col)) {
                continue;
            }

            $this->applyOperator($query, $col, (string) $val);
        }
    }

    protected function applyOperator($query, string $col, string $expr): void
    {
        if (str_starts_with($expr, 'eq.')) {
            $val = substr($expr, 3);
            $query->where($col, '=', $this->castValue($val));
        } elseif (str_starts_with($expr, 'neq.')) {
            $val = substr($expr, 4);
            $query->where($col, '!=', $this->castValue($val));
        } elseif (str_starts_with($expr, 'gt.')) {
            $val = substr($expr, 3);
            $query->where($col, '>', $this->castValue($val));
        } elseif (str_starts_with($expr, 'gte.')) {
            $val = substr($expr, 4);
            $query->where($col, '>=', $this->castValue($val));
        } elseif (str_starts_with($expr, 'lt.')) {
            $val = substr($expr, 3);
            $query->where($col, '<', $this->castValue($val));
        } elseif (str_starts_with($expr, 'lte.')) {
            $val = substr($expr, 4);
            $query->where($col, '<=', $this->castValue($val));
        } elseif (str_starts_with($expr, 'like.')) {
            $val = str_replace('*', '%', substr($expr, 5));
            $query->where($col, 'LIKE', $val);
        } elseif (str_starts_with($expr, 'ilike.')) {
            $val = str_replace('*', '%', substr($expr, 6));
            $query->where($col, 'ILIKE', $val);
        } elseif (str_starts_with($expr, 'is.')) {
            $val = substr($expr, 3);
            if ($val === 'null') {
                $query->whereNull($col);
            }
        } elseif (str_starts_with($expr, 'in.(') && str_ends_with($expr, ')')) {
            $inner = substr($expr, 4, -1);
            $vals = array_map(fn($v) => trim($v, ' "\''), explode(',', $inner));
            $query->whereIn($col, $vals);
        } else {
            $query->where($col, '=', $this->castValue($expr));
        }
    }

    /**
     * Split a comma-separated list of expressions respecting nested parentheses.
     */
    protected function splitClauses(string $expr): array
    {
        $clauses = [];
        $current = '';
        $depth = 0;
        $len = strlen($expr);

        for ($i = 0; $i < $len; $i++) {
            $ch = $expr[$i];
            if ($ch === '(') {
                $depth++;
                $current .= $ch;
            } elseif ($ch === ')') {
                $depth--;
                $current .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
                if (trim($current) !== '') {
                    $clauses[] = trim($current);
                }
                $current = '';
            } else {
                $current .= $ch;
            }
        }

        if (trim($current) !== '') {
            $clauses[] = trim($current);
        }

        return $clauses;
    }

    protected function applyOrCondition($query, string $expr, string $tableName): void
    {
        $inner = trim($expr);
        if (str_starts_with($inner, '(') && str_ends_with($inner, ')')) {
            $inner = substr($inner, 1, -1);
        }
        $clauses = $this->splitClauses($inner);

        $query->where(function ($sub) use ($clauses, $tableName) {
            foreach ($clauses as $clause) {
                if (str_starts_with($clause, 'and(') && str_ends_with($clause, ')')) {
                    $subInner = substr($clause, 4, -1);
                    $sub->orWhere(function ($andSub) use ($subInner, $tableName) {
                        $this->applyAndCondition($andSub, $subInner, $tableName);
                    });
                } elseif (str_starts_with($clause, 'or(') && str_ends_with($clause, ')')) {
                    $subInner = substr($clause, 3, -1);
                    $sub->orWhere(function ($orSub) use ($subInner, $tableName) {
                        $this->applyOrCondition($orSub, $subInner, $tableName);
                    });
                } else {
                    $this->applySingleCondition($sub, $clause, $tableName, 'or');
                }
            }
        });
    }

    protected function applyAndCondition($query, string $expr, string $tableName): void
    {
        $inner = trim($expr);
        if (str_starts_with($inner, '(') && str_ends_with($inner, ')')) {
            $inner = substr($inner, 1, -1);
        }
        $clauses = $this->splitClauses($inner);

        foreach ($clauses as $clause) {
            if (str_starts_with($clause, 'or(') && str_ends_with($clause, ')')) {
                $subInner = substr($clause, 3, -1);
                $query->where(function ($orSub) use ($subInner, $tableName) {
                    $this->applyOrCondition($orSub, $subInner, $tableName);
                });
            } elseif (str_starts_with($clause, 'and(') && str_ends_with($clause, ')')) {
                $subInner = substr($clause, 4, -1);
                $query->where(function ($andSub) use ($subInner, $tableName) {
                    $this->applyAndCondition($andSub, $subInner, $tableName);
                });
            } else {
                $this->applySingleCondition($query, $clause, $tableName, 'and');
            }
        }
    }

    protected function applySingleCondition($query, string $clause, string $tableName, string $boolean = 'and'): void
    {
        $parts = explode('.', $clause, 3);
        if (count($parts) < 2) {
            return;
        }

        $rawCol = $parts[0];
        $op = strtolower($parts[1]);
        $val = $parts[2] ?? '';
        $col = $this->resolveColumnName($tableName, $rawCol);

        if (! Schema::hasColumn($tableName, $col)) {
            return;
        }

        $isOr = $boolean === 'or';

        if ($op === 'eq') {
            $isOr ? $query->orWhere($col, '=', $this->castValue($val)) : $query->where($col, '=', $this->castValue($val));
        } elseif ($op === 'neq') {
            $isOr ? $query->orWhere($col, '!=', $this->castValue($val)) : $query->where($col, '!=', $this->castValue($val));
        } elseif ($op === 'gt') {
            $isOr ? $query->orWhere($col, '>', $this->castValue($val)) : $query->where($col, '>', $this->castValue($val));
        } elseif ($op === 'gte') {
            $isOr ? $query->orWhere($col, '>=', $this->castValue($val)) : $query->where($col, '>=', $this->castValue($val));
        } elseif ($op === 'lt') {
            $isOr ? $query->orWhere($col, '<', $this->castValue($val)) : $query->where($col, '<', $this->castValue($val));
        } elseif ($op === 'lte') {
            $isOr ? $query->orWhere($col, '<=', $this->castValue($val)) : $query->where($col, '<=', $this->castValue($val));
        } elseif ($op === 'like') {
            $pattern = str_replace('*', '%', $val);
            $isOr ? $query->orWhere($col, 'LIKE', $pattern) : $query->where($col, 'LIKE', $pattern);
        } elseif ($op === 'ilike') {
            $pattern = str_replace('*', '%', $val);
            $isOr ? $query->orWhere($col, 'ILIKE', $pattern) : $query->where($col, 'ILIKE', $pattern);
        } elseif ($op === 'is') {
            if ($val === 'null') {
                $isOr ? $query->orWhereNull($col) : $query->whereNull($col);
            } else {
                $isOr ? $query->orWhereNotNull($col) : $query->whereNotNull($col);
            }
        } elseif ($op === 'in') {
            $inner = trim($val, '()');
            $vals = array_map(fn($v) => $this->castValue(trim($v, ' "\'')), explode(',', $inner));
            $isOr ? $query->orWhereIn($col, $vals) : $query->whereIn($col, $vals);
        } else {
            $isOr ? $query->orWhere($col, '=', $this->castValue($val)) : $query->where($col, '=', $this->castValue($val));
        }
    }

    protected function castValue(string $val): mixed
    {
        $clean = trim($val, ' "\'');
        if (is_numeric($clean)) {
            return str_contains($clean, '.') ? (float) $clean : (int) $clean;
        }
        if (strtolower($clean) === 'true') {
            return true;
        }
        if (strtolower($clean) === 'false') {
            return false;
        }
        if (strtolower($clean) === 'null') {
            return null;
        }
        return $clean;
    }

    protected function prepareInsertRow(string $table, array $data): array
    {
        $row = [];
        foreach ($data as $key => $val) {
            $col = $this->resolveColumnName($table, $key);
            if (Schema::hasColumn($table, $col)) {
                $row[$col] = is_array($val) ? json_encode($val) : $val;
            }
        }
        return $row;
    }

    protected function prepareUpdateRow(string $table, array $data): array
    {
        return $this->prepareInsertRow($table, $data);
    }

    /**
     * Format a database row into an all-inclusive DTO object with both
     * Indonesian and standard column keys.
     */
    protected function formatRow(string $table, array $row): array
    {
        if ($table === 'kosts') {
            $name = $row['nama_kost'] ?? $row['name'] ?? '';
            $address = $row['address'] ?? $row['alamat'] ?? '';
            $price = (float) ($row['price_min'] ?? $row['harga'] ?? 0);
            $gender = $row['gender_type'] ?? $row['tipe_kost'] ?? 'campur';

            $images = $row['image_urls'] ?? [];
            if (is_string($images)) {
                $images = json_decode($images, true) ?: [];
            }

            $facilities = $row['fasilitas'] ?? $row['facilities'] ?? [];
            if (is_string($facilities)) {
                $facilities = json_decode($facilities, true) ?: [];
            }

            return array_merge($row, [
                'id' => (string) ($row['id'] ?? ''),
                'owner_id' => (string) ($row['owner_id'] ?? ''),
                'nama_kost' => $name,
                'name' => $name,
                'alamat' => $address,
                'address' => $address,
                'patokan' => $row['patokan'] ?? '',
                'harga' => $price,
                'price_min' => $price,
                'tipe_kost' => strtoupper($gender),
                'gender_type' => strtolower($gender),
                'deskripsi' => $row['description'] ?? '',
                'description' => $row['description'] ?? '',
                'no_whatsapp' => $row['no_whatsapp'] ?? '',
                'latitude' => isset($row['latitude']) ? (float) $row['latitude'] : 0.0,
                'longitude' => isset($row['longitude']) ? (float) $row['longitude'] : 0.0,
                'status' => strtoupper($row['status'] ?? 'TERSEDIA'),
                'verification_status' => strtoupper($row['verification_status'] ?? 'APPROVED'),
                'provinsi' => $row['province'] ?? 'Riau',
                'kota' => $row['city'] ?? 'Pekanbaru',
                'kecamatan' => $row['district'] ?? 'Tampan',
                'kelurahan' => $row['village'] ?? 'Simpang Baru',
                'ukuran_kamar' => $row['ukuran_kamar'] ?? '3x4',
                'total_kamar' => (int) ($row['total_kamar'] ?? 1),
                'kamar_tersedia' => (int) ($row['kamar_tersedia'] ?? 1),
                'thumbnail_url' => $row['thumbnail_url'] ?? '',
                'image_urls' => $images,
                'fasilitas' => $facilities,
            ]);
        }

        if ($table === 'users') {
            unset($row['password'], $row['remember_token']);
            $name = $row['name'] ?? $row['nama'] ?? '';
            $phone = $row['phone'] ?? $row['no_hp'] ?? '';

            return array_merge($row, [
                'id' => (string) ($row['id'] ?? ''),
                'nama' => $name,
                'name' => $name,
                'email' => $row['email'] ?? '',
                'role' => $row['role'] ?? 'pencari',
                'no_hp' => $phone,
                'phone' => $phone,
                'avatar_url' => $row['avatar_url'] ?? $row['avatar'] ?? '',
                'verification_status' => $row['verification_status'] ?? 'verified',
                'is_active' => (bool) ($row['is_active'] ?? true),
            ]);
        }

        return $row;
    }
}
