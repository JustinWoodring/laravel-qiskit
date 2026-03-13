<?php

namespace JustinWoodring\LaravelQiskit\Client;

readonly class QiskitResponse
{
    public function __construct(
        public int $status,
        public array $body,
    ) {}

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function toArray(): array
    {
        return $this->body;
    }
}
