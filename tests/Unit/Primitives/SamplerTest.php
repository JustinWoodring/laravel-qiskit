<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Primitives;

use Illuminate\Support\Facades\Queue;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Primitives\Sampler;
use JustinWoodring\LaravelQiskit\Support\PendingJob;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class SamplerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_creates_sampler_on_backend(): void
    {
        $sampler = Sampler::on('ibm_brisbane');

        $payload = $sampler->toPayload();

        $this->assertEquals('sampler', $payload['program_id']);
        $this->assertEquals('ibm_brisbane', $payload['backend']);
    }

    public function test_add_pub_with_circuit(): void
    {
        $circuit = Circuit::new(2)->h(0)->cx(0, 1)->measure();

        $sampler = Sampler::on('ibm_brisbane')->addPub($circuit, [], 2048);

        $payload = $sampler->toPayload();

        $this->assertCount(1, $payload['params']['pubs']);
        $this->assertEquals(2048, $payload['params']['pubs'][0]['shots']);
        $this->assertStringContainsString('OPENQASM', $payload['params']['pubs'][0]['circuit']);
    }

    public function test_add_pub_with_raw_qasm(): void
    {
        $qasm = 'OPENQASM 3.0; qubit[1] q; h q[0];';

        $sampler = Sampler::on('ibm_brisbane')->addPub($qasm);

        $payload = $sampler->toPayload();

        $this->assertEquals($qasm, $payload['params']['pubs'][0]['circuit']);
    }

    public function test_add_pub_with_param_values(): void
    {
        $sampler = Sampler::on('ibm_brisbane')
            ->addPub('OPENQASM 3.0; qubit[1] q;', ['theta' => 1.57]);

        $payload = $sampler->toPayload();

        $this->assertEquals(['theta' => 1.57], $payload['params']['pubs'][0]['parameter_values']);
    }

    public function test_in_session_adds_session_id(): void
    {
        $sampler = Sampler::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->h(0))
            ->inSession('session-abc');

        $payload = $sampler->toPayload();

        $this->assertEquals('session-abc', $payload['session_id']);
    }

    public function test_dispatch_returns_pending_job(): void
    {
        $sampler = Sampler::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->h(0));

        // Set as dispatched to avoid __destruct side effects
        $pending = $sampler->dispatch();

        $this->assertInstanceOf(PendingJob::class, $pending);
    }

    public function test_multiple_pubs(): void
    {
        $sampler = Sampler::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->h(0))
            ->addPub(Circuit::new(2)->cx(0, 1));

        $payload = $sampler->toPayload();

        $this->assertCount(2, $payload['params']['pubs']);
    }
}
