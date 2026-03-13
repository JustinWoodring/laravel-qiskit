<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backends — Laravel Qiskit Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-5xl mx-auto py-12 px-4">

    <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">← Home</a>
    <h1 class="text-3xl font-bold mt-2 mb-8">IBM Quantum Backends</h1>

    {{-- Recommended --}}
    <h2 class="text-lg font-semibold mb-3 text-green-700">
        ✓ Recommended (online, ≥100 qubits, ≤50 jobs queued)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        @forelse($recommended as $backend)
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex justify-between items-start">
                <span class="font-semibold">{{ $backend->name }}</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">online</span>
            </div>
            <div class="mt-2 text-sm text-gray-500 space-y-1">
                <div>{{ $backend->qubits }} qubits</div>
                <div>{{ $backend->queueDepth }} jobs queued</div>
            </div>
        </div>
        @empty
        <p class="col-span-3 text-gray-400">None matching filters right now.</p>
        @endforelse
    </div>

    {{-- Simulators --}}
    <h2 class="text-lg font-semibold mb-3 text-purple-700">Simulators</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        @forelse($simulators as $backend)
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex justify-between items-start">
                <span class="font-semibold">{{ $backend->name }}</span>
                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">simulator</span>
            </div>
            <div class="mt-2 text-sm text-gray-500">{{ $backend->qubits }} qubits</div>
        </div>
        @empty
        <p class="col-span-3 text-gray-400">No simulators available.</p>
        @endforelse
    </div>

    {{-- All backends --}}
    <h2 class="text-lg font-semibold mb-3">All Backends</h2>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Qubits</th>
                    <th class="px-4 py-3 text-left">Queue</th>
                    <th class="px-4 py-3 text-left">Type</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($all as $backend)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $backend->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs
                            {{ $backend->isOnline() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $backend->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $backend->qubits }}</td>
                    <td class="px-4 py-3">{{ $backend->queueDepth }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        {{ $backend->isSimulator ? 'simulator' : 'hardware' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
