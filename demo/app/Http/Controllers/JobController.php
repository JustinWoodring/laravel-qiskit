<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class JobController extends Controller
{
    public function index()
    {
        $jobs = QuantumJob::latest()->paginate(20);

        return view('jobs.index', compact('jobs'));
    }

    public function show(QuantumJob $job)
    {
        return view('jobs.show', compact('job'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'circuit'  => 'required|in:bell,ghz,random',
            'shots'    => 'integer|min:1|max:20000',
            'backend'  => 'nullable|string',
        ]);

        $backend = $request->input('backend', config('qiskit.default_backend'));
        $shots   = (int) $request->input('shots', 1024);

        $circuit = match ($request->input('circuit')) {
            'bell' => Circuit::new(2, 2)->h(0)->cx(0, 1)->measure(),
            'ghz'  => Circuit::new(3, 3)->h(0)->cx(0, 1)->cx(1, 2)->measure(),
            default => Circuit::new(2, 2)->h(0)->h(1)->measure(),
        };

        $job = Qiskit::sampler($backend)
            ->addPub($circuit, shots: $shots)
            ->dispatch()
            ->dispatch();

        return redirect()->route('jobs.show', $job)
            ->with('success', "Job #{$job->id} submitted to {$backend}.");
    }

    public function cancel(QuantumJob $job)
    {
        $job->cancel();

        return back()->with('success', "Job #{$job->id} cancelled.");
    }
}
