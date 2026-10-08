<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostgrestGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgrest_select_kosts_returns_array(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        for ($i = 1; $i <= 3; $i++) {
            Kost::create([
                'owner_id' => $owner->id,
                'name' => "Kost {$i}",
                'nama_kost' => "Kost {$i}",
                'address' => "Jl. Alamat {$i}",
                'city' => 'Pekanbaru',
                'district' => 'Tampan',
                'gender_type' => 'campur',
                'price_min' => 700000 * $i,
                'verification_status' => 'approved',
                'status' => 'TERSEDIA',
            ]);
        }

        $response = $this->getJson('/rest/v1/kosts?select=*');

        $response->assertStatus(200)
            ->assertHeader('Content-Range');

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
        $this->assertArrayHasKey('nama_kost', $data[0]);
        $this->assertArrayHasKey('alamat', $data[0]);
        $this->assertArrayHasKey('harga', $data[0]);
    }

    public function test_postgrest_select_with_filtering(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Harmoni Putri',
            'nama_kost' => 'Kost Harmoni Putri',
            'address' => 'Jl. Harmoni No 10',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'gender_type' => 'putri',
            'price_min' => 850000,
            'status' => 'TERSEDIA',
        ]);
        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Putra Melati',
            'nama_kost' => 'Kost Putra Melati',
            'address' => 'Jl. Melati No 12',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'gender_type' => 'putra',
            'price_min' => 1200000,
            'status' => 'TERSEDIA',
        ]);

        $response = $this->getJson('/rest/v1/kosts?harga=lte.1000000');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals('Kost Harmoni Putri', $data[0]['nama_kost']);
    }

    public function test_auth_v1_user_endpoint(): void
    {
        $user = User::factory()->create([
            'name' => 'Tester User',
            'email' => 'tester@example.com',
            'role' => 'pencari',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/auth/v1/user');

        $response->assertStatus(200)
            ->assertJsonPath('email', 'tester@example.com')
            ->assertJsonPath('user_metadata.name', 'Tester User');
    }
}
