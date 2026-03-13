<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs — Laravel Qiskit Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-6xl mx-auto py-12 px-4">

    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">← Home</a>
            <h1 class="text-3xl font-bold mt-1">Quantum Jobs</h1>
        </div>
        <div class="space-x-2">
            <a href="{{ route('submit.bell') }}" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                + Bell State
            </a>
            <a href="{{ route('submit.vqe') }}" class="bg-purple-600 text-white text-sm px-4 py-2 rounded hover:bg-purple-700">
                + VQE
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-6">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">IBM Job ID</th>
                    <th class="px-4 py-3 text-left">Backend</th>
                    <th class="px-4 py-3 text-left">Primitive</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Submitted</th>
                    <th class="px-4 py-3 text-left"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($jobs as $job)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono">{{ $job->id }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">
                        {{ $job->ibm_job_id ?? '—' }}
                    </td>
                    <td class="px-4 py-3">{{ $job->backend }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $job->primitive_type === 'sampler' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $job->primitive_type }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $colors = [
                                'pending'   => 'bg-gray-100 text-gray-600',
                                'queued'    => 'bg-yellow-100 text-yellow-700',
                                'running'   => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'failed'    => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-200 text-gray-500',
                                'timed_out' => 'bg-orange-100 text-orange-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $colors[$job->status] ?? '' }}">
                            {{ $job->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $job->submitted_at?->diffForHumans() ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('jobs.show', $job->id) }}" class="text-blue-600 hover:underline text-xs">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No jobs yet. <a href="{{ route('submit.bell') }}" class="text-blue-600 hover:underline">Submit one!</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $jobs->links() }}</div>

</div>

</body>
</html>
