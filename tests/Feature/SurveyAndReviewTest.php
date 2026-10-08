<?php

namespace Tests\Feature;

use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyAndReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_pencari_can_schedule_survey_and_owner_can_confirm(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $pencari = User::factory()->create(['role' => 'pencari']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Teratai Indah',
            'address' => 'Jl. Teratai',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 700000,
            'price_max' => 1200000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $tokenPencari = $pencari->createToken('token')->plainTextToken;
        $tokenOwner = $owner->createToken('token')->plainTextToken;

        // Pencari schedules survey
        $response = $this->withHeader('Authorization', 'Bearer '.$tokenPencari)
            ->postJson('/api/v1/surveys', [
                'kost_id' => $kost->id,
                'survey_date' => now()->addDays(2)->toIso8601String(),
                'notes' => 'Ingin melihat sirkulasi udara kamar.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'PENDING',
                    'kost_id' => $kost->id,
                ],
            ]);

        $surveyId = $response->json('data.id');

        auth()->forgetGuards();

        // Owner lists incoming surveys
        $ownerListResponse = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->getJson('/api/v1/owner/surveys');

        $ownerListResponse->assertStatus(200)
            ->assertJsonFragment(['id' => $surveyId]);

        auth()->forgetGuards();

        // Owner confirms survey
        $confirmResponse = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->patchJson("/api/v1/owner/surveys/{$surveyId}", [
                'status' => 'COMPLETED',
                'owner_notes' => 'Survei selesai.',
            ]);

        $confirmResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'COMPLETED',
                ],
            ]);

        auth()->forgetGuards();

        // Pencari submits review after survey
        $reviewResponse = $this->withHeader('Authorization', 'Bearer '.$tokenPencari)
            ->postJson('/api/v1/reviews', [
                'kost_id' => $kost->id,
                'survey_request_id' => $surveyId,
                'rating' => 5,
                'comment' => 'Kost bersih dan aman.',
            ]);

        $reviewResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'rating' => 5,
                ],
            ]);

        // Check rating_avg on kost was updated
        $this->assertEquals(5.0, (float) $kost->fresh()->rating_avg);
        $this->assertEquals(1, $kost->fresh()->rating_count);
    }

    public function test_user_cannot_survey_own_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Sendiri',
            'address' => 'Jl. Sendiri',
            'city' => 'Pekanbaru',
            'district' => 'Tampan',
            'price_min' => 500000,
            'price_max' => 800000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $tokenOwner = $owner->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->postJson('/api/v1/surveys', [
                'kost_id' => $kost->id,
                'survey_date' => now()->addDay()->toIso8601String(),
            ]);

        $response->assertStatus(422);
    }
}
