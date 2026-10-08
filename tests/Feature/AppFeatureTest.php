<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\ApplicationRelease;
use App\Models\PlatformSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_latest_application_release(): void
    {
        ApplicationRelease::create([
            'version_name' => 'v1.0.0-beta',
            'version_code' => 100,
            'apk_url' => 'https://example.com/app.apk',
            'file_size' => '18.4 MB',
            'is_latest' => true,
            'release_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/app/latest-release');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'version_name' => 'v1.0.0-beta',
                    'version_code' => 100,
                    'apk_url' => 'https://example.com/app.apk',
                ],
            ]);
    }

    public function test_can_retrieve_platform_settings(): void
    {
        PlatformSetting::set('contact_whatsapp', '6281270008899', 'string', 'contact');

        $response = $this->getJson('/api/v1/app/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'contact_whatsapp' => '6281270008899',
                    'default_city' => 'Pekanbaru',
                ],
            ]);
    }

    public function test_can_retrieve_active_ads_and_track_clicks(): void
    {
        $ad = Advertisement::create([
            'title' => 'Promo Mahasiswa Baru',
            'type' => 'Banner',
            'image_url' => 'https://example.com/banner.jpg',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'is_active' => true,
        ]);

        // Get active ads
        $response = $this->getJson('/api/v1/app/ads/active');
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Promo Mahasiswa Baru']);

        $this->assertEquals(1, $ad->fresh()->impressions);

        // Track click
        $clickResponse = $this->postJson("/api/v1/app/ads/{$ad->id}/click");
        $clickResponse->assertStatus(200);

        $this->assertEquals(1, $ad->fresh()->clicks);
    }

    public function test_can_record_crash_report(): void
    {
        $response = $this->postJson('/api/v1/app/crashes', [
            'device_model' => 'Xiaomi Redmi Note 10',
            'os_version' => 'Android 12',
            'app_version' => 'v1.0.0-beta',
            'exception_type' => 'NullPointerException',
            'stack_trace' => 'java.lang.NullPointerException at com.carikostkita.MainActivity.onCreate',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('crash_reports', [
            'device_model' => 'Xiaomi Redmi Note 10',
            'exception_type' => 'NullPointerException',
        ]);
    }

    public function test_can_record_telemetry_event(): void
    {
        $response = $this->postJson('/api/v1/app/events', [
            'event_name' => 'search_kost',
            'event_data' => ['keyword' => 'Sukajadi', 'price_max' => 1000000],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('app_events', [
            'event_name' => 'search_kost',
        ]);
    }
}
