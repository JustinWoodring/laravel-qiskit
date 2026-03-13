<?php

namespace JustinWoodring\LaravelQiskit\Support;

use Illuminate\Database\Eloquent\Builder;
use JustinWoodring\LaravelQiskit\Backends\BackendRepository;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\Estimator;
use JustinWoodring\LaravelQiskit\Primitives\Sampler;
use JustinWoodring\LaravelQiskit\Sessions\SessionManager;

class QiskitManager
{
    public function __construct(
        private readonly QiskitClient $client,
        private readonly BackendRepository $backendRepository,
        private readonly SessionManager $sessionManager,
    ) {}

    public function sampler(?string $backend = null): Sampler
    {
        return Sampler::on($backend ?? config('qiskit.default_backend'));
    }

    public function estimator(?string $backend = null): Estimator
    {
        return Estimator::on($backend ?? config('qiskit.default_backend'));
    }

    public function run(Circuit|string $circuit, ?string $backend = null): PendingJob
    {
        return $this->sampler($backend)->addPub($circuit)->dispatch();
    }

    public function job(int|string $id): QuantumJob
    {
        return QuantumJob::findOrFail($id);
    }

    public function jobs(): Builder
    {
        return QuantumJob::query();
    }

    public function sessions(): SessionManager
    {
        return $this->sessionManager;
    }

    public function backends(): BackendRepository
    {
        return $this->backendRepository;
    }

    public function client(): QiskitClient
    {
        return $this->client;
    }
}
