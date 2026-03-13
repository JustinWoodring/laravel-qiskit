<?php

namespace JustinWoodring\LaravelQiskit\Circuit\Serializer;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Circuit\Gate;

class OpenQasm3Serializer
{
    public function serialize(Circuit $circuit): string
    {
        $lines = [];

        $lines[] = 'OPENQASM 3.0;';
        $lines[] = 'include "stdgates.inc";';
        $lines[] = '';

        $lines[] = "qubit[{$circuit->qubits}] q;";

        if ($circuit->cbits > 0) {
            $lines[] = "bit[{$circuit->cbits}] c;";
        }

        $lines[] = '';

        foreach ($circuit->gates as $gate) {
            $lines[] = $this->serializeGate($gate, $circuit);
        }

        return implode("\n", $lines);
    }

    private function serializeGate(Gate $gate, Circuit $circuit): string
    {
        $params = $this->resolveParams($gate->params, $circuit->boundParameters);

        return match ($gate->name) {
            'measure' => $this->serializeMeasure($gate),
            default => $this->serializeStandardGate($gate->name, $gate->qubits, $params),
        };
    }

    private function serializeStandardGate(string $name, array $qubits, array $params): string
    {
        $qubitStr = implode(', ', array_map(fn ($q) => "q[{$q}]", $qubits));

        if (empty($params)) {
            return "{$name} {$qubitStr};";
        }

        $paramStr = implode(', ', array_map(fn ($p) => $this->formatParam($p), $params));

        return "{$name}({$paramStr}) {$qubitStr};";
    }

    private function serializeMeasure(Gate $gate): string
    {
        $qubit = $gate->qubits[0];
        $cbit = $gate->cbit ?? $qubit;

        return "c[{$cbit}] = measure q[{$qubit}];";
    }

    private function resolveParams(array $params, array $bound): array
    {
        return array_map(function ($param) use ($bound) {
            if (is_string($param) && isset($bound[$param])) {
                return $bound[$param];
            }

            return $param;
        }, $params);
    }

    private function formatParam(mixed $param): string
    {
        if (is_float($param)) {
            return rtrim(rtrim(number_format($param, 10, '.', ''), '0'), '.');
        }

        return (string) $param;
    }
}
