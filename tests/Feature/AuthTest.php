<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_pencari_successfully(): void
    {
        $payload = [
            'name' => 'Ahmad Reza',
            'email' => 'ahmad@example.com',
            'password' => 'secret1234',
            'role' => 'pencari',
            'phone' => '08123456789',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registrasi berhasil',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'phone',
                        'profile',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'role' => 'pencari',
        ]);
    }

    public function test_user_can_register_as_pemilik_successfully(): void
    {
        $payload = [
            'name' => 'Bapak Pemilik Kost',
            'email' => 'pemilik_baru@example.com',
            'password' => 'secret1234',
            'role' => 'pemilik',
            'phone' => '081299887766',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'pemilik',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'pemilik_baru@example.com',
            'role' => 'pemilik',
        ]);
    }

    public function test_user_cannot_register_as_admin_via_public_api(): void
    {
        $payload = [
            'name' => 'Hacker Admin',
            'email' => 'hacker@example.com',
            'password' => 'secret1234',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'hacker@example.com',
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'duplikat@example.com',
        ]);

        $payload = [
            'name' => 'Duplikat User',
            'email' => 'duplikat@example.com',
            'password' => 'secret1234',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'userlogin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'userlogin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'email',
                    ],
                ],
            ]);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'wrongpass@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrongpass@example.com',
            'password' => 'incorrectpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }
}
