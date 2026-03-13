<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;

class BackendController extends Controller
{
    /**
     * List all available backends, with optional filtering.
     */
    public function index(): View
    {
        // All backends from IBM Quantum
        $all = Qiskit::backends()->all();

        // Only online backends with at least 100 qubits, real hardware only
        $recommended = Qiskit::backends()
            ->filter()
            ->online()
            ->withMinQubits(100)
            ->simulator(false)
            ->withMaxQueueDepth(50)
            ->get();

        // Simulators
        $simulators = Qiskit::backends()
            ->filter()
            ->simulator()
            ->get();

        return view('backends.index', compact('all', 'recommended', 'simulators'));
    }
}
