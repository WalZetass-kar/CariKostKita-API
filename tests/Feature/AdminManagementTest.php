<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Kost;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $pencari = User::factory()->create(['role' => 'pencari']);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Teratai',
            'address' => 'Jl. Teratai',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 700000,
            'price_max' => 1200000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $admin->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'metrics' => [
                        'total_kost' => 1,
                        'kost_aktif' => 1,
                        'total_owners' => 1,
                        'total_users' => 1,
                    ],
                ],
            ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'pencari', 'is_active' => true]);

        $token = $admin->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/v1/admin/users/{$user->id}/status");

        $response->assertStatus(200);
        $this->assertFalse((bool) $user->fresh()->is_active);

        // Check audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'admin_id' => $admin->id,
            'action' => 'nonaktifkan_pengguna',
            'target_id' => $user->id,
        ]);
    }

    public function test_admin_can_update_kost_status_with_revision_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Pending',
            'address' => 'Jl. Pending',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 800000,
            'gender_type' => 'campur',
            'verification_status' => 'pending',
        ]);

        $token = $admin->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/v1/admin/kosts/{$kost->id}/status", [
                'status' => 'Ditolak',
                'catatan_revisi' => 'Foto kamar utama buram, mohon unggah ulang.',
            ]);

        $response->assertStatus(200);

        $fresh = $kost->fresh();
        $this->assertEquals('rejected', $fresh->verification_status);
        $this->assertEquals('Foto kamar utama buram, mohon unggah ulang.', $fresh->catatan_revisi);
    }

    public function test_admin_can_crud_advertisements(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('token')->plainTextToken;

        // Create Ad
        $createRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/admin/ads', [
                'title' => 'Iklan Spesial Admin',
                'type' => 'Banner',
                'image_url' => 'https://example.com/ad.jpg',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
            ]);

        $createRes->assertStatus(201);
        $adId = $createRes->json('data.id');

        // Toggle Ad Status
        $toggleRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/v1/admin/ads/{$adId}/status");

        $toggleRes->assertStatus(200);
        $this->assertFalse(Advertisement::find($adId)->is_active);

        // Delete Ad
        $deleteRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/admin/ads/{$adId}");

        $deleteRes->assertStatus(200);
        $this->assertDatabaseMissing('advertisements', ['id' => $adId]);
    }

    public function test_admin_can_update_platform_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/admin/settings', [
                'settings' => [
                    'contact_whatsapp' => '628999999999',
                    'default_city' => 'Pekanbaru',
                ],
            ]);

        $response->assertStatus(200);
        $this->assertEquals('628999999999', PlatformSetting::get('contact_whatsapp'));
    }
}
