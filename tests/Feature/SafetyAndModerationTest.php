<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafetyAndModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_block_another_user(): void
    {
        $user1 = User::factory()->create(['role' => 'pencari']);
        $user2 = User::factory()->create(['role' => 'pencari']);

        $token = $user1->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/safety/block', [
                'blocked_id' => $user2->id,
                'reason' => 'Perilaku tidak menyenangkan di chat',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'blocked_id' => $user2->id,
                ],
            ]);

        $this->assertDatabaseHas('user_blocks', [
            'blocker_id' => $user1->id,
            'blocked_id' => $user2->id,
        ]);
    }

    public function test_user_cannot_block_self(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/safety/block', [
                'blocked_id' => $user->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_report_another_user(): void
    {
        $user1 = User::factory()->create(['role' => 'pencari']);
        $user2 = User::factory()->create(['role' => 'pencari']);

        $token = $user1->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/safety/report-user', [
                'reported_user_id' => $user2->id,
                'reason' => 'Spam / Penipuan',
                'details' => 'Meminta transfer di luar aplikasi.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'Baru',
                ],
            ]);

        $this->assertDatabaseHas('user_reports', [
            'reporter_id' => $user1->id,
            'reported_user_id' => $user2->id,
            'status' => 'Baru',
        ]);
    }

    public function test_user_can_report_invalid_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $pencari = User::factory()->create(['role' => 'pencari']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Palsu',
            'address' => 'Jl. Palsu',
            'city' => 'Pekanbaru',
            'district' => 'Rumbai',
            'price_min' => 100000,
            'price_max' => 200000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $pencari->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/reports/kost', [
                'kost_id' => $kost->id,
                'reason' => 'Harga tidak sesuai dengan kenyataan di lokasi',
                'description' => 'Saat survei diminta bayar 2x lipat.',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', [
            'kost_id' => $kost->id,
            'reporter_id' => $pencari->id,
        ]);
    }
}
