<?php

namespace JustinWoodring\LaravelQiskit\Circuit;

use JustinWoodring\LaravelQiskit\Circuit\Serializer\OpenQasm3Serializer;
use JustinWoodring\LaravelQiskit\Contracts\CircuitContract;
use JustinWoodring\LaravelQiskit\Exceptions\CircuitValidationException;

class Circuit implements CircuitContract
{
    /** @var Gate[] */
    public array $gates = [];

    public array $parameters = [];

    public array $boundParameters = [];

    private function __construct(
        public readonly int $qubits,
        public readonly int $cbits,
    ) {}

    public static function new(int $qubits, int $cbits = 0): self
    {
        if ($qubits < 1) {
            throw new CircuitValidationException('Circuit must have at least 1 qubit.');
        }

        return new self($qubits, $cbits);
    }

    // --- Single-qubit gates ---

    public function h(int $qubit): self
    {
        return $this->addGate('h', [$qubit]);
    }

    public function x(int $qubit): self
    {
        return $this->addGate('x', [$qubit]);
    }

    public function y(int $qubit): self
    {
        return $this->addGate('y', [$qubit]);
    }

    public function z(int $qubit): self
    {
        return $this->addGate('z', [$qubit]);
    }

    public function s(int $qubit): self
    {
        return $this->addGate('s', [$qubit]);
    }

    public function t(int $qubit): self
    {
        return $this->addGate('t', [$qubit]);
    }

    // --- Rotation gates ---

    public function rx(float|string $theta, int $qubit): self
    {
        return $this->addGate('rx', [$qubit], [$theta]);
    }

    public function ry(float|string $theta, int $qubit): self
    {
        return $this->addGate('ry', [$qubit], [$theta]);
    }

    public function rz(float|string $phi, int $qubit): self
    {
        return $this->addGate('rz', [$qubit], [$phi]);
    }

    // --- Two-qubit gates ---

    public function cx(int $control, int $target): self
    {
        return $this->addGate('cx', [$control, $target]);
    }

    public function cnot(int $control, int $target): self
    {
        return $this->cx($control, $target);
    }

    public function cz(int $control, int $target): self
    {
        return $this->addGate('cz', [$control, $target]);
    }

    public function swap(int $qubit1, int $qubit2): self
    {
        return $this->addGate('swap', [$qubit1, $qubit2]);
    }

    // --- Three-qubit gates ---

    public function ccx(int $control1, int $control2, int $target): self
    {
        return $this->addGate('ccx', [$control1, $control2, $target]);
    }

    public function toffoli(int $control1, int $control2, int $target): self
    {
        return $this->ccx($control1, $control2, $target);
    }

    // --- Measurement ---

    public function measure(?array $qubits = null): self
    {
        $qubits ??= range(0, $this->qubits - 1);

        foreach ($qubits as $index => $qubit) {
            $this->validateQubit($qubit);
            $this->gates[] = new Gate('measure', [$qubit], [], $index);
        }

        return $this;
    }

    public function measureQubit(int $qubit, int $cbit): self
    {
        $this->validateQubit($qubit);
        $this->gates[] = new Gate('measure', [$qubit], [], $cbit);

        return $this;
    }

    // --- Parameterized circuits ---

    public function withParameters(array $parameterNames): self
    {
        $this->parameters = $parameterNames;

        return $this;
    }

    public function bind(array $values): self
    {
        $clone = clone $this;
        $clone->boundParameters = array_merge($clone->boundParameters, $values);

        return $clone;
    }

    // --- Serialization ---

    public function toQasm(): string
    {
        return (new OpenQasm3Serializer())->serialize($this);
    }

    public function __toString(): string
    {
        return $this->toQasm();
    }

    // --- Internal ---

    private function addGate(string $name, array $qubits, array $params = []): self
    {
        foreach ($qubits as $qubit) {
            $this->validateQubit($qubit);
        }

        $this->gates[] = new Gate($name, $qubits, $params);

        return $this;
    }

    private function validateQubit(int $qubit): void
    {
        if ($qubit < 0 || $qubit >= $this->qubits) {
            throw new CircuitValidationException(
                "Qubit index {$qubit} is out of range for a {$this->qubits}-qubit circuit."
            );
        }
    }
}
