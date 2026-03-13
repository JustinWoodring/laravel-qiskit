<?php

/**
 * Backend Discovery Example
 * -------------------------
 * Demonstrates listing, filtering, and inspecting IBM Quantum backends.
 *
 * Run: php examples/backend_discovery.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use JustinWoodring\LaravelQiskit\Facades\Qiskit;

// ------------------------------------------------------------------
// All backends
// ------------------------------------------------------------------

$all = Qiskit::backends()->all();

echo "All backends (" . count($all) . " total):\n";
echo "───────────────────────────────────────\n";
foreach ($all as $b) {
    $type = $b->isSimulator ? '[sim]' : '[hw] ';
    $status = $b->isOnline() ? '✓' : '✗';
    printf("  %s %-30s %s  %3d qubits  queue: %d\n",
        $status, $b->name, $type, $b->qubits, $b->queueDepth);
}

// ------------------------------------------------------------------
// Filtering: online real hardware with manageable queue
// ------------------------------------------------------------------

echo "\nOnline hardware backends (≥ 100 qubits, queue ≤ 30):\n";
echo "───────────────────────────────────────────────────\n";

$suitable = Qiskit::backends()
    ->filter()
    ->online()
    ->simulator(false)
    ->withMinQubits(100)
    ->withMaxQueueDepth(30)
    ->get();

if (empty($suitable)) {
    echo "  (none right now — try relaxing the filters)\n";
} else {
    foreach ($suitable as $b) {
        printf("  ✓ %-30s %3d qubits  queue: %d\n", $b->name, $b->qubits, $b->queueDepth);
    }
}

// ------------------------------------------------------------------
// Pick the best available backend (lowest queue depth)
// ------------------------------------------------------------------

$best = collect($suitable)->sortBy('queueDepth')->first();

if ($best) {
    echo "\nBest available backend right now: {$best->name} (queue: {$best->queueDepth})\n";

    // Fetch live status
    $status = Qiskit::backends()->status($best->id);
    echo "Live status: " . json_encode($status->toArray(), JSON_PRETTY_PRINT) . "\n";
}
