<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Client;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use JustinWoodring\LaravelQiskit\Auth\IamTokenManager;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Client\QiskitResponse;
use JustinWoodring\LaravelQiskit\Exceptions\AuthenticationException;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class QiskitClientTest extends TestCase
{
    private QiskitClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $tokenManager = $this->createMock(IamTokenManager::class);
        $tokenManager->method('getToken')->willReturn('fake-bearer-token');

        $this->client = new QiskitClient(
            http: app(HttpFactory::class),
            tokenManager: $tokenManager,
            baseUrl: 'https://fake.quantum-computing.cloud.ibm.com',
            serviceCrn: 'test-crn',
            timeout: 30,
            retryTimes: 1,
            retrySleep: 100,
        );
    }

    public function test_successful_get_request(): void
    {
        Http::fake([
            'fake.quantum-computing.cloud.ibm.com/v1/backends' => Http::response(
                ['backends' => [['name' => 'ibm_test', 'n_qubits' => 127]]],
                200
            ),
        ]);

        $response = $this->client->request('GET', '/v1/backends');

        $this->assertInstanceOf(QiskitResponse::class, $response);
        $this->assertEquals(200, $response->status);
        $this->assertTrue($response->successful());
    }

    public function test_sends_auth_header(): void
    {
        Http::fake([
            'fake.quantum-computing.cloud.ibm.com/*' => Http::response(['id' => 'job-123'], 200),
        ]);

        $this->client->request('POST', '/v1/jobs', ['backend' => 'ibm_test']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer fake-bearer-token')
                && $request->hasHeader('Service-CRN', 'test-crn');
        });
    }

    public function test_retries_on_401_after_forgetting_token(): void
    {
        $tokenManager = $this->createMock(IamTokenManager::class);
        $tokenManager->expects($this->once())->method('forgetToken');
        $tokenManager->method('getToken')->willReturn('fake-token');

        $client = new QiskitClient(
            http: app(HttpFactory::class),
            tokenManager: $tokenManager,
            baseUrl: 'https://fake.quantum-computing.cloud.ibm.com',
            serviceCrn: 'test-crn',
            timeout: 30,
            retryTimes: 1,
            retrySleep: 100,
        );

        Http::fake([
            'fake.quantum-computing.cloud.ibm.com/*' => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['id' => 'job-123'], 200),
        ]);

        $response = $client->request('GET', '/v1/jobs');

        $this->assertEquals(200, $response->status);
    }

    public function test_throws_authentication_exception_on_repeated_401(): void
    {
        Http::fake([
            'fake.quantum-computing.cloud.ibm.com/*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->expectException(AuthenticationException::class);

        $this->client->request('GET', '/v1/jobs');
    }

    public function test_list_jobs_endpoint(): void
    {
        Http::fake([
            'fake.quantum-computing.cloud.ibm.com/v1/jobs*' => Http::response(
                ['jobs' => [], 'count' => 0],
                200
            ),
        ]);

        $response = $this->client->listJobs(10, 0);

        $this->assertTrue($response->successful());
    }
}
