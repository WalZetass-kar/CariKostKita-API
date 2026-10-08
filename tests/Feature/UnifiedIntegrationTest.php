<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_aliases_work_seamlessly_for_mobile_and_admin(): void
    {
        // Mobile uses 'owner' and 'developer' strings
        $mobileOwner = User::factory()->create(['role' => 'owner']);
        $mobileDev = User::factory()->create(['role' => 'developer']);

        $tokenOwner = $mobileOwner->createToken('token')->plainTextToken;
        $tokenDev = $mobileDev->createToken('token')->plainTextToken;

        // Mobile owner can access /api/v1/owner/kosts
        $responseOwner = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->getJson('/api/v1/owner/kosts');
        $responseOwner->assertStatus(200);

        auth()->forgetGuards();

        // Mobile developer can access /api/v1/admin/dashboard
        $responseDev = $this->withHeader('Authorization', 'Bearer '.$tokenDev)
            ->getJson('/api/v1/admin/dashboard');
        $responseDev->assertStatus(200);
    }

    public function test_flexible_field_aliases_when_creating_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('token')->plainTextToken;

        // Using mobile flat field naming: nama_kost, harga, alamat, kecamatan, kota, tipeKost
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/owner/kosts', [
                'nama_kost' => 'Kost Mahasiswa Cendana',
                'harga' => 850000,
                'alamat' => 'Jl. Cendana No. 20',
                'kecamatan' => 'Sukajadi',
                'kota' => 'Pekanbaru',
                'tipeKost' => 'PUTRA',
                'fasilitas' => ['WiFi', 'Kamar Mandi Dalam', 'AC'],
                'ukuran_kamar' => '3x4 m',
                'total_kamar' => 10,
                'kamar_tersedia' => 4,
                'deposit' => 200000,
                'no_whatsapp' => '081234567890',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'namaKost' => 'Kost Mahasiswa Cendana',
                    'price' => 850000,
                    'price_min' => 850000,
                    'price_max' => 850000,
                    'area' => 'Sukajadi',
                    'tipeKost' => 'PUTRA',
                    'kamarTersedia' => 4,
                    'totalKamar' => 10,
                    'deposit' => 200000,
                ],
            ]);

        $kostId = $response->json('data.id');

        // Test owner resubmit endpoint
        auth()->forgetGuards();
        $kost = Kost::find($kostId);
        $kost->update(['verification_status' => 'rejected', 'catatan_revisi' => 'Perbaiki foto']);

        $resubmitResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/owner/kosts/{$kostId}/resubmit");

        $resubmitResponse->assertStatus(200);
        $this->assertEquals('pending', $kost->fresh()->verification_status);
        $this->assertNull($kost->fresh()->catatan_revisi);
    }

    public function test_both_unversioned_and_v1_endpoints_work_consistently(): void
    {
        $kost = Kost::create([
            'owner_id' => User::factory()->create(['role' => 'pemilik'])->id,
            'name' => 'Kost Publik',
            'address' => 'Jl. Publik',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 500000,
            'price_max' => 900000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        // Root /api/kosts
        $res1 = $this->getJson('/api/kosts');
        $res1->assertStatus(200)->assertJsonFragment(['name' => 'Kost Publik']);

        // Versioned /api/v1/kosts
        $res2 = $this->getJson('/api/v1/kosts');
        $res2->assertStatus(200)->assertJsonFragment(['name' => 'Kost Publik']);
    }

    public function test_interactive_map_search_endpoint(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Dekat Sukajadi',
            'address' => 'Jl. KH Ahmad Dahlan',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'latitude' => 0.512345,
            'longitude' => 101.442345,
            'price_min' => 700000,
            'price_max' => 1200000,
            'gender_type' => 'putri',
            'verification_status' => 'approved',
        ]);

        $response = $this->getJson('/api/v1/kosts/map?latitude=0.512&longitude=101.442&radius=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'total' => 1,
                ],
            ])
            ->assertJsonFragment(['name' => 'Kost Dekat Sukajadi']);
    }

    public function test_user_self_account_deletion_for_google_play(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/user/account');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
