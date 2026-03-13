<?php

namespace JustinWoodring\LaravelQiskit\Support;

use JustinWoodring\LaravelQiskit\Contracts\PrimitiveContract;
use JustinWoodring\LaravelQiskit\Jobs\DispatchQuantumJob;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\PrimitiveResult;

class PendingJob
{
    private ?string $queue = null;

    private ?string $connection = null;

    private ?string $sessionId = null;

    private bool $dispatched = false;

    public function __construct(
        private readonly PrimitiveContract $primitive,
    ) {}

    public function onQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    public function onConnection(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function inSession(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function dispatch(): QuantumJob
    {
        $this->dispatched = true;

        $payload = $this->primitive->toPayload();

        if ($this->sessionId !== null) {
            $payload['session_id'] = $this->sessionId;
        }

        $primitiveType = class_basename($this->primitive);

        $model = QuantumJob::create([
            'backend' => $payload['backend'] ?? config('qiskit.default_backend'),
            'primitive_type' => strtolower($primitiveType),
            'status' => QuantumJob::STATUS_PENDING,
            'payload' => $payload,
            'poll_count' => 0,
        ]);

        $job = DispatchQuantumJob::dispatch($payload, $model->id);

        if ($this->queue) {
            $job->onQueue($this->queue);
        }

        if ($this->connection) {
            $job->onConnection($this->connection);
        }

        return $model;
    }

    public function dispatchSync(): PrimitiveResult
    {
        $this->dispatched = true;

        return $this->primitive->dispatchSync();
    }

    public function __destruct()
    {
        if (! $this->dispatched) {
            $this->dispatch();
        }
    }
}
