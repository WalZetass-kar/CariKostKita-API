<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_rooms_of_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Room Test',
            'address' => 'Jl. Kamar',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
            'verification_status' => 'approved',
        ]);

        $kost->rooms()->create([
            'name' => 'Kamar Tipe A',
            'price' => 700000,
            'total_rooms' => 5,
            'available_rooms' => 3,
        ]);

        $response = $this->getJson("/api/kosts/{$kost->id}/rooms");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment(['name' => 'Kamar Tipe A']);
    }

    public function test_pemilik_can_add_room_to_own_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Sendiri',
            'address' => 'Jl. Sendiri',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
            'verification_status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/owner/kosts/{$kost->id}/rooms", [
                'name' => 'Kamar Deluxe',
                'room_type' => 'Deluxe',
                'price' => 950000,
                'size' => '3x4',
                'total_rooms' => 10,
                'available_rooms' => 8,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Kamar Deluxe',
                    'price' => 950000,
                ],
            ]);

        $this->assertDatabaseHas('rooms', [
            'kost_id' => $kost->id,
            'name' => 'Kamar Deluxe',
        ]);
    }

    public function test_pemilik_cannot_add_room_to_other_owner_kost(): void
    {
        $ownerA = User::factory()->create(['role' => 'pemilik']);
        $ownerB = User::factory()->create(['role' => 'pemilik']);
        $tokenB = $ownerB->createToken('test_token')->plainTextToken;

        $kostA = Kost::create([
            'owner_id' => $ownerA->id,
            'name' => 'Kost Owner A',
            'address' => 'Jl. A',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->postJson("/api/owner/kosts/{$kostA->id}/rooms", [
                'name' => 'Kamar Ilegal',
                'price' => 500000,
                'total_rooms' => 5,
                'available_rooms' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_pemilik_can_update_own_room(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Owner',
            'address' => 'Jl. Owner',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $room = $kost->rooms()->create([
            'name' => 'Kamar Lama',
            'price' => 500000,
            'total_rooms' => 2,
            'available_rooms' => 2,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/owner/rooms/{$room->id}", [
                'name' => 'Kamar Baru Diperbarui',
                'price' => 600000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Kamar Baru Diperbarui',
                    'price' => 600000,
                ],
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Kamar Baru Diperbarui',
        ]);
    }

    public function test_pemilik_can_delete_own_room(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Owner Room Delete',
            'address' => 'Jl. Owner Room',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $room = $kost->rooms()->create([
            'name' => 'Kamar Akan Dihapus',
            'price' => 500000,
            'total_rooms' => 1,
            'available_rooms' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/owner/rooms/{$room->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }
}
