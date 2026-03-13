<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Circuit;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Circuit\Serializer\OpenQasm3Serializer;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class OpenQasm3SerializerTest extends TestCase
{
    private OpenQasm3Serializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new OpenQasm3Serializer();
    }

    public function test_outputs_openqasm_header(): void
    {
        $circuit = Circuit::new(1);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('OPENQASM 3.0;', $qasm);
        $this->assertStringContainsString('include "stdgates.inc";', $qasm);
    }

    public function test_declares_qubit_register(): void
    {
        $circuit = Circuit::new(3);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('qubit[3] q;', $qasm);
    }

    public function test_declares_classical_register_when_cbits_present(): void
    {
        $circuit = Circuit::new(2, 2);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('bit[2] c;', $qasm);
    }

    public function test_no_classical_register_when_no_cbits(): void
    {
        $circuit = Circuit::new(2, 0);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringNotContainsString('bit[', $qasm);
    }

    public function test_serializes_hadamard_gate(): void
    {
        $circuit = Circuit::new(2)->h(0);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('h q[0];', $qasm);
    }

    public function test_serializes_cx_gate(): void
    {
        $circuit = Circuit::new(2)->cx(0, 1);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('cx q[0], q[1];', $qasm);
    }

    public function test_serializes_rotation_gate_with_params(): void
    {
        $circuit = Circuit::new(1)->rx(1.5707963268, 0);
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('rx(', $qasm);
        $this->assertStringContainsString('q[0];', $qasm);
    }

    public function test_serializes_measurement(): void
    {
        $circuit = Circuit::new(1, 1)->measure();
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('c[0] = measure q[0];', $qasm);
    }

    public function test_bell_state_circuit(): void
    {
        $circuit = Circuit::new(2, 2)->h(0)->cx(0, 1)->measure();
        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('h q[0];', $qasm);
        $this->assertStringContainsString('cx q[0], q[1];', $qasm);
        $this->assertStringContainsString('c[0] = measure q[0];', $qasm);
        $this->assertStringContainsString('c[1] = measure q[1];', $qasm);
    }

    public function test_resolves_bound_parameters(): void
    {
        $circuit = Circuit::new(1)
            ->withParameters(['theta'])
            ->rx('theta', 0)
            ->bind(['theta' => 1.5707963268]);

        $qasm = $this->serializer->serialize($circuit);

        $this->assertStringContainsString('rx(', $qasm);
        $this->assertStringNotContainsString('theta', $qasm);
    }
}
