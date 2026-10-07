<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CariKostKita API');
        $response->assertSee('REST API & Backend Infrastructure', false);
        $response->assertSee('Operational');
    }

    public function test_root_route_returns_json_when_requested_by_api_clients(): void
    {
        $response = $this->getJson('/');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => 'CariKostKita API',
                'status' => 'online',
            ])
            ->assertJsonStructure([
                'success',
                'name',
                'version',
                'status',
                'documentation',
                'health_check',
            ]);
    }

    public function test_api_health_endpoint_returns_operational_status(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'ok',
                'services' => [
                    'api' => 'ok',
                    'database' => 'ok',
                    'storage' => 'ok',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'timestamp',
                'services' => [
                    'api',
                    'database',
                    'storage',
                    'cache',
                ],
            ]);
    }

    public function test_documentation_page_renders_registered_routes(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('API Documentation');
        $response->assertSee('/api/kosts');
        $response->assertSee('/api/login');
        $response->assertSee('/api/register');
    }

    public function test_api_tester_page_renders_successfully(): void
    {
        $response = $this->get('/docs/tester');

        $response->assertStatus(200);
        $response->assertSee('REST API Tester');
        $response->assertSee('Send Request');
    }

    public function test_api_logs_page_renders_successfully(): void
    {
        $response = $this->get('/docs/logs');

        $response->assertStatus(200);
        $response->assertSee('Request Logs & Telemetry', false);
    }

    public function test_api_request_logger_middleware_records_api_traffic(): void
    {
        // Make an API request
        $this->getJson('/api/health');

        $this->assertDatabaseHas('api_logs', [
            'method' => 'GET',
            'path' => '/api/health',
            'status' => 200,
        ]);
    }
}
