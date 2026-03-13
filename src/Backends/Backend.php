<?php

namespace JustinWoodring\LaravelQiskit\Backends;

readonly class Backend
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
        public int $qubits,
        public int $queueDepth,
        public bool $isSimulator,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['name'] ?? $data['id'] ?? '',
            name: $data['name'] ?? '',
            status: $data['status'] ?? 'unknown',
            qubits: (int) ($data['n_qubits'] ?? $data['qubits'] ?? 0),
            queueDepth: (int) ($data['pending_jobs'] ?? $data['queue_depth'] ?? 0),
            isSimulator: (bool) ($data['simulator'] ?? false),
        );
    }

    public function isOnline(): bool
    {
        return strtolower($this->status) === 'online';
    }
}
