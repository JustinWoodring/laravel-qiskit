<?php

namespace JustinWoodring\LaravelQiskit\Events;

use JustinWoodring\LaravelQiskit\Models\QuantumJob;

readonly class QuantumJobCancelled
{
    public function __construct(
        public QuantumJob $job,
    ) {}
}
