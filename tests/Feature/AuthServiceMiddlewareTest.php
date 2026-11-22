<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthServiceMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_service_blocks_unauthenticated_request_when_enabled(): void
    {
        Config::set('services.auth_service.enabled', true);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_auth_service_allows_request_with_valid_token_when_enabled(): void
    {
        Config::set('services.auth_service.enabled', true);

        // Sử dụng token kiểm thử hợp lệ được hỗ trợ bởi VerifyAuthService
        $response = $this->withToken('test-token-valid')->getJson('/api/orders');

        $response->assertStatus(200);
    }

    public function test_auth_service_validates_token_against_auth_service_url(): void
    {
        Config::set('services.auth_service.enabled', true);
        Config::set('services.auth_service.url', 'http://localhost:8001/api/verify');

        Http::fake([
            'http://localhost:8001/api/verify' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 42,
                    'name' => 'Nguyễn Văn A',
                    'email' => 'a@example.com',
                ],
            ], 200),
        ]);

        $response = $this->withToken('remote-jwt-token-xyz')->getJson('/api/orders');

        $response->assertStatus(200);
    }

    public function test_auth_service_rejects_invalid_token_from_auth_service(): void
    {
        Config::set('services.auth_service.enabled', true);
        Config::set('services.auth_service.url', 'http://localhost:8001/api/verify');

        Http::fake([
            'http://localhost:8001/api/verify' => Http::response([
                'status' => false,
                'message' => 'Token không hợp lệ',
            ], 401),
        ]);

        $response = $this->withToken('invalid-or-expired-token')->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }
}
