<?php

namespace JustinWoodring\LaravelQiskit\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;
use JustinWoodring\LaravelQiskit\Events\QuantumJobFailed;
use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;
use JustinWoodring\LaravelQiskit\Jobs\DispatchQuantumJob;
use JustinWoodring\LaravelQiskit\Jobs\PollQuantumJobStatus;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\Sampler;
use JustinWoodring\LaravelQiskit\Tests\TestCase;

class JobDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
        // Http::fake() is intentionally NOT called here — each test that needs
        // HTTP faking defines its own to avoid merging conflicts.
    }

    private function fakeIam(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
        ]);
    }

    public function test_dispatch_creates_quantum_job_model(): void
    {
        $model = Sampler::on('ibm_test')
            ->addPub(Circuit::new(2)->h(0)->cx(0, 1)->measure())
            ->dispatch()  // Sampler::dispatch() → PendingJob
            ->dispatch(); // PendingJob::dispatch() → QuantumJob

        $this->assertInstanceOf(QuantumJob::class, $model);
        $this->assertEquals('pending', $model->status);
        $this->assertEquals('ibm_test', $model->backend);
        $this->assertEquals('sampler', $model->primitive_type);
    }

    public function test_dispatch_enqueues_dispatch_job(): void
    {
        Sampler::on('ibm_test')
            ->addPub(Circuit::new(1)->h(0))
            ->dispatch()  // Sampler::dispatch() → PendingJob
            ->dispatch(); // PendingJob::dispatch() → enqueues DispatchQuantumJob

        Queue::assertPushed(DispatchQuantumJob::class);
    }

    public function test_dispatch_quantum_job_updates_model_and_fires_event(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'fake.quantum-computing.cloud.ibm.com/v1/jobs' => Http::response([
                'id' => 'ibm-job-abc123',
            ], 200),
        ]);

        $model = QuantumJob::create([
            'backend' => 'ibm_test',
            'primitive_type' => 'sampler',
            'status' => QuantumJob::STATUS_PENDING,
            'payload' => ['program_id' => 'sampler', 'backend' => 'ibm_test', 'params' => ['pubs' => []]],
            'poll_count' => 0,
        ]);

        $job = new DispatchQuantumJob($model->payload, $model->id);
        $job->handle(app(\JustinWoodring\LaravelQiskit\Client\QiskitClient::class));

        $model->refresh();

        $this->assertEquals('ibm-job-abc123', $model->ibm_job_id);
        $this->assertEquals(QuantumJob::STATUS_QUEUED, $model->status);

        Event::assertDispatched(QuantumJobSubmitted::class);
    }

    public function test_poll_job_handles_completed_status(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response(['access_token' => 'token', 'expires_in' => 3600]),
            'fake.quantum-computing.cloud.ibm.com/v1/jobs/ibm-job-abc123' => Http::response([
                'state' => ['status' => 'Completed'],
            ], 200),
            'fake.quantum-computing.cloud.ibm.com/v1/jobs/ibm-job-abc123/results' => Http::response([
                'results' => [['data' => ['counts' => ['00' => 512, '11' => 512]]]],
            ], 200),
        ]);

        $model = QuantumJob::create([
            'ibm_job_id' => 'ibm-job-abc123',
            'backend' => 'ibm_test',
            'primitive_type' => 'sampler',
            'status' => QuantumJob::STATUS_RUNNING,
            'payload' => [],
            'poll_count' => 0,
        ]);

        $pollJob = new PollQuantumJobStatus('ibm-job-abc123', $model->id, 0);
        $pollJob->handle(app(\JustinWoodring\LaravelQiskit\Client\QiskitClient::class));

        $model->refresh();

        $this->assertEquals(QuantumJob::STATUS_COMPLETED, $model->status);
        Event::assertDispatched(QuantumJobCompleted::class);
    }

    public function test_poll_job_handles_failed_status(): void
    {
        Http::fake([
            'fake.iam.cloud.ibm.com/*' => Http::response(['access_token' => 'token', 'expires_in' => 3600]),
            'fake.quantum-computing.cloud.ibm.com/v1/jobs/ibm-job-fail' => Http::response([
                'state' => ['status' => 'Failed'],
            ], 200),
        ]);

        $model = QuantumJob::create([
            'ibm_job_id' => 'ibm-job-fail',
            'backend' => 'ibm_test',
            'primitive_type' => 'sampler',
            'status' => QuantumJob::STATUS_RUNNING,
            'payload' => [],
            'poll_count' => 0,
        ]);

        $pollJob = new PollQuantumJobStatus('ibm-job-fail', $model->id, 0);
        $pollJob->handle(app(\JustinWoodring\LaravelQiskit\Client\QiskitClient::class));

        $model->refresh();

        $this->assertEquals(QuantumJob::STATUS_FAILED, $model->status);
        Event::assertDispatched(QuantumJobFailed::class);
    }

    public function test_poll_job_times_out_at_max_attempts(): void
    {
        $model = QuantumJob::create([
            'ibm_job_id' => 'ibm-job-timeout',
            'backend' => 'ibm_test',
            'primitive_type' => 'sampler',
            'status' => QuantumJob::STATUS_RUNNING,
            'payload' => [],
            'poll_count' => 0,
        ]);

        $maxAttempts = config('qiskit.polling.max_attempts', 360);
        $pollJob = new PollQuantumJobStatus('ibm-job-timeout', $model->id, $maxAttempts);
        $pollJob->handle(app(\JustinWoodring\LaravelQiskit\Client\QiskitClient::class));

        $model->refresh();

        $this->assertEquals(QuantumJob::STATUS_TIMED_OUT, $model->status);
    }
}
