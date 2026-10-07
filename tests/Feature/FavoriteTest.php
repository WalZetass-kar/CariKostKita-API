<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_kost_to_favorites(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Favorit',
            'address' => 'Jl. Favorit',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/favorites/{$kost->id}");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Kost berhasil ditambahkan ke favorit',
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'kost_id' => $kost->id,
        ]);
    }

    public function test_user_can_list_favorites(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Favorit List',
            'address' => 'Jl. List',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $user->favorites()->create(['kost_id' => $kost->id]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment(['name' => 'Kost Favorit List']);
    }

    public function test_user_can_remove_kost_from_favorites(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Hapus Favorit',
            'address' => 'Jl. Hapus Fav',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $user->favorites()->create(['kost_id' => $kost->id]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/favorites/{$kost->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kost berhasil dihapus dari favorit',
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'kost_id' => $kost->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_manage_favorites(): void
    {
        $response = $this->getJson('/api/favorites');
        $response->assertStatus(401);
    }
}
