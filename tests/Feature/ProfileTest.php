<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'pencari',
        ]);
        $user->profile()->create([
            'bio' => 'Looking for a quiet room',
            'city' => 'Bandung',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'profile' => [
                        'bio' => 'Looking for a quiet room',
                        'city' => 'Bandung',
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => '08000000',
        ]);
        $user->profile()->create([
            'bio' => 'Old Bio',
            'city' => 'Old City',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user/profile', [
                'name' => 'New Name',
                'phone' => '0812334455',
                'bio' => 'Updated bio description',
                'city' => 'Pekanbaru',
                'district' => 'Sukajadi',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'name' => 'New Name',
                    'phone' => '0812334455',
                    'profile' => [
                        'bio' => 'Updated bio description',
                        'city' => 'Pekanbaru',
                        'district' => 'Sukajadi',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone' => '0812334455',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'bio' => 'Updated bio description',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
        ]);
    }
}
