<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return $this->successResponse(new UserResource($user), 'Data pengguna berhasil diambil');
    }

    /**
     * Update authenticated user profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $user->update(array_filter([
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'] ?? $validated['no_hp'] ?? null,
                'no_hp' => $validated['no_hp'] ?? $validated['phone'] ?? null,
                'avatar' => $validated['avatar'] ?? $validated['avatar_url'] ?? null,
                'avatar_url' => $validated['avatar_url'] ?? $validated['avatar'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ], fn ($v) => $v !== null));

            $profileData = array_filter([
                'bio' => $validated['bio'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'district' => $validated['district'] ?? null,
            ], fn ($v) => $v !== null);

            if (! empty($profileData)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }
        });

        $user->load('profile');

        return $this->successResponse(new UserResource($user), 'Profil berhasil diperbarui');
    }

    /**
     * Update authenticated user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', Password::defaults()],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->errorResponse('Password saat ini tidak cocok.', 422, [
                'current_password' => ['Password saat ini tidak valid.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return $this->successResponse(null, 'Password berhasil diperbarui');
    }

    /**
     * Self-delete user account (required for Google Play store policy).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft / Hard delete user records
        $user->delete();

        return $this->successResponse(null, 'Akun berhasil dihapus secara permanen.');
    }

    /**
     * Submit verification documents for Kost Owner (KTP, selfie, ownership docs).
     */
    public function submitVerification(Request $request, SupabaseStorageService $storageService): JsonResponse
    {
        $request->validate([
            'ktp' => ['nullable', 'image', 'max:5120'],
            'selfie' => ['nullable', 'image', 'max:5120'],
            'kepemilikan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $user = $request->user();
        $updates = [
            'verification_status' => 'pending',
            'pengajuan_at' => now(),
            'catatan_revisi' => null,
        ];

        if ($request->hasFile('ktp')) {
            $ktpResult = $storageService->uploadVerificationDoc($request->file('ktp'), $user->id, 'ktp');
            $updates['ktp_path'] = $ktpResult['url'];
        }

        if ($request->hasFile('selfie')) {
            $selfieResult = $storageService->uploadVerificationDoc($request->file('selfie'), $user->id, 'selfie');
            $updates['selfie_path'] = $selfieResult['url'];
        }

        if ($request->hasFile('kepemilikan')) {
            $docResult = $storageService->uploadVerificationDoc($request->file('kepemilikan'), $user->id, 'kepemilikan');
            $updates['kepemilikan_path'] = $docResult['url'];
        }

        $user->update($updates);

        return $this->successResponse(new UserResource($user), 'Dokumen verifikasi berhasil dikirim dan sedang ditinjau');
    }
}
