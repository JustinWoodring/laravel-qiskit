<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Demo — Laravel Qiskit Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-4xl mx-auto py-12 px-4">

    <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">← Home</a>
    <h1 class="text-3xl font-bold mt-2 mb-2">Session Demo</h1>
    <p class="text-gray-500 mb-8">
        The following jobs were submitted within a single IBM Quantum session,
        keeping the backend reserved between jobs to reduce queue wait time.
    </p>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Primitive</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">IBM Session ID</th>
                    <th class="px-4 py-3 text-left"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($quantumJobs as $job)
                <tr>
                    <td class="px-4 py-3 font-mono">{{ $job->id }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $job->primitive_type === 'sampler' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $job->primitive_type }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $job->status }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-400">
                        {{ $job->ibm_session_id ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('jobs.show', $job->id) }}" class="text-blue-600 hover:underline text-xs">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No jobs submitted.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 bg-gray-900 text-green-300 rounded-xl p-6 text-sm overflow-x-auto">
        <p class="text-gray-400 mb-2">// Code that generated these jobs:</p>
        <pre>Qiskit::sessions()->run('{{ config('qiskit.default_backend') }}', function (string $sessionId) {
    // Bell state
    Qiskit::sampler()
        ->addPub(Circuit::new(2, 2)->h(0)->cx(0, 1)->measure(), shots: 1024)
        ->inSession($sessionId)
        ->dispatch()->dispatch();

    // Parameterized sweep (4 angles)
    $ansatz = Circuit::new(1)->withParameters(['theta'])->ry('theta', 0)->measure();

    foreach ([0, M_PI/4, M_PI/2, M_PI] as $theta) {
        Qiskit::sampler()
            ->addPub($ansatz->bind(['theta' => $theta]), shots: 512)
            ->inSession($sessionId)
            ->dispatch()->dispatch();
    }

    // Estimator
    Qiskit::estimator()
        ->addPub(Circuit::new(2)->h(0)->cx(0, 1), observables: ['ZZ', 'ZI', 'IZ'])
        ->inSession($sessionId)
        ->dispatch()->dispatch();
});
// Session auto-closed after callback returns</pre>
    </div>

</div>

</body>
</html>
