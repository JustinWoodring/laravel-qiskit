<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;

class HandleQuantumResults
{
    public function handle(QuantumJobCompleted $event): void
    {
        $job    = $event->job;
        $result = $event->result;
        $counts = $result->getCounts();

        Log::info('Quantum job completed', [
            'job_id'     => $job->id,
            'ibm_job_id' => $job->ibm_job_id,
            'backend'    => $job->backend,
            'counts'     => $counts,
        ]);

        // Sort by count descending and log the top outcomes
        arsort($counts);
        $top = array_slice($counts, 0, 5, true);

        foreach ($top as $bitstring => $count) {
            Log::debug("  |{$bitstring}⟩ → {$count}");
        }
    }
}
