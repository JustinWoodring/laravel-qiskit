@extends('layout')

@section('content')
<h2>Backends</h2>

@if(empty($backends))
    <p style="color:#475569; font-size:.9rem;">No backends returned — check your credentials or network.</p>
@else
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Qubits</th>
            <th>Queue depth</th>
            <th>Simulator</th>
        </tr>
    </thead>
    <tbody>
        @foreach($backends as $backend)
        <tr>
            <td style="font-family:monospace; color:#a5f3fc;">{{ $backend->name ?? $backend->id ?? '—' }}</td>
            <td>
                @php $status = strtolower($backend->status ?? 'unknown'); @endphp
                <span class="badge {{ $status === 'online' ? 'badge-completed' : 'badge-pending' }}">{{ $status }}</span>
            </td>
            <td>{{ $backend->qubits ?? '—' }}</td>
            <td>{{ $backend->queueDepth ?? '—' }}</td>
            <td>{{ ($backend->isSimulator ?? false) ? 'yes' : 'no' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
