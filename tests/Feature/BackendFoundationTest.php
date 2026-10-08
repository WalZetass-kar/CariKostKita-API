<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Favorite;
use App\Models\Kost;
use App\Models\KostImage;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test database connection & schema integrity.
     */
    public function test_database_connection_and_required_tables_exist(): void
    {
        // Check PDO connectivity
        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);

        // Verify all 10 required tables from DATABASE.md exist
        $requiredTables = [
            'users',
            'profiles',
            'kosts',
            'kost_images',
            'rooms',
            'favorites',
            'chats',
            'messages',
            'reports',
            'notifications',
            'api_logs',
            'personal_access_tokens',
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table [{$table}] must exist in database.");
        }
    }

    /**
     * 2. Test Eloquent models and relationships integrity.
     */
    public function test_model_relationships_and_foreign_keys_work_correctly(): void
    {
        // Create user with profile
        $owner = User::factory()->create(['role' => 'pemilik']);
        $owner->profile()->create([
            'bio' => 'Owner bio',
            'city' => 'Pekanbaru',
        ]);

        $this->assertInstanceOf(Profile::class, $owner->profile);
        $this->assertEquals($owner->id, $owner->profile->user->id);

        // Create kost
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Foundation Test',
            'address' => 'Jl. Test No. 1',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $this->assertTrue($owner->kosts->contains($kost));
        $this->assertEquals($owner->id, $kost->owner->id);

        // Create image
        $image = KostImage::create([
            'kost_id' => $kost->id,
            'file_path' => 'kosts/test.jpg',
            'url' => 'https://example.com/test.jpg',
            'is_primary' => true,
        ]);
        $this->assertEquals($kost->id, $image->kost->id);

        // Create room
        $room = Room::create([
            'kost_id' => $kost->id,
            'name' => 'Kamar A',
            'price' => 600000,
            'total_rooms' => 5,
            'available_rooms' => 2,
        ]);
        $this->assertEquals($kost->id, $room->kost->id);

        // Create pencari
        $seeker = User::factory()->create(['role' => 'pencari']);

        // Favorite
        $favorite = Favorite::create([
            'user_id' => $seeker->id,
            'kost_id' => $kost->id,
        ]);
        $this->assertEquals($seeker->id, $favorite->user->id);
        $this->assertEquals($kost->id, $favorite->kost->id);

        // Chat & Message
        $chat = Chat::create([
            'user_id' => $seeker->id,
            'owner_id' => $owner->id,
            'kost_id' => $kost->id,
        ]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $seeker->id,
            'message' => 'Halo apakah masih ada kamar?',
            'is_read' => false,
        ]);
        $this->assertEquals($chat->id, $message->chat->id);
        $this->assertEquals($seeker->id, $message->sender->id);

        // Report
        $report = Report::create([
            'reporter_id' => $seeker->id,
            'kost_id' => $kost->id,
            'reason' => 'Foto tidak sesuai',
            'status' => 'pending',
        ]);
        $this->assertEquals($seeker->id, $report->reporter->id);
        $this->assertEquals($kost->id, $report->kost->id);

        // Notification
        $notification = Notification::create([
            'user_id' => $owner->id,
            'type' => 'chat',
            'title' => 'Pesan baru',
            'message' => 'Ada calon penyewa bertanya.',
        ]);
        $this->assertEquals($owner->id, $notification->user->id);
    }

    /**
     * 3. Test Register endpoint and automatic profile creation.
     */
    public function test_register_creates_user_and_associated_profile(): void
    {
        $payload = [
            'name' => 'Rina Wijaya',
            'email' => 'rina@example.com',
            'password' => 'password123',
            'role' => 'pencari',
            'phone' => '081234567890',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registrasi berhasil',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'profile',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'rina@example.com', 'role' => 'pencari']);
        $this->assertDatabaseHas('profiles', ['user_id' => $response->json('data.user.id')]);
    }

    /**
     * 4. Test Login and Token Generation.
     */
    public function test_login_authenticates_user_and_issues_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'tester@example.com',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'tester@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil',
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * 5. Test Logout invalidates access token.
     */
    public function test_logout_revokes_token_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);

        // Token should be deleted
        $this->assertCount(0, $user->fresh()->tokens);
    }

    /**
     * 6. Test Unauthorized requests return 401.
     */
    public function test_unauthorized_requests_return_401(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
        $this->postJson('/api/logout')->assertStatus(401);
        $this->getJson('/api/favorites')->assertStatus(401);
        $this->getJson('/api/chats')->assertStatus(401);
        $this->getJson('/api/owner/kosts')->assertStatus(401);
        $this->getJson('/api/admin/users')->assertStatus(401);
    }

    /**
     * 7. Test Role authorization: pencari, pemilik, admin boundaries.
     */
    public function test_role_authorization_enforces_boundaries(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $pemilik = User::factory()->create(['role' => 'pemilik']);
        $admin = User::factory()->create(['role' => 'admin']);

        $tokenPencari = $pencari->createToken('token')->plainTextToken;
        $tokenPemilik = $pemilik->createToken('token')->plainTextToken;
        $tokenAdmin = $admin->createToken('token')->plainTextToken;

        // Pencari cannot access owner endpoints
        $this->withHeader('Authorization', 'Bearer '.$tokenPencari)
            ->getJson('/api/owner/kosts')
            ->assertStatus(403);

        auth()->forgetGuards();

        // Pemilik cannot access admin endpoints
        $this->withHeader('Authorization', 'Bearer '.$tokenPemilik)
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        auth()->forgetGuards();

        // Pencari cannot access admin endpoints
        $this->withHeader('Authorization', 'Bearer '.$tokenPencari)
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        auth()->forgetGuards();

        // Admin can access admin endpoints
        $this->withHeader('Authorization', 'Bearer '.$tokenAdmin)
            ->getJson('/api/admin/users')
            ->assertStatus(200);

        auth()->forgetGuards();

        // Pemilik can access owner endpoints
        $this->withHeader('Authorization', 'Bearer '.$tokenPemilik)
            ->getJson('/api/owner/kosts')
            ->assertStatus(200);
    }

    /**
     * 8. Test Pemilik only manages their own kosts (KostPolicy).
     */
    public function test_owner_can_only_manage_own_kosts(): void
    {
        $owner1 = User::factory()->create(['role' => 'pemilik']);
        $owner2 = User::factory()->create(['role' => 'pemilik']);

        $kost1 = Kost::create([
            'owner_id' => $owner1->id,
            'name' => 'Kost Owner 1',
            'address' => 'Jl. Satu',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $tokenOwner2 = $owner2->createToken('token')->plainTextToken;

        // Owner 2 attempts to update Owner 1's kost
        $response = $this->withHeader('Authorization', 'Bearer '.$tokenOwner2)
            ->putJson("/api/owner/kosts/{$kost1->id}", [
                'name' => 'Hacked Kost Name',
            ]);

        $response->assertStatus(403);

        // Verify database was NOT changed
        $this->assertEquals('Kost Owner 1', $kost1->fresh()->name);
    }

    /**
     * 9. Test Profile retrieval and update.
     */
    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create(['role' => 'pencari', 'name' => 'Original Name']);
        $token = $user->createToken('token')->plainTextToken;

        // View profile
        $viewResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $viewResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Original Name',
                ],
            ]);

        // Update profile
        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user/profile', [
                'name' => 'Updated User Name',
                'phone' => '089988776655',
                'bio' => 'Mahasiswa tingkat akhir sedang cari kost tenang.',
                'city' => 'Yogyakarta',
                'district' => 'Depok',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'name' => 'Updated User Name',
                    'phone' => '089988776655',
                    'profile' => [
                        'bio' => 'Mahasiswa tingkat akhir sedang cari kost tenang.',
                        'city' => 'Yogyakarta',
                        'district' => 'Depok',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User Name']);
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'city' => 'Yogyakarta']);
    }
}
