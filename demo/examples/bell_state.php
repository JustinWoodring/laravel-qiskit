<?php

/**
 * Bell State Example
 * -----------------
 * Demonstrates submitting a 2-qubit Bell state circuit via the Sampler
 * primitive and waiting synchronously for the result.
 *
 * Run: php examples/bell_state.php
 */

require __DIR__ . '/../vendor/autoload.php';

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Primitives\Sampler;

// Bootstrap a minimal Laravel app (adjust path if needed)
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ------------------------------------------------------------------
// Build the circuit:
//
//   q[0]: ─ H ─●─ M
//               │
//   q[1]: ─────X─ M
//
// Hadamard on q[0] creates superposition, CX entangles it with q[1].
// Measuring should give ~50% |00⟩ and ~50% |11⟩.
// ------------------------------------------------------------------

$circuit = Circuit::new(2, 2)
    ->h(0)
    ->cx(0, 1)
    ->measure();

echo "Circuit (OPENQASM 3.0):\n";
echo "─────────────────────\n";
echo $circuit->toQasm() . "\n\n";

// ------------------------------------------------------------------
// Submit synchronously (blocks until IBM Quantum finishes)
// For production use Sampler::dispatch() with a queue instead.
// ------------------------------------------------------------------

echo "Submitting to IBM Quantum...\n";

$result = Sampler::on(config('qiskit.default_backend'))
    ->addPub($circuit, shots: 4096)
    ->dispatchSync();

// ------------------------------------------------------------------
// Display results
// ------------------------------------------------------------------

$counts = $result->getCounts();
$total = array_sum($counts);

echo "\nMeasurement Results ({$total} shots):\n";
echo "─────────────────────────────────\n";

arsort($counts);
foreach ($counts as $bitstring => $count) {
    $pct = round($count / $total * 100, 1);
    $bar = str_repeat('█', (int) ($pct / 2));
    printf("  |%s⟩  %s %d (%.1f%%)\n", $bitstring, str_pad($bar, 50), $count, $pct);
}

echo "\nExpected: ~50% |00⟩ and ~50% |11⟩ — the Bell state Φ+\n";
