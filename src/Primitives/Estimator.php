<?php

namespace JustinWoodring\LaravelQiskit\Primitives;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Contracts\PrimitiveContract;
use JustinWoodring\LaravelQiskit\Support\PendingJob;

class Estimator implements PrimitiveContract
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

    /**
     * @param  string|array  $observables  SparsePauliOp notation e.g. 'ZZ' or ['ZZ', 'XI']
     */
    public function addPub(Circuit|string $circuit, string|array $observables = [], array $paramValues = []): self
    {
        $qasm = $circuit instanceof Circuit ? $circuit->toQasm() : $circuit;

        $pub = [
            'circuit' => $qasm,
            'observables' => is_string($observables) ? [$observables] : $observables,
        ];

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
            'program_id' => 'estimator',
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
