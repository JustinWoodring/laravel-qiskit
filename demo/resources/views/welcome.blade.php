<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel Qiskit Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-4xl mx-auto py-16 px-4">

    <h1 class="text-4xl font-bold mb-2">⚛ Laravel Qiskit Demo</h1>
    <p class="text-gray-500 mb-10">
        Demonstrates <code class="bg-gray-100 px-1 rounded">justinwoodring/laravel-qiskit</code>
        — a Laravel package for IBM Quantum / Qiskit Runtime.
    </p>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Submit Jobs --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Submit Jobs</h2>
            <div class="space-y-3">
                <a href="{{ route('submit.bell') }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                    Bell State (Sampler)
                </a>
                <a href="{{ route('submit.ghz') }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                    3-Qubit GHZ State (Sampler)
                </a>
                <a href="{{ route('submit.vqe') }}"
                   class="block w-full text-center bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
                    VQE Ansatz (Estimator)
                </a>
            </div>
        </div>

        {{-- Sessions --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Sessions</h2>
            <p class="text-sm text-gray-500 mb-4">
                Submit a batch of jobs within a single reserved IBM Quantum session
                to minimize queue wait time between iterations.
            </p>
            <a href="{{ route('sessions.demo') }}"
               class="block w-full text-center bg-emerald-600 text-white py-2 px-4 rounded hover:bg-emerald-700">
                Run Session Demo
            </a>
        </div>

        {{-- Job History --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Job History</h2>
            <p class="text-sm text-gray-500 mb-4">
                View all submitted jobs, their statuses, and results.
            </p>
            <a href="{{ route('jobs.index') }}"
               class="block w-full text-center bg-gray-700 text-white py-2 px-4 rounded hover:bg-gray-800">
                View Jobs
            </a>
        </div>

        {{-- Backends --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Backends</h2>
            <p class="text-sm text-gray-500 mb-4">
                Browse available IBM Quantum backends and filter by qubit count,
                queue depth, and availability.
            </p>
            <a href="{{ route('backends.index') }}"
               class="block w-full text-center bg-gray-700 text-white py-2 px-4 rounded hover:bg-gray-800">
                Browse Backends
            </a>
        </div>

    </div>

    <div class="mt-12 bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Quick Start (from code)</h2>
        <pre class="bg-gray-900 text-green-300 rounded-lg p-4 text-sm overflow-x-auto"><code>use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;

// Build a Bell state circuit
$circuit = Circuit::new(2, 2)
    ->h(0)
    ->cx(0, 1)
    ->measure();

// Submit as a Sampler job (async via queue)
$job = Qiskit::sampler('ibm_brisbane')
    ->addPub($circuit, shots: 4096)
    ->dispatch()   // returns PendingJob
    ->dispatch();  // enqueues + returns QuantumJob model

// Listen for results in your EventServiceProvider:
// QuantumJobCompleted::class => [YourListener::class]

// $event->result->getCounts()
// => ['00' => 2048, '11' => 2048]
</code></pre>
    </div>

</div>

</body>
</html>
