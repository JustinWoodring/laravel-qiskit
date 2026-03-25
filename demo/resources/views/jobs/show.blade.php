@extends('layout')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h2 style="margin:0;">Job #{{ $job->id }}</h2>
    <div style="display:flex; gap:.5rem; align-items:center;">
        @if(!$job->isTerminal())
        <form method="POST" action="{{ route('jobs.cancel', $job) }}">
            @csrf
            <button type="submit" class="btn btn-danger">Cancel</button>
        </form>
        @endif
        <a href="{{ route('jobs.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="grid-2" style="margin-bottom:1.5rem;">
    <div class="card">
        <h3>Details</h3>
        <table>
            <tr><th style="width:140px;">Status</th><td><span class="badge badge-{{ $job->status }}">{{ $job->status }}</span></td></tr>
            <tr><th>Primitive</th><td>{{ $job->primitive_type ?? '—' }}</td></tr>
            <tr><th>Backend</th><td>{{ $job->backend ?? '—' }}</td></tr>
            <tr><th>IBM Job ID</th><td style="font-family:monospace; font-size:.8rem;">{{ $job->ibm_job_id ?? '—' }}</td></tr>
            <tr><th>Session ID</th><td style="font-family:monospace; font-size:.8rem;">{{ $job->ibm_session_id ?? '—' }}</td></tr>
            <tr><th>Poll count</th><td>{{ $job->poll_count }}</td></tr>
            <tr><th>Submitted</th><td>{{ $job->submitted_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
            <tr><th>Completed</th><td>{{ $job->completed_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
        </table>
    </div>

    @if($job->error_message)
    <div class="card">
        <h3 style="color:#f87171;">Error</h3>
        <pre>{{ $job->error_message }}</pre>
    </div>
    @endif
</div>

@if($job->status === 'completed' && $job->result)
<div class="card">
    <h3>Results</h3>
    @php $result = $job->getResultAttribute(); @endphp

    @if($result && ($counts = $result->getCounts()) && count($counts))
        <p style="color:#64748b; font-size:.8rem; margin-bottom:1rem;">Measurement counts ({{ array_sum($counts) }} shots)</p>
        @php arsort($counts); $total = array_sum($counts); @endphp
        @foreach($counts as $bitstring => $count)
        @php $pct = round($count / $total * 100, 1); @endphp
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.35rem;">
            <span style="font-family:monospace; color:#a5f3fc; width:60px; text-align:right;">|{{ $bitstring }}⟩</span>
            <div style="flex:1; background:#0f172a; border-radius:4px; height:16px; overflow:hidden;">
                <div style="width:{{ $pct }}%; background:#7c3aed; height:100%;"></div>
            </div>
            <span style="font-size:.8rem; color:#64748b; width:100px;">{{ $count }} ({{ $pct }}%)</span>
        </div>
        @endforeach
    @elseif($result && ($evs = $result->getExpectationValues()) && count($evs))
        <p style="color:#64748b; font-size:.8rem; margin-bottom:1rem;">Expectation values</p>
        @foreach($evs as $key => $val)
        <div style="display:flex; gap:1rem; margin-bottom:.25rem; font-family:monospace; font-size:.85rem;">
            <span style="color:#a5f3fc;">{{ $key }}</span>
            <span>{{ is_numeric($val) ? number_format($val, 6) : $val }}</span>
        </div>
        @endforeach
    @else
        <pre>{{ json_encode($job->result, JSON_PRETTY_PRINT) }}</pre>
    @endif
</div>
@endif

@if($job->payload)
<div class="card">
    <h3>Payload</h3>
    <pre>{{ json_encode($job->payload, JSON_PRETTY_PRINT) }}</pre>
</div>
@endif
@endsection
