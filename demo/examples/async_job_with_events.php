<?php

/**
 * Async Job + Event Listener Example
 * ------------------------------------
 * Shows the recommended production pattern:
 *   1. Submit job to the queue (returns immediately)
 *   2. Queue worker runs DispatchQuantumJob → fires QuantumJobSubmitted
 *   3. StartJobPolling listener begins polling IBM Quantum
 *   4. PollQuantumJobStatus fires QuantumJobCompleted when done
 *   5. Your listener receives the result
 *
 * This script just demonstrates submission. The queue worker handles the rest.
 *
 * Run the queue worker alongside this script:
 *   php artisan queue:work
 *
 * Run: php examples/async_job_with_events.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

// ------------------------------------------------------------------
// Build circuits
// ------------------------------------------------------------------

$bellState = Circuit::new(2, 2)
    ->h(0)
    ->cx(0, 1)
    ->measure();

$ghz = Circuit::new(3, 3)
    ->h(0)
    ->cx(0, 1)
    ->cx(1, 2)
    ->measure();

// ------------------------------------------------------------------
// Submit — dispatch() enqueues DispatchQuantumJob and returns the
// QuantumJob model immediately. No waiting for IBM Quantum.
// ------------------------------------------------------------------

echo "Submitting Bell state job...\n";
$bellJob = Qiskit::sampler()
    ->addPub($bellState, shots: 4096)
    ->dispatch()
    ->dispatch();

echo "  → Local Job ID: {$bellJob->id}  Status: {$bellJob->status}\n";

echo "Submitting GHZ state job...\n";
$ghzJob = Qiskit::sampler()
    ->addPub($ghz, shots: 8192)
    ->dispatch()
    ->dispatch();

echo "  → Local Job ID: {$ghzJob->id}  Status: {$ghzJob->status}\n";

// ------------------------------------------------------------------
// Poll for completion (just for this demo — in production your
// EventServiceProvider listener handles this automatically)
// ------------------------------------------------------------------

echo "\nWaiting for jobs to complete (run `php artisan queue:work` in another terminal)...\n";
echo "Checking every 5 seconds. Press Ctrl+C to stop.\n\n";

$jobIds = [$bellJob->id, $ghzJob->id];

while (true) {
    $jobs = QuantumJob::whereIn('id', $jobIds)->get()->keyBy('id');

    foreach ($jobIds as $id) {
        $job = $jobs[$id];
        printf("  Job #%-5d  %-12s  IBM: %s\n",
            $id,
            $job->status,
            $job->ibm_job_id ?? 'pending submission'
        );
    }

    $allDone = $jobs->every(fn ($j) => $j->isTerminal());

    if ($allDone) {
        echo "\nAll jobs finished!\n\n";

        foreach ($jobs as $job) {
            if ($job->status === 'completed' && $job->result) {
                echo "Job #{$job->id} counts:\n";
                foreach ($job->result->getCounts() as $bits => $count) {
                    $total = array_sum($job->result->getCounts());
                    printf("  |%s⟩  %d  (%.1f%%)\n", $bits, $count, $count / $total * 100);
                }
                echo "\n";
            }
        }

        break;
    }

    sleep(5);
    echo str_repeat('─', 50) . "\n";
}
