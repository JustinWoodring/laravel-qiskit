<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;
use JustinWoodring\LaravelQiskit\Events\QuantumJobFailed;
use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;

class HandleQuantumResults
{
    /**
     * Log when a job has been submitted to IBM Quantum.
     */
    public function handleSubmitted(QuantumJobSubmitted $event): void
    {
        Log::info('Quantum job submitted', [
            'local_id' => $event->job->id,
            'ibm_job_id' => $event->ibmJobId,
            'backend' => $event->job->backend,
        ]);
    }

    /**
     * Process results when a job completes.
     *
     * For a Sampler job, $event->result->getCounts() returns bitstring
     * frequencies, e.g. ['00' => 2048, '11' => 2048].
     *
     * For an Estimator job, $event->result->getExpectationValues() returns
     * expectation values per observable, e.g. [0.98, -0.02, 0.01].
     */
    public function handleCompleted(QuantumJobCompleted $event): void
    {
        $job = $event->job;
        $result = $event->result;

        Log::info('Quantum job completed', [
            'local_id' => $job->id,
            'ibm_job_id' => $job->ibm_job_id,
            'backend' => $job->backend,
            'primitive' => $job->primitive_type,
        ]);

        if ($job->primitive_type === 'sampler') {
            $counts = $result->getCounts();
            $total = array_sum($counts);

            Log::info('Sampler counts', [
                'counts' => $counts,
                'total_shots' => $total,
                'probabilities' => array_map(fn ($c) => round($c / $total, 4), $counts),
            ]);
        }

        if ($job->primitive_type === 'estimator') {
            Log::info('Estimator expectation values', [
                'evs' => $result->getExpectationValues(),
            ]);
        }

        // You could also notify users, update dashboards, trigger follow-up
        // computations, etc. here.
    }

    /**
     * Handle job failure — log the reason and optionally alert.
     */
    public function handleFailed(QuantumJobFailed $event): void
    {
        Log::error('Quantum job failed', [
            'local_id' => $event->job->id,
            'ibm_job_id' => $event->job->ibm_job_id,
            'reason' => $event->reason,
        ]);
    }
}
