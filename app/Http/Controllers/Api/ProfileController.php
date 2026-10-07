<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'phone' => $validated['phone'] ?? null,
                'avatar' => $validated['avatar'] ?? null,
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
}
