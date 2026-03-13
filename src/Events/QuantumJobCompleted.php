<?php

namespace JustinWoodring\LaravelQiskit\Events;

use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\PrimitiveResult;

readonly class QuantumJobCompleted
{
    public function __construct(
        public QuantumJob $job,
        public PrimitiveResult $result,
    ) {}
}
