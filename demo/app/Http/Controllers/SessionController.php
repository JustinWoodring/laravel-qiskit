<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class SessionController extends Controller
{
    /**
     * Demonstrate submitting multiple jobs within a single IBM Quantum session.
     *
     * Sessions keep a backend reserved for your jobs, reducing queue wait
     * times when running variational algorithms with many iterations.
     */
    public function demo(): View
    {
        $jobs = [];

        Qiskit::sessions()->run(config('qiskit.default_backend'), function (string $sessionId) use (&$jobs) {
            // All jobs submitted inside this closure share the same session,
            // so they run on the same backend without re-queuing.

            // Job 1: Bell state
            $jobs[] = Qiskit::sampler()
                ->addPub(
                    Circuit::new(2, 2)->h(0)->cx(0, 1)->measure(),
                    shots: 1024
                )
                ->inSession($sessionId)
                ->dispatch()
                ->dispatch();

            // Job 2: Parameterized sweep — bind multiple angles
            $ansatz = Circuit::new(1)->withParameters(['theta'])->ry('theta', 0)->measure();

            foreach ([0, M_PI / 4, M_PI / 2, M_PI] as $theta) {
                $jobs[] = Qiskit::sampler()
                    ->addPub($ansatz->bind(['theta' => $theta]), shots: 512)
                    ->inSession($sessionId)
                    ->dispatch()
                    ->dispatch();
            }

            // Job 3: Estimator in the same session
            $jobs[] = Qiskit::estimator()
                ->addPub(
                    Circuit::new(2)->h(0)->cx(0, 1),
                    observables: ['ZZ', 'ZI', 'IZ']
                )
                ->inSession($sessionId)
                ->dispatch()
                ->dispatch();
        });

        // Collect the created QuantumJob models for display
        $quantumJobs = QuantumJob::whereIn('id', collect($jobs)->pluck('id'))->get();

        return view('sessions.demo', compact('quantumJobs'));
    }
}
