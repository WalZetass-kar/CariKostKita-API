<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create(['role' => 'pencari']);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'current_page'],
            ]);
    }

    public function test_non_admin_cannot_access_admin_users(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $token = $pencari->createToken('user_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_pending_kosts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Menunggu Approval',
            'address' => 'Jl. Pending',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'pending',
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/kosts/pending');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Kost Menunggu Approval']);
    }

    public function test_admin_can_approve_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Siap Approve',
            'address' => 'Jl. Approve',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'pending',
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/kosts/{$kost->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'verification_status' => 'approved',
                ],
            ]);

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'verification_status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Akan Ditolak',
            'address' => 'Jl. Tolak',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'pending',
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/kosts/{$kost->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'verification_status' => 'rejected',
                ],
            ]);

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'verification_status' => 'rejected',
        ]);
    }

    public function test_admin_can_delete_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Admin Hapus',
            'address' => 'Jl. Admin Hapus',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/admin/kosts/{$kost->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('kosts', ['id' => $kost->id]);
    }

    public function test_admin_can_update_report_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Dilaporkan',
            'address' => 'Jl. Laporkan',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
        ]);

        $report = Report::create([
            'reporter_id' => $user->id,
            'kost_id' => $kost->id,
            'reason' => 'Data palsu',
            'status' => 'pending',
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/reports/{$report->id}/status", [
                'status' => 'resolved',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'resolved',
                ],
            ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }
}
