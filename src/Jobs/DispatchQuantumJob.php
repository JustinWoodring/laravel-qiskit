<?php

namespace JustinWoodring\LaravelQiskit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Events\QuantumJobFailed;
use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;
use JustinWoodring\LaravelQiskit\Exceptions\JobSubmissionException;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class DispatchQuantumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly array $payload,
        private readonly int $quantumJobModelId,
    ) {}

    public function handle(QiskitClient $client): void
    {
        $model = QuantumJob::findOrFail($this->quantumJobModelId);

        $response = $client->submitJob($this->payload);

        if ($response->failed()) {
            throw new JobSubmissionException(
                'Failed to submit job to IBM Quantum: ' . json_encode($response->toArray())
            );
        }

        $ibmJobId = $response->get('id');

        $model->update([
            'ibm_job_id' => $ibmJobId,
            'status' => QuantumJob::STATUS_QUEUED,
            'submitted_at' => now(),
        ]);

        event(new QuantumJobSubmitted($model->fresh(), $ibmJobId));
    }

    public function failed(\Throwable $exception): void
    {
        $model = QuantumJob::find($this->quantumJobModelId);

        if ($model) {
            $model->update([
                'status' => QuantumJob::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);

            event(new QuantumJobFailed($model, $exception->getMessage()));
        }
    }
}
