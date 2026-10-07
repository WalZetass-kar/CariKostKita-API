<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Kost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_chat_with_kost_owner(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Chat Test',
            'address' => 'Jl. Chat',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $pencari->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/chats', [
                'kost_id' => $kost->id,
                'message' => 'Halo, apakah kamar masih tersedia?',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Percakapan berhasil dimulai',
            ]);

        $this->assertDatabaseHas('chats', [
            'user_id' => $pencari->id,
            'owner_id' => $owner->id,
            'kost_id' => $kost->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $pencari->id,
            'message' => 'Halo, apakah kamar masih tersedia?',
        ]);
    }

    public function test_user_cannot_chat_with_self_on_own_kost(): void
    {
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Sendiri Chat',
            'address' => 'Jl. Sendiri',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
            'verification_status' => 'approved',
        ]);

        $token = $owner->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/chats', [
                'kost_id' => $kost->id,
                'message' => 'Chat sendiri',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_can_send_and_receive_messages_in_chat(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Chat Mesg',
            'address' => 'Jl. Chat',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
        ]);

        $chat = Chat::create([
            'user_id' => $pencari->id,
            'owner_id' => $owner->id,
            'kost_id' => $kost->id,
        ]);

        $ownerToken = $owner->createToken('test_token')->plainTextToken;

        // Owner sends reply
        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->postJson("/api/chats/{$chat->id}/messages", [
                'message' => 'Ya, masih ada 2 kamar kosong.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Ya, masih ada 2 kamar kosong.',
                    'sender_id' => $owner->id,
                ],
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'sender_id' => $owner->id,
            'message' => 'Ya, masih ada 2 kamar kosong.',
        ]);
    }

    public function test_messages_are_marked_as_read_when_retrieved_by_receiver(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Read Test',
            'address' => 'Jl. Read',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
        ]);

        $chat = Chat::create([
            'user_id' => $pencari->id,
            'owner_id' => $owner->id,
            'kost_id' => $kost->id,
        ]);

        $msg = $chat->messages()->create([
            'sender_id' => $pencari->id,
            'message' => 'Pesan dari pencari',
            'is_read' => false,
        ]);

        $ownerToken = $owner->createToken('test_token')->plainTextToken;

        // Owner views messages
        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'id' => $msg->id,
            'is_read' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_access_chat_messages(): void
    {
        $pencari = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'pemilik']);
        $stranger = User::factory()->create(['role' => 'pencari']);

        $kost = Kost::create([
            'owner_id' => $owner->id,
            'name' => 'Kost Secret Chat',
            'address' => 'Jl. Secret',
            'city' => 'Pekanbaru',
            'district' => 'Sukajadi',
            'price_min' => 500000,
            'price_max' => 1000000,
            'gender_type' => 'campur',
        ]);

        $chat = Chat::create([
            'user_id' => $pencari->id,
            'owner_id' => $owner->id,
            'kost_id' => $kost->id,
        ]);

        $strangerToken = $stranger->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$strangerToken)
            ->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(403);
    }
}
