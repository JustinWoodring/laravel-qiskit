<?php

namespace JustinWoodring\LaravelQiskit\Circuit;

readonly class Gate
{
    public function __construct(
        public string $name,
        public array $qubits,
        public array $params = [],
        public ?int $cbit = null,
    ) {}
}
