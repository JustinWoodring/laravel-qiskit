<?php

namespace JustinWoodring\LaravelQiskit\Primitives;

readonly class PrimitiveResult
{
    public function __construct(
        private array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function getCounts(): array
    {
        // IBM Sampler result structure: results[0].data.counts
        return $this->raw['results'][0]['data']['counts'] ?? [];
    }

    public function getExpectationValues(): array
    {
        // IBM Estimator result structure: results[0].data.evs
        return $this->raw['results'][0]['data']['evs'] ?? [];
    }

    public function getPubResults(): array
    {
        return $this->raw['results'] ?? [];
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}
