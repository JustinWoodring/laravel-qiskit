@extends('layout')

@section('content')
<div style="display:flex; gap:1.5rem; align-items:flex-start;">

    {{-- Submit form --}}
    <div style="width:280px; flex-shrink:0;">
        <div class="card">
            <h3>Submit a Job</h3>
            <form method="POST" action="{{ route('jobs.store') }}">
                @csrf
                <div style="margin-bottom:.75rem;">
                    <label>Circuit</label>
                    <select name="circuit" style="width:100%;">
                        <option value="bell">Bell state (2q)</option>
                        <option value="ghz">GHZ state (3q)</option>
                        <option value="random">H⊗2 (2q)</option>
                    </select>
                </div>
                <div style="margin-bottom:.75rem;">
                    <label>Shots</label>
                    <input type="number" name="shots" value="1024" min="1" max="20000" style="width:100%;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Backend (optional)</label>
                    <input type="text" name="backend" placeholder="{{ config('qiskit.default_backend') }}" style="width:100%;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Dispatch</button>
            </form>
        </div>

        <div class="card">
            <h3>Session Demo</h3>
            <p style="font-size:.8rem; color:#64748b; margin-bottom:1rem;">
                Submit two jobs inside a single IBM Quantum session.
            </p>
            <form method="POST" action="{{ route('sessions.demo') }}">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width:100%;">Run Session Demo</button>
            </form>
        </div>
    </div>

    {{-- Jobs table --}}
    <div style="flex:1; min-width:0;">
        <h2>Quantum Jobs</h2>
        @if($jobs->isEmpty())
            <p style="color:#475569; font-size:.9rem;">No jobs yet — submit one on the left.</p>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>IBM Job ID</th>
                    <th>Primitive</th>
                    <th>Backend</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                <tr>
                    <td>{{ $job->id }}</td>
                    <td style="font-family:monospace; font-size:.75rem; color:#64748b;">
                        {{ $job->ibm_job_id ? substr($job->ibm_job_id, 0, 16) . '…' : '—' }}
                    </td>
                    <td>{{ $job->primitive_type ?? '—' }}</td>
                    <td>{{ $job->backend ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $job->status }}">{{ $job->status }}</span>
                    </td>
                    <td class="meta">{{ $job->submitted_at?->diffForHumans() ?? $job->created_at->diffForHumans() }}</td>
                    <td>
                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-secondary" style="font-size:.75rem; padding:.2rem .6rem;">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $jobs->links() }}</div>
        @endif
    </div>
</div>
@endsection
