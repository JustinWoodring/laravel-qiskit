<?php

namespace JustinWoodring\LaravelQiskit\Tests\Unit\Primitives;

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Primitives\Estimator;
use JustinWoodring\LaravelQiskit\Support\PendingJob;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class EstimatorTest extends TestCase
{
    public function test_creates_estimator_on_backend(): void
    {
        $estimator = Estimator::on('ibm_brisbane');

        $payload = $estimator->toPayload();

        $this->assertEquals('estimator', $payload['program_id']);
        $this->assertEquals('ibm_brisbane', $payload['backend']);
    }

    public function test_add_pub_with_string_observable(): void
    {
        $circuit = Circuit::new(2)->h(0)->cx(0, 1);

        $estimator = Estimator::on('ibm_brisbane')
            ->addPub($circuit, 'ZZ');

        $payload = $estimator->toPayload();

        $this->assertCount(1, $payload['params']['pubs']);
        $this->assertEquals(['ZZ'], $payload['params']['pubs'][0]['observables']);
    }

    public function test_add_pub_with_array_observables(): void
    {
        $circuit = Circuit::new(2)->h(0)->cx(0, 1);

        $estimator = Estimator::on('ibm_brisbane')
            ->addPub($circuit, ['ZZ', 'XI', 'IZ']);

        $payload = $estimator->toPayload();

        $this->assertEquals(['ZZ', 'XI', 'IZ'], $payload['params']['pubs'][0]['observables']);
    }

    public function test_in_session_adds_session_id(): void
    {
        $estimator = Estimator::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->h(0), 'Z')
            ->inSession('session-xyz');

        $payload = $estimator->toPayload();

        $this->assertEquals('session-xyz', $payload['session_id']);
    }

    public function test_dispatch_returns_pending_job(): void
    {
        $estimator = Estimator::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->h(0), 'Z');

        $pending = $estimator->dispatch();

        $this->assertInstanceOf(PendingJob::class, $pending);
    }

    public function test_param_values_included_when_provided(): void
    {
        $estimator = Estimator::on('ibm_brisbane')
            ->addPub(Circuit::new(1)->rx('theta', 0), 'Z', ['theta' => [1.57]]);

        $payload = $estimator->toPayload();

        $this->assertEquals(['theta' => [1.57]], $payload['params']['pubs'][0]['parameter_values']);
    }
}
