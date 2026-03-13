<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Circuit;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Exceptions\CircuitValidationException;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class CircuitTest extends TestCase
{
    public function test_creates_circuit_with_named_constructor(): void
    {
        $circuit = Circuit::new(2, 2);

        $this->assertEquals(2, $circuit->qubits);
        $this->assertEquals(2, $circuit->cbits);
    }

    public function test_throws_on_zero_qubits(): void
    {
        $this->expectException(CircuitValidationException::class);

        Circuit::new(0);
    }

    public function test_single_qubit_gates_chain(): void
    {
        $circuit = Circuit::new(2)
            ->h(0)
            ->x(1)
            ->y(0)
            ->z(1)
            ->s(0)
            ->t(1);

        $this->assertCount(6, $circuit->gates);
    }

    public function test_two_qubit_gates(): void
    {
        $circuit = Circuit::new(2)
            ->cx(0, 1)
            ->cz(0, 1)
            ->swap(0, 1);

        $this->assertCount(3, $circuit->gates);
    }

    public function test_cnot_alias_for_cx(): void
    {
        $circuit = Circuit::new(2)->cnot(0, 1);
        $this->assertEquals('cx', $circuit->gates[0]->name);
    }

    public function test_toffoli_alias_for_ccx(): void
    {
        $circuit = Circuit::new(3)->toffoli(0, 1, 2);
        $this->assertEquals('ccx', $circuit->gates[0]->name);
    }

    public function test_rotation_gates_store_params(): void
    {
        $circuit = Circuit::new(1)
            ->rx(1.57, 0)
            ->ry(3.14, 0)
            ->rz(0.5, 0);

        $this->assertEquals([1.57], $circuit->gates[0]->params);
        $this->assertEquals([3.14], $circuit->gates[1]->params);
        $this->assertEquals([0.5], $circuit->gates[2]->params);
    }

    public function test_measure_all_qubits(): void
    {
        $circuit = Circuit::new(3)->measure();

        $this->assertCount(3, $circuit->gates);
        $this->assertEquals('measure', $circuit->gates[0]->name);
        $this->assertEquals('measure', $circuit->gates[2]->name);
    }

    public function test_measure_specific_qubits(): void
    {
        $circuit = Circuit::new(3)->measure([0, 2]);

        $this->assertCount(2, $circuit->gates);
        $this->assertEquals([0], $circuit->gates[0]->qubits);
        $this->assertEquals([2], $circuit->gates[1]->qubits);
    }

    public function test_measure_qubit(): void
    {
        $circuit = Circuit::new(2)->measureQubit(1, 0);

        $this->assertCount(1, $circuit->gates);
        $this->assertEquals([1], $circuit->gates[0]->qubits);
        $this->assertEquals(0, $circuit->gates[0]->cbit);
    }

    public function test_throws_on_out_of_range_qubit(): void
    {
        $this->expectException(CircuitValidationException::class);

        Circuit::new(2)->h(5);
    }

    public function test_bind_returns_clone_with_params(): void
    {
        $circuit = Circuit::new(1)->withParameters(['theta'])->rx('theta', 0);
        $bound = $circuit->bind(['theta' => 1.57]);

        $this->assertEquals(['theta' => 1.57], $bound->boundParameters);
        $this->assertEmpty($circuit->boundParameters);
    }

    public function test_to_string_returns_qasm(): void
    {
        $circuit = Circuit::new(1)->h(0)->measure();
        $qasm = (string) $circuit;

        $this->assertStringContainsString('OPENQASM 3.0', $qasm);
        $this->assertStringContainsString('h q[0]', $qasm);
    }
}
