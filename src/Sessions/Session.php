<?php

namespace JustinWoodring\LaravelQiskit\Sessions;

use Carbon\Carbon;

readonly class Session
{
    public function __construct(
        public string $id,
        public string $backend,
        public string $status,
        public bool $acceptingJobs,
        public ?Carbon $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            backend: $data['backend_name'] ?? $data['backend'] ?? '',
            status: $data['state'] ?? $data['status'] ?? 'unknown',
            acceptingJobs: (bool) ($data['accepting_jobs'] ?? true),
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
        );
    }

    public function isOpen(): bool
    {
        return strtolower($this->status) === 'open' && $this->acceptingJobs;
    }
}
