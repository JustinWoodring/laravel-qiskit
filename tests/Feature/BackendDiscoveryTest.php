<?php

namespace JustinWoodring\LaravelQiskit\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustinWoodring\LaravelQiskit\Backends\Backend;
use JustinWoodring\LaravelQiskit\Backends\BackendRepository;
use JustinWoodring\LaravelQiskit\Exceptions\BackendUnavailableException;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class BackendDiscoveryTest extends TestCase
{
    private array $fakeBackends = [
        ['name' => 'ibm_brisbane', 'status' => 'online', 'n_qubits' => 127, 'pending_jobs' => 5, 'simulator' => false],
        ['name' => 'ibm_osaka', 'status' => 'online', 'n_qubits' => 127, 'pending_jobs' => 2, 'simulator' => false],
        ['name' => 'ibm_kyoto', 'status' => 'offline', 'n_qubits' => 127, 'pending_jobs' => 0, 'simulator' => false],
        ['name' => 'simulator_statevector', 'status' => 'online', 'n_qubits' => 32, 'pending_jobs' => 0, 'simulator' => true],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'fake.quantum-computing.cloud.ibm.com/v1/backends' => Http::response(
                ['backends' => $this->fakeBackends],
                200
            ),
            'fake.quantum-computing.cloud.ibm.com/v1/backends/ibm_brisbane/status' => Http::response(
                ['status' => 'online', 'pending_jobs' => 5],
                200
            ),
        ]);
    }

    public function test_lists_all_backends(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->all();

        $this->assertCount(4, $backends);
        $this->assertContainsOnlyInstancesOf(Backend::class, $backends);
    }

    public function test_available_returns_online_backends(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->available();

        foreach ($backends as $backend) {
            $this->assertTrue($backend->isOnline());
        }
        $this->assertCount(3, $backends);
    }

    public function test_filter_by_min_qubits(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->filter()->withMinQubits(100)->get();

        foreach ($backends as $backend) {
            $this->assertGreaterThanOrEqual(100, $backend->qubits);
        }
    }

    public function test_filter_simulators_only(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->filter()->simulator()->get();

        foreach ($backends as $backend) {
            $this->assertTrue($backend->isSimulator);
        }
        $this->assertCount(1, $backends);
    }

    public function test_filter_non_simulators(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->filter()->simulator(false)->get();

        foreach ($backends as $backend) {
            $this->assertFalse($backend->isSimulator);
        }
    }

    public function test_filter_chaining(): void
    {
        $repository = app(BackendRepository::class);
        $backends = $repository->filter()
            ->online()
            ->withMinQubits(100)
            ->simulator(false)
            ->get();

        $this->assertCount(2, $backends);
    }

    public function test_find_returns_backend_by_id(): void
    {
        $repository = app(BackendRepository::class);
        $backend = $repository->find('ibm_brisbane');

        $this->assertEquals('ibm_brisbane', $backend->name);
    }

    public function test_find_throws_on_unknown_backend(): void
    {
        $repository = app(BackendRepository::class);

        $this->expectException(BackendUnavailableException::class);

        $repository->find('ibm_nonexistent');
    }

    public function test_backend_status(): void
    {
        $repository = app(BackendRepository::class);
        $response = $repository->status('ibm_brisbane');

        $this->assertTrue($response->successful());
        $this->assertEquals('online', $response->get('status'));
    }
}
