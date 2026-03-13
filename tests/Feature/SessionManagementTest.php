<?php

namespace JustinWoodring\LaravelQiskit\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustinWoodring\LaravelQiskit\Sessions\Session;
use JustinWoodring\LaravelQiskit\Sessions\SessionManager;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class SessionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'fake.quantum-computing.cloud.ibm.com/v1/sessions' => Http::response([
                'id' => 'session-abc',
                'backend_name' => 'ibm_test',
                'state' => 'open',
                'accepting_jobs' => true,
                'created_at' => now()->toIso8601String(),
            ], 200),
            'fake.quantum-computing.cloud.ibm.com/v1/sessions/session-abc' => Http::response([
                'id' => 'session-abc',
                'backend_name' => 'ibm_test',
                'state' => 'open',
                'accepting_jobs' => true,
                'created_at' => now()->toIso8601String(),
            ], 200),
            'fake.quantum-computing.cloud.ibm.com/v1/sessions/session-abc/close' => Http::response([], 204),
        ]);
    }

    public function test_open_session_returns_session_object(): void
    {
        $manager = app(SessionManager::class);
        $session = $manager->open('ibm_test');

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals('session-abc', $session->id);
        $this->assertEquals('ibm_test', $session->backend);
        $this->assertTrue($session->isOpen());
    }

    public function test_get_session(): void
    {
        $manager = app(SessionManager::class);
        $session = $manager->get('session-abc');

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals('session-abc', $session->id);
    }

    public function test_run_opens_and_closes_session(): void
    {
        $manager = app(SessionManager::class);

        $capturedId = null;

        $manager->run('ibm_test', function (string $sessionId) use (&$capturedId) {
            $capturedId = $sessionId;

            return 'result';
        });

        $this->assertEquals('session-abc', $capturedId);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/sessions/session-abc/close');
        });
    }

    public function test_run_closes_session_even_on_exception(): void
    {
        $manager = app(SessionManager::class);

        try {
            $manager->run('ibm_test', function () {
                throw new \RuntimeException('Something went wrong');
            });
        } catch (\RuntimeException) {
            // expected
        }

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/sessions/session-abc/close');
        });
    }

    public function test_close_session(): void
    {
        $manager = app(SessionManager::class);
        $manager->close('session-abc');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/sessions/session-abc/close');
        });
    }
}
