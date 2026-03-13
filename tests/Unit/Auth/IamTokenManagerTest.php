<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Auth;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JustinWoodring\LaravelQiskit\Auth\IamTokenManager;
use JustinWoodring\LaravelQiskit\Exceptions\AuthenticationException;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class IamTokenManagerTest extends TestCase
{
    private IamTokenManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new IamTokenManager(
            http: app(HttpFactory::class),
            cache: Cache::store(),
            apiKey: 'test-api-key',
            iamTokenUrl: 'https://fake.iam.cloud.ibm.com/identity/token',
            cachePrefix: 'test_qiskit_token',
        );
    }

    public function test_fetches_token_from_iam(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'my-token-123',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = $this->manager->getToken();

        $this->assertEquals('my-token-123', $token);
    }

    public function test_caches_token_on_second_call(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'cached-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $this->manager->getToken();
        $token = $this->manager->getToken();

        $this->assertEquals('cached-token', $token);
        Http::assertSentCount(1);
    }

    public function test_forget_token_clears_cache(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::sequence()
                ->push(['access_token' => 'first-token', 'expires_in' => 3600])
                ->push(['access_token' => 'second-token', 'expires_in' => 3600]),
        ]);

        $this->manager->getToken();
        $this->manager->forgetToken();
        $token = $this->manager->getToken();

        $this->assertEquals('second-token', $token);
    }

    public function test_throws_authentication_exception_on_failure(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response(['error' => 'invalid_apikey'], 400),
        ]);

        $this->expectException(AuthenticationException::class);

        $this->manager->getToken();
    }
}
