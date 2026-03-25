<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;

class SessionController extends Controller
{
    public function demo(Request $request)
    {
        $request->validate([
            'backend' => 'nullable|string',
        ]);

        $backend = $request->input('backend', config('qiskit.default_backend'));
        $jobs    = [];

        Qiskit::sessions()->run($backend, function (string $sessionId) use ($backend, &$jobs) {
            $circuit1 = Circuit::new(2, 2)->h(0)->cx(0, 1)->measure();
            $circuit2 = Circuit::new(2, 2)->h(0)->h(1)->measure();

            $job1 = Qiskit::sampler($backend)
                ->addPub($circuit1, shots: 512)
                ->inSession($sessionId)
                ->dispatch()
                ->dispatch();

            $job2 = Qiskit::sampler($backend)
                ->addPub($circuit2, shots: 512)
                ->inSession($sessionId)
                ->dispatch()
                ->dispatch();

            $jobs = [$job1, $job2];
        });

        return redirect()->route('jobs.index')
            ->with('success', sprintf(
                'Session demo submitted %d jobs to %s.',
                count($jobs),
                $backend
            ));
    }
}
