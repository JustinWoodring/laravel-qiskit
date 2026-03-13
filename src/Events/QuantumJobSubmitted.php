<?php

namespace JustinWoodring\LaravelQiskit\Events;

use JustinWoodring\LaravelQiskit\Models\QuantumJob;

readonly class QuantumJobSubmitted
{
    public function __construct(
        public QuantumJob $job,
        public string $ibmJobId,
    ) {}
}
