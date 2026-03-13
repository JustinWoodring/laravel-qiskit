<?php

/**
 * VQE-style Estimator Example
 * ---------------------------
 * Sweeps a rotation angle θ from 0 to π and measures the expectation
 * value ⟨ZZ⟩ of a parameterized 2-qubit ansatz. Useful as a building
 * block for Variational Quantum Eigensolver (VQE) algorithms.
 *
 * Run: php examples/vqe_estimator.php
 */

require __DIR__ . '/../vendor/autoload.php';

use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Primitives\Estimator;

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ------------------------------------------------------------------
// Parameterized ansatz:
//
//   q[0]: ─ RY(θ) ─●─
//                   │
//   q[1]: ──────────X─
//
// We bind different values of θ to sweep the energy landscape.
// ------------------------------------------------------------------

$ansatz = Circuit::new(2)
    ->withParameters(['theta'])
    ->ry('theta', 0)
    ->cx(0, 1);

echo "Ansatz circuit:\n";
echo "───────────────\n";
echo $ansatz->toQasm() . "\n\n";

// ------------------------------------------------------------------
// Sweep θ from 0 to π in 8 steps, measuring ⟨ZZ⟩ at each point
// ------------------------------------------------------------------

$steps = 8;
$angles = array_map(fn ($i) => M_PI * $i / ($steps - 1), range(0, $steps - 1));

echo "Sweeping θ from 0 to π, measuring ⟨ZZ⟩ + ⟨XI⟩...\n\n";

$estimator = Estimator::on(config('qiskit.default_backend'));

foreach ($angles as $theta) {
    $estimator->addPub(
        circuit: $ansatz->bind(['theta' => $theta]),
        observables: ['ZZ', 'XI'],
    );
}

$result = $estimator->dispatchSync();

// ------------------------------------------------------------------
// Display results table
// ------------------------------------------------------------------

printf("  %-8s  %-12s  %-12s\n", 'θ (rad)', '⟨ZZ⟩', '⟨XI⟩');
echo str_repeat('─', 38) . "\n";

foreach ($result->getPubResults() as $i => $pub) {
    $theta = $angles[$i];
    $evs = $pub['data']['evs'] ?? [null, null];

    printf(
        "  %-8s  %-12s  %-12s\n",
        number_format($theta, 4),
        isset($evs[0]) ? number_format($evs[0], 6) : '—',
        isset($evs[1]) ? number_format($evs[1], 6) : '—',
    );
}

echo "\nFor θ=0:  |00⟩ → ⟨ZZ⟩ ≈ +1\n";
echo "For θ=π/2: superposition → ⟨ZZ⟩ ≈ 0\n";
echo "For θ=π:  |11⟩ → ⟨ZZ⟩ ≈ -1\n";
