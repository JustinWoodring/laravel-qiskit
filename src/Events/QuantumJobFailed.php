<?php

namespace JustinWoodring\LaravelQiskit\Events;

use JustinWoodring\LaravelQiskit\Models\QuantumJob;

readonly class QuantumJobFailed
{
    public function __construct(
        public QuantumJob $job,
        public ?string $reason,
    ) {}
}
