<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_browse_only_approved_kosts(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        $approvedKost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Mawar Indah',
            'address' => 'Jl. Mawar No 1',
            'city' => 'Pekanbaru',
            'district' => 'Bukit Raya',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putri',
            'verification_status' => 'approved',
        ]);

        $pendingKost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Melati Rahasia',
            'address' => 'Jl. Melati No 2',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 400000,
            'price_max' => 800000,
            'gender_type' => 'putra',
            'verification_status' => 'pending',
        ]);

        $response = $this->getJson('/api/kosts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment(['name' => 'Kost Mawar Indah'])
            ->assertJsonMissing(['name' => 'Kost Melati Rahasia']);
    }

    public function test_public_can_filter_kosts_by_city_gender_and_price(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Bandung Putra',
            'address' => 'Jl. Dago',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'price_min' => 1000000,
            'price_max' => 2000000,
            'gender_type' => 'putra',
            'verification_status' => 'approved',
        ]);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Jakarta Putri',
            'address' => 'Jl. Sudirman',
            'city' => 'Jakarta',
            'district' => 'Pusat',
            'price_min' => 3000000,
            'price_max' => 5000000,
            'gender_type' => 'putri',
            'verification_status' => 'approved',
        ]);

        $response = $this->getJson('/api/kosts?city=Bandung&gender=putra&max_price=2500000');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Kost Bandung Putra'])
            ->assertJsonMissing(['name' => 'Kost Jakarta Putri']);
    }

    public function test_public_can_view_approved_kost_detail(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Detail Test',
            'address' => 'Jl. Flamboyan',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 600000,
            'price_max' => 1200000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $response = $this->getJson("/api/kosts/{$kost->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $kost->id,
                    'name' => 'Kost Detail Test',
                ],
            ]);
    }

    public function test_public_cannot_view_unapproved_kost_detail(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Pending Test',
            'address' => 'Jl. Tersembunyi',
            'city' => 'Pekanbaru',
            'district' => 'Rumbai',
            'price_min' => 500000,
            'price_max' => 900000,
            'gender_type' => 'putra',
            'verification_status' => 'pending',
        ]);

        $response = $this->getJson("/api/kosts/{$kost->id}");

        $response->assertStatus(404);
    }

    public function test_pencari_cannot_create_kost(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $token = $pencari->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/owner/kosts', [
                'name' => 'Kost Ilegal Pencari',
                'address' => 'Jl. Palsu',
                'city' => 'Pekanbaru',
                'district' => 'Tampan',
                'price_min' => 500000,
                'price_max' => 1000000,
                'gender_type' => 'putra',
            ]);

        $response->assertStatus(403);
    }

    public function test_pemilik_can_create_kost_with_pending_status(): void
    {
        $pemilik = User::factory()->create(['role' => 'pemilik']);
        $token = $pemilik->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/owner/kosts', [
                'name' => 'Kost Baru Terverifikasi',
                'address' => 'Jl. Kenanga No 10',
                'city' => 'Pekanbaru',
                'district' => 'Sukajadi',
                'price_min' => 700000,
                'price_max' => 1500000,
                'gender_type' => 'campur',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Kost Baru Terverifikasi',
                    'verification_status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('kosts', [
            'name' => 'Kost Baru Terverifikasi',
            'owner_id' => $pemilik->id,
            'verification_status' => 'pending',
        ]);
    }

    public function test_pemilik_can_update_own_kost(): void
    {
        $pemilik = User::factory()->create(['role' => 'pemilik']);
        $token = $pemilik->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $pemilik->id,
            'name' => 'Nama Kost Lama',
            'address' => 'Jl. Lama No 1',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
            'verification_status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/owner/kosts/{$kost->id}", [
                'name' => 'Nama Kost Telah Diperbarui',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Nama Kost Telah Diperbarui',
                ],
            ]);

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'name' => 'Nama Kost Telah Diperbarui',
        ]);
    }

    public function test_pemilik_cannot_update_other_owner_kost(): void
    {
        $ownerA = User::factory()->create(['role' => 'pemilik']);
        $ownerB = User::factory()->create(['role' => 'pemilik']);
        $tokenB = $ownerB->createToken('test_token')->plainTextToken;

        $kostA = Kost::create([
            'owner_id' => $ownerA->id,
            'name' => 'Kost Owner A',
            'address' => 'Jl. A No 1',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
            'verification_status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->putJson("/api/owner/kosts/{$kostA->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_pemilik_can_delete_own_kost(): void
    {
        $pemilik = User::factory()->create(['role' => 'pemilik']);
        $token = $pemilik->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $pemilik->id,
            'name' => 'Kost Dihapus',
            'address' => 'Jl. Hapus',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/owner/kosts/{$kost->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('kosts', ['id' => $kost->id]);
    }
}
