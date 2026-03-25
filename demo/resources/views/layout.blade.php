<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>laravel-qiskit demo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f0f11; color: #e2e8f0; min-height: 100vh; }
        header { background: #1a1a2e; border-bottom: 1px solid #2d2d4e; padding: 1rem 2rem; display: flex; align-items: center; gap: 2rem; }
        header h1 { font-size: 1.1rem; font-weight: 700; color: #a78bfa; letter-spacing: -0.02em; }
        header nav a { color: #94a3b8; text-decoration: none; font-size: 0.875rem; margin-right: 1.5rem; }
        header nav a:hover { color: #e2e8f0; }
        main { max-width: 1100px; margin: 0 auto; padding: 2rem; }
        .alert { padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1.5rem; font-size: .875rem; }
        .alert-success { background: #052e16; color: #4ade80; border: 1px solid #166534; }
        .alert-error   { background: #2d0f0f; color: #f87171; border: 1px solid #7f1d1d; }
        h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem; color: #c4b5fd; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th { text-align: left; padding: .5rem .75rem; color: #64748b; font-weight: 500; border-bottom: 1px solid #1e293b; }
        td { padding: .6rem .75rem; border-bottom: 1px solid #1a1a2e; }
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
        .badge-pending   { background: #1e293b; color: #94a3b8; }
        .badge-queued    { background: #1e3a5f; color: #60a5fa; }
        .badge-running   { background: #1a2e1a; color: #4ade80; }
        .badge-completed { background: #052e16; color: #4ade80; }
        .badge-failed    { background: #2d0f0f; color: #f87171; }
        .badge-cancelled { background: #1a1a2e; color: #64748b; }
        .btn { display: inline-block; padding: .45rem .9rem; border-radius: .375rem; font-size: .875rem; font-weight: 500; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: #7c3aed; color: #fff; }
        .btn-primary:hover { background: #6d28d9; }
        .btn-danger { background: #7f1d1d; color: #fca5a5; }
        .btn-danger:hover { background: #991b1b; }
        .btn-secondary { background: #1e293b; color: #94a3b8; }
        .btn-secondary:hover { background: #273449; }
        form { display: inline; }
        .card { background: #1a1a2e; border: 1px solid #2d2d4e; border-radius: .75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
        .card h3 { font-size: 1rem; font-weight: 600; color: #c4b5fd; margin-bottom: 1rem; }
        label { display: block; font-size: .8rem; color: #94a3b8; margin-bottom: .25rem; }
        select, input[type=text], input[type=number] { background: #0f172a; border: 1px solid #2d2d4e; color: #e2e8f0; padding: .4rem .6rem; border-radius: .375rem; font-size: .875rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        pre { background: #0f172a; border: 1px solid #1e293b; border-radius: .5rem; padding: 1rem; font-size: .8rem; overflow-x: auto; color: #a5f3fc; }
        .meta { font-size: .75rem; color: #475569; }
    </style>
</head>
<body>
<header>
    <h1>⚛ laravel-qiskit</h1>
    <nav>
        <a href="{{ route('jobs.index') }}">Jobs</a>
        <a href="{{ route('backends.index') }}">Backends</a>
    </nav>
</header>
<main>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
