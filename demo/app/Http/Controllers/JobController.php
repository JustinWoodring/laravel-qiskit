<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class JobController extends Controller
{
    /**
     * List all quantum jobs from the database.
     */
    public function index(): View
    {
        $jobs = QuantumJob::query()
            ->latest()
            ->paginate(20);

        return view('jobs.index', compact('jobs'));
    }

    /**
     * Show a single job's status and results.
     */
    public function show(int $id): View
    {
        $job = QuantumJob::findOrFail($id);

        return view('jobs.show', compact('job'));
    }

    /**
     * Submit a Bell state circuit (maximally entangled 2-qubit state).
     *
     * Circuit:
     *   H  ──●──  measure
     *        │
     *   I  ──X──  measure
     *
     * Expected result: ~50% |00⟩, ~50% |11⟩
     */
    public function submitBellState(): RedirectResponse
    {
        $circuit = Circuit::new(2, 2)
            ->h(0)
            ->cx(0, 1)
            ->measure();

        $job = Qiskit::sampler()
            ->addPub($circuit, shots: 4096)
            ->dispatch()
            ->dispatch();

        return redirect()->route('jobs.show', $job->id)
            ->with('success', 'Bell state job submitted! Job ID: ' . $job->id);
    }

    /**
     * Submit a 3-qubit GHZ state circuit.
     *
     * Circuit:
     *   H  ──●──────  measure
     *        │
     *   I  ──X──●──  measure
     *            │
     *   I  ──────X──  measure
     *
     * Expected result: ~50% |000⟩, ~50% |111⟩
     */
    public function submitGhz(): RedirectResponse
    {
        $circuit = Circuit::new(3, 3)
            ->h(0)
            ->cx(0, 1)
            ->cx(1, 2)
            ->measure();

        $job = Qiskit::sampler()
            ->addPub($circuit, shots: 8192)
            ->dispatch()
            ->dispatch();

        return redirect()->route('jobs.show', $job->id)
            ->with('success', '3-qubit GHZ state job submitted! Job ID: ' . $job->id);
    }

    /**
     * Submit a VQE-style estimator job measuring expectation values.
     *
     * Uses a parameterized ansatz circuit with RY rotation, binding
     * a specific angle, then estimates ⟨ZZ⟩ + ⟨XI⟩ observables.
     */
    public function submitVqe(): RedirectResponse
    {
        // Parameterized ansatz: RY(θ)|0⟩ entangled via CX
        $ansatz = Circuit::new(2)
            ->withParameters(['theta'])
            ->ry('theta', 0)
            ->cx(0, 1);

        // Bind theta = π/3 (~60 degrees)
        $bound = $ansatz->bind(['theta' => M_PI / 3]);

        $job = Qiskit::estimator()
            ->addPub($bound, observables: ['ZZ', 'XI', 'IZ'])
            ->dispatch()
            ->dispatch();

        return redirect()->route('jobs.show', $job->id)
            ->with('success', 'VQE estimator job submitted! Job ID: ' . $job->id);
    }

    /**
     * Cancel a job.
     */
    public function cancel(int $id): RedirectResponse
    {
        $job = QuantumJob::findOrFail($id);

        if ($job->isTerminal()) {
            return back()->with('error', "Job #{$id} is already {$job->status}.");
        }

        // Cancel on IBM Quantum
        if ($job->ibm_job_id) {
            Qiskit::client()->cancelJob($job->ibm_job_id);
        }

        $job->cancel();

        return redirect()->route('jobs.index')
            ->with('success', "Job #{$id} cancelled.");
    }
}
