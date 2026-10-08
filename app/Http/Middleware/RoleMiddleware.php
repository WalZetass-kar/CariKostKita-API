<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Map client role synonyms to canonical role groups.
     *
     * @var array<string, list<string>>
     */
    protected static array $roleAliases = [
        'admin' => ['admin', 'developer'],
        'pemilik' => ['pemilik', 'owner', 'pemilik_kost'],
        'pencari' => ['pencari', 'user'],
        'moderator' => ['moderator', 'admin', 'developer'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan oleh administrator.',
            ], 403);
        }

        // Expand requested roles with their accepted aliases
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles[] = $role;
            if (isset(static::$roleAliases[$role])) {
                $allowedRoles = array_merge($allowedRoles, static::$roleAliases[$role]);
            }
        }
        $allowedRoles = array_unique($allowedRoles);

        if (! in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role tidak memiliki izin untuk tindakan ini.',
            ], 403);
        }

        return $next($request);
    }
}
