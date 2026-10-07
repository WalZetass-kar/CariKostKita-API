<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'pencari',
                'phone' => $validated['phone'] ?? null,
            ]);

            $user->profile()->create();

            return $user;
        });

        $user->load('profile');
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Registrasi berhasil', 201);
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Kredensial yang diberikan tidak cocok.', 422, [
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        $user->load('profile');
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login berhasil');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse(null, 'Logout berhasil');
    }
}
