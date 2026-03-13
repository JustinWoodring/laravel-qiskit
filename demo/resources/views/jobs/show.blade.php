<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job #{{ $job->id }} — Laravel Qiskit Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(!$job->isTerminal())
    <meta http-equiv="refresh" content="5">
    @endif
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-4xl mx-auto py-12 px-4">

    <a href="{{ route('jobs.index') }}" class="text-sm text-blue-600 hover:underline">← All Jobs</a>

    <div class="flex items-center justify-between mt-2 mb-8">
        <h1 class="text-3xl font-bold">Job #{{ $job->id }}</h1>

        @if(!$job->isTerminal())
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Auto-refreshing every 5s…
            </div>
        @endif
    </div>

    {{-- Status card --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">IBM Job ID</dt>
                <dd class="font-mono mt-1">{{ $job->ibm_job_id ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Status</dt>
                <dd class="mt-1 font-semibold">{{ strtoupper($job->status) }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Backend</dt>
                <dd class="mt-1">{{ $job->backend }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Primitive</dt>
                <dd class="mt-1">{{ $job->primitive_type }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Submitted</dt>
                <dd class="mt-1">{{ $job->submitted_at?->toDateTimeString() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Completed</dt>
                <dd class="mt-1">{{ $job->completed_at?->toDateTimeString() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-400 uppercase text-xs font-medium">Poll Count</dt>
                <dd class="mt-1">{{ $job->poll_count }}</dd>
            </div>
        </dl>

        @if($job->error_message)
        <div class="mt-4 bg-red-50 text-red-700 px-4 py-3 rounded text-sm">
            <strong>Error:</strong> {{ $job->error_message }}
        </div>
        @endif

        @if(!$job->isTerminal())
        <form method="POST" action="{{ route('jobs.cancel', $job->id) }}" class="mt-4">
            @csrf
            <button type="submit"
                class="text-sm text-red-600 border border-red-300 px-3 py-1.5 rounded hover:bg-red-50"
                onclick="return confirm('Cancel this job?')">
                Cancel Job
            </button>
        </form>
        @endif
    </div>

    {{-- Results --}}
    @if($job->status === 'completed' && $job->result)
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Results</h2>

        @if($job->primitive_type === 'sampler')
            @php
                $counts = $job->result->getCounts();
                $total = array_sum($counts);
                arsort($counts);
            @endphp
            <p class="text-sm text-gray-500 mb-4">
                Measurement outcomes across {{ number_format($total) }} shots:
            </p>
            <div class="space-y-2">
                @foreach($counts as $bitstring => $count)
                @php $pct = $total > 0 ? round($count / $total * 100, 1) : 0; @endphp
                <div class="flex items-center gap-3 text-sm">
                    <span class="font-mono w-16 text-right">|{{ $bitstring }}⟩</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                        <div class="bg-blue-500 h-4 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="w-20 text-right text-gray-600">{{ number_format($count) }} ({{ $pct }}%)</span>
                </div>
                @endforeach
            </div>
        @endif

        @if($job->primitive_type === 'estimator')
            @php $evs = $job->result->getExpectationValues(); @endphp
            <p class="text-sm text-gray-500 mb-4">Expectation values per observable:</p>
            <div class="space-y-2">
                @foreach($evs as $idx => $ev)
                <div class="flex items-center gap-3 text-sm">
                    <span class="font-mono w-16">PUB {{ $idx }}</span>
                    <span class="font-semibold">{{ is_numeric($ev) ? round($ev, 6) : json_encode($ev) }}</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- Raw payload --}}
    <details class="bg-white rounded-xl shadow">
        <summary class="px-6 py-4 cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-800">
            Raw Payload / Result JSON
        </summary>
        <div class="px-6 pb-6">
            <pre class="bg-gray-900 text-green-300 rounded p-4 text-xs overflow-x-auto">{{ json_encode($job->getRawOriginal(), JSON_PRETTY_PRINT) }}</pre>
        </div>
    </details>

</div>

</body>
</html>
