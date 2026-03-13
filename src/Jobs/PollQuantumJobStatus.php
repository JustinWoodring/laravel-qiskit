<?php

namespace JustinWoodring\LaravelQiskit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;
use JustinWoodring\LaravelQiskit\Events\QuantumJobFailed;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\PrimitiveResult;

class PollQuantumJobStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $ibmJobId,
        private readonly int $quantumJobModelId,
        private readonly int $pollCount = 0,
    ) {}

    public function handle(QiskitClient $client): void
    {
        $model = QuantumJob::findOrFail($this->quantumJobModelId);

        $maxAttempts = config('qiskit.polling.max_attempts', 360);

        if ($this->pollCount >= $maxAttempts) {
            $model->update(['status' => QuantumJob::STATUS_TIMED_OUT]);

            event(new QuantumJobFailed($model, 'Job polling timed out after ' . $maxAttempts . ' attempts.'));

            return;
        }

        $response = $client->getJob($this->ibmJobId);
        $state = $response->get('state', []);
        $ibmStatus = $state['status'] ?? 'running';

        $model->increment('poll_count');

        match ($ibmStatus) {
            'Queued', 'Running' => $this->requeue($model, $ibmStatus),
            'Completed' => $this->handleCompleted($client, $model),
            'Failed', 'Cancelled' => $this->handleFailed($model, $ibmStatus),
            default => $this->requeue($model, $ibmStatus),
        };
    }

    private function requeue(QuantumJob $model, string $ibmStatus): void
    {
        $status = strtolower($ibmStatus) === 'queued'
            ? QuantumJob::STATUS_QUEUED
            : QuantumJob::STATUS_RUNNING;

        $model->update(['status' => $status]);

        $interval = config('qiskit.polling.interval', 10);
        $queue = config('qiskit.polling.queue');

        $job = self::dispatch($this->ibmJobId, $this->quantumJobModelId, $this->pollCount + 1)
            ->delay(now()->addSeconds($interval));

        if ($queue) {
            $job->onQueue($queue);
        }
    }

    private function handleCompleted(QiskitClient $client, QuantumJob $model): void
    {
        $resultsResponse = $client->getJobResults($this->ibmJobId);
        $result = PrimitiveResult::fromArray($resultsResponse->toArray());

        $model->update([
            'status' => QuantumJob::STATUS_COMPLETED,
            'result' => $result->getRaw(),
            'completed_at' => now(),
        ]);

        event(new QuantumJobCompleted($model->fresh(), $result));
    }

    private function handleFailed(QuantumJob $model, string $ibmStatus): void
    {
        $status = strtolower($ibmStatus) === 'cancelled'
            ? QuantumJob::STATUS_CANCELLED
            : QuantumJob::STATUS_FAILED;

        $model->update([
            'status' => $status,
            'error_message' => "IBM Quantum job ended with status: {$ibmStatus}",
        ]);

        event(new QuantumJobFailed($model, "IBM Quantum job ended with status: {$ibmStatus}"));
    }
}
