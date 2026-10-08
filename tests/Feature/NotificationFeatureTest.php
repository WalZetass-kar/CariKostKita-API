<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_notifications(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Pesan Baru',
            'message' => 'Anda mendapatkan pesan baru',
            'type' => 'chat',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_fcm_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/notifications/fcm-token', [
            'fcm_token' => 'test_device_fcm_token_xyz',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.fcm_token', 'test_device_fcm_token_xyz');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'test_device_fcm_token_xyz',
        ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Survei Dikonfirmasi',
            'message' => 'Pemilik menyetujui jadwal survei',
            'type' => 'survey',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'type' => 'system',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }
}
