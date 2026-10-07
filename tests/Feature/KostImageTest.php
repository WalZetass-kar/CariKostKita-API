<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KostImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_can_upload_image_to_own_kost(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Image Test',
            'address' => 'Jl. Foto',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $file = UploadedFile::fake()->image('kost1.jpg', 600, 400);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/owner/kosts/{$kost->id}/images", [
                'image' => $file,
                'is_primary' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'kost_id' => $kost->id,
                    'is_primary' => true,
                ],
            ]);

        $this->assertDatabaseHas('kost_images', [
            'kost_id' => $kost->id,
            'is_primary' => true,
        ]);
    }

    public function test_pemilik_cannot_upload_image_to_other_owner_kost(): void
    {
        Storage::fake('public');

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

        $file = UploadedFile::fake()->image('kost_fake.jpg');

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->postJson("/api/owner/kosts/{$kostA->id}/images", [
                'image' => $file,
            ]);

        $response->assertStatus(403);
    }

    public function test_pemilik_can_set_image_as_primary(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Primary Test',
            'address' => 'Jl. Primary',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $img1 = $kost->images()->create([
            'file_path' => 'path/1.jpg',
            'url' => 'http://localhost/path/1.jpg',
            'is_primary' => true,
        ]);

        $img2 = $kost->images()->create([
            'file_path' => 'path/2.jpg',
            'url' => 'http://localhost/path/2.jpg',
            'is_primary' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/owner/images/{$img2->id}/primary");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $img2->id,
                    'is_primary' => true,
                ],
            ]);

        $this->assertDatabaseHas('kost_images', [
            'id' => $img1->id,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('kost_images', [
            'id' => $img2->id,
            'is_primary' => true,
        ]);
    }

    public function test_pemilik_can_delete_image(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'pemilik']);
        $token = $owner->createToken('test_token')->plainTextToken;

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Delete Image',
            'address' => 'Jl. Delete',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'putra',
        ]);

        $img = $kost->images()->create([
            'file_path' => 'path/delete.jpg',
            'url' => 'http://localhost/path/delete.jpg',
            'is_primary' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/owner/images/{$img->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('kost_images', ['id' => $img->id]);
    }
}
