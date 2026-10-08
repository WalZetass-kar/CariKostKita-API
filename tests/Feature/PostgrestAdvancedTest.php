<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgrestAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgrest_supports_nested_compound_or_and_filters(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);

        DB::table('user_blocks')->insert([
            'blocker_id' => $userA->id,
            'blocked_id' => $userB->id,
            'created_at' => now(),
        ]);

        $query = "or=(and(blocker_id.eq.{$userA->id},blocked_id.eq.{$userB->id}),and(blocker_id.eq.{$userB->id},blocked_id.eq.{$userA->id}))";
        $response = $this->getJson("/rest/v1/user_blocks?{$query}");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_postgrest_supports_order_with_nullslast(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);

        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Alpha',
            'address' => 'Jl. Garuda No. 1',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 500000,
            'price_max' => 500000,
            'gender_type' => 'campur',
            'created_at' => now()->subDay(),
        ]);
        Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Beta',
            'address' => 'Jl. Garuda No. 2',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 600000,
            'price_max' => 600000,
            'gender_type' => 'campur',
            'created_at' => now(),
        ]);

        $response = $this->getJson('/rest/v1/kosts?order=price_min.desc.nullslast');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('Kost Beta', $data[0]['nama_kost']);
    }

    public function test_rpc_owner_favorite_count_returns_scalar(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Gamma',
            'address' => 'Jl. Garuda No. 3',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 700000,
            'price_max' => 700000,
            'gender_type' => 'campur',
        ]);
        $seeker = User::factory()->create();

        DB::table('favorites')->insert([
            'user_id' => $seeker->id,
            'kost_id' => $kost->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/rest/v1/rpc/owner_favorite_count', []);

        $response->assertStatus(200);
        $this->assertEquals('1', trim($response->getContent()));
    }

    public function test_rpc_admin_funnel_returns_array(): void
    {
        $response = $this->postJson('/rest/v1/rpc/admin_funnel', ['days' => 30]);

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_realtime_websocket_endpoint_returns_status(): void
    {
        $response = $this->getJson('/realtime/v1/websocket');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'online')
            ->assertJsonPath('fallback_polling', true);
    }

    public function test_storage_serve_public_redirects_to_supabase_bucket(): void
    {
        $response = $this->get('/storage/v1/object/public/kost-images/sample-image.jpg');

        $response->assertStatus(302);
        $this->assertStringContainsString('supabase.co/storage/v1/object/public/kost-images/sample-image.jpg', $response->headers->get('Location'));
    }
}
