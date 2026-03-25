# laravel-qiskit

**Quantum computing for Laravel — submit circuits, poll results, and handle IBM Quantum jobs just like you'd handle anything else in your app.**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%20|%2011%20|%2012%20|%2013-red)](https://laravel.com)

---

## Overview

`laravel-qiskit` is a first-class Laravel package for [IBM Quantum](https://quantum.ibm.com/) / Qiskit Runtime. It brings quantum job submission into the Laravel ecosystem you already know — Eloquent models, queued jobs, events, Artisan commands, and a fluent facade — so you can focus on your circuits instead of the API plumbing.

## Features

- **Fluent circuit builder** — chainable gate API with OPENQASM 3.0 serialization
- **Sampler & Estimator primitives** — mirrors the Qiskit Runtime primitive interface
- **Async job dispatch** — jobs go through Laravel's queue; polling happens automatically via a background job chain
- **Event-driven results** — listen for `QuantumJobCompleted` and get counts or expectation values
- **Session management** — open, close, or `run()` a session with auto-close
- **Backend discovery** — filter backends by qubit count, queue depth, and availability
- **Eloquent model** — `QuantumJob` with scopes, status constants, and a `PrimitiveResult` accessor
- **Artisan commands** — `qiskit:install`, `qiskit:jobs`, `qiskit:status`, `qiskit:cancel`, `qiskit:backends`

## Requirements

- PHP 8.2+
- Laravel 10, 11, 12, or 13
- An [IBM Quantum](https://quantum.ibm.com/account) account (API key + Service CRN)

## Installation

```bash
composer require justinwoodring/laravel-qiskit
```

Publish the config and run migrations:

```bash
php artisan qiskit:install
```

Add your credentials to `.env`:

```env
QISKIT_API_KEY=your-ibm-quantum-api-key
QISKIT_SERVICE_CRN=crn:v1:bluemix:public:quantum-computing:us-east:...
QISKIT_DEFAULT_BACKEND=ibm_brisbane
```

## Quick Start

### Build a circuit

```php
use JustinWoodring\LaravelQiskit\Circuit\Circuit;

// Bell state — maximally entangled 2-qubit state
$circuit = Circuit::new(2, 2)
    ->h(0)
    ->cx(0, 1)
    ->measure();

echo $circuit->toQasm();
```

```
OPENQASM 3.0;
include "stdgates.inc";

qubit[2] q;
bit[2] c;

h q[0];
cx q[0], q[1];
c[0] = measure q[0];
c[1] = measure q[1];
```

### Submit a Sampler job (async)

```php
use JustinWoodring\LaravelQiskit\Facades\Qiskit;

$job = Qiskit::sampler('ibm_brisbane')
    ->addPub($circuit, shots: 4096)
    ->dispatch()   // → PendingJob
    ->dispatch();  // → QuantumJob (Eloquent model, enqueues to queue)

// $job->id, $job->status, $job->backend ...
```

Start your queue worker to process the submission and polling:

```bash
php artisan queue:work
```

### Listen for results

```php
// app/Providers/EventServiceProvider.php
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;

protected $listen = [
    QuantumJobCompleted::class => [
        App\Listeners\HandleQuantumResults::class,
    ],
];
```

```php
// app/Listeners/HandleQuantumResults.php
public function handle(QuantumJobCompleted $event): void
{
    $counts = $event->result->getCounts();
    // ['00' => 2048, '11' => 2048]
}
```

### Estimator with observables

```php
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Facades\Qiskit;

$ansatz = Circuit::new(2)
    ->withParameters(['theta'])
    ->ry('theta', 0)
    ->cx(0, 1);

$job = Qiskit::estimator()
    ->addPub($ansatz->bind(['theta' => M_PI / 3]), observables: ['ZZ', 'XI'])
    ->dispatch()
    ->dispatch();
```

### Sessions

Reserve a backend across multiple jobs to avoid re-queuing between iterations:

```php
Qiskit::sessions()->run('ibm_brisbane', function (string $sessionId) {
    Qiskit::sampler()->addPub($circuit1)->inSession($sessionId)->dispatch()->dispatch();
    Qiskit::sampler()->addPub($circuit2)->inSession($sessionId)->dispatch()->dispatch();
    // Session closes automatically when the callback returns
});
```

### Backend discovery

```php
// All online real hardware with at least 100 qubits
$backends = Qiskit::backends()
    ->filter()
    ->online()
    ->simulator(false)
    ->withMinQubits(100)
    ->withMaxQueueDepth(30)
    ->get();
```

## Artisan Commands

```bash
# Publish config + run migrations
php artisan qiskit:install

# List jobs
php artisan qiskit:jobs
php artisan qiskit:jobs --status=completed --limit=50

# Check job status
php artisan qiskit:status 42
php artisan qiskit:status cj1abc123def --ibm-id

# Cancel a job
php artisan qiskit:cancel 42

# List backends
php artisan qiskit:backends
php artisan qiskit:backends --online
```

## Configuration

After running `qiskit:install`, edit `config/qiskit.php`:

```php
return [
    'api_key'         => env('QISKIT_API_KEY'),
    'service_crn'     => env('QISKIT_SERVICE_CRN'),
    'base_url'        => env('QISKIT_BASE_URL', 'https://us-east.quantum-computing.cloud.ibm.com'),
    'default_backend' => env('QISKIT_DEFAULT_BACKEND', 'ibm_brisbane'),

    'polling' => [
        'interval'     => env('QISKIT_POLL_INTERVAL', 10),      // seconds
        'max_attempts' => env('QISKIT_POLL_MAX_ATTEMPTS', 360),  // ~1 hour
        'queue'        => env('QISKIT_POLL_QUEUE', null),
    ],

    'http' => [
        'timeout' => env('QISKIT_HTTP_TIMEOUT', 30),
        'retry'   => ['times' => 3, 'sleep' => 1000],
    ],
];
```

## Events

| Event | Payload |
|---|---|
| `QuantumJobSubmitted` | `$job`, `$ibmJobId` |
| `QuantumJobCompleted` | `$job`, `$result` (`PrimitiveResult`) |
| `QuantumJobFailed` | `$job`, `$reason` |
| `QuantumJobCancelled` | `$job` |

## Circuit Gates

| Method | Gate |
|---|---|
| `h(q)` | Hadamard |
| `x(q)` `y(q)` `z(q)` | Pauli X / Y / Z |
| `s(q)` `t(q)` | S / T phase |
| `rx(θ, q)` `ry(θ, q)` `rz(φ, q)` | Rotation gates |
| `cx(c, t)` / `cnot(c, t)` | Controlled-X |
| `cz(c, t)` | Controlled-Z |
| `swap(a, b)` | SWAP |
| `ccx(c1, c2, t)` / `toffoli(...)` | Toffoli |
| `measure()` | Measure all qubits |
| `measureQubit(q, c)` | Measure qubit `q` into classical bit `c` |

Parameterized circuits:

```php
$circuit = Circuit::new(1)
    ->withParameters(['theta'])
    ->ry('theta', 0)
    ->bind(['theta' => 1.5707]);
```

## Testing

```bash
composer install
./vendor/bin/phpunit
```

Tests use [Orchestra Testbench](https://github.com/orchestral/testbench) with an in-memory SQLite database and faked HTTP responses — no real IBM Quantum credentials needed.

## Demo

A full demo Laravel app lives in [`demo/`](demo/). See [`demo/README.md`](demo/README.md) for setup instructions.

## License

MIT — see [LICENSE](LICENSE).
