<?php

namespace JustinWoodring\LaravelQiskit\Primitives;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Contracts\PrimitiveContract;
use JustinWoodring\LaravelQiskit\Support\PendingJob;

class Sampler implements PrimitiveContract
{
    private string $backend;

    private array $pubs = [];

    private ?string $sessionId = null;

    private function __construct(string $backend)
    {
        $this->backend = $backend;
    }

    public static function on(string $backend): self
    {
        return new self($backend);
    }

    public function addPub(Circuit|string $circuit, array $paramValues = [], int $shots = 1024): self
    {
        $qasm = $circuit instanceof Circuit ? $circuit->toQasm() : $circuit;

        $pub = ['circuit' => $qasm, 'shots' => $shots];

        if (! empty($paramValues)) {
            $pub['parameter_values'] = $paramValues;
        }

        $this->pubs[] = $pub;

        return $this;
    }

    public function inSession(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function toPayload(): array
    {
        $payload = [
            'program_id' => 'sampler',
            'backend' => $this->backend,
            'params' => [
                'pubs' => $this->pubs,
            ],
        ];

        if ($this->sessionId !== null) {
            $payload['session_id'] = $this->sessionId;
        }

        return $payload;
    }

    public function dispatch(): PendingJob
    {
        return new PendingJob($this);
    }

    public function dispatchSync(): PrimitiveResult
    {
        /** @var QiskitClient $client */
        $client = app(QiskitClient::class);

        $response = $client->submitJob($this->toPayload());

        $jobId = $response->get('id');

        // Poll synchronously until complete
        do {
            sleep(2);
            $status = $client->getJob($jobId)->get('state', []);
            $jobStatus = $status['status'] ?? 'running';
        } while (! in_array($jobStatus, ['Completed', 'Failed', 'Cancelled']));

        if ($jobStatus !== 'Completed') {
            throw new \RuntimeException("Job {$jobId} ended with status: {$jobStatus}");
        }

        $results = $client->getJobResults($jobId);

        return PrimitiveResult::fromArray($results->toArray());
    }
}
