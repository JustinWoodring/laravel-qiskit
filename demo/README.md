# laravel-qiskit Demo

A minimal Laravel application demonstrating the `justinwoodring/laravel-qiskit` package.

## Setup

```bash
cd demo

# Install dependencies (symlinks the parent package via path repository)
composer install

# Copy env and fill in your IBM Quantum credentials
cp .env.example .env
php artisan key:generate

# Edit .env — set QISKIT_API_KEY and QISKIT_SERVICE_CRN
# Get these from https://quantum.ibm.com/account

# Install the package (publishes config + runs migrations)
php artisan qiskit:install

# Start the dev server
php artisan serve
```

Then open http://localhost:8000.

## Queue Worker

For async job dispatch (the recommended production pattern), run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

Jobs submitted via the web UI or `examples/async_job_with_events.php` will be picked up by the worker, submitted to IBM Quantum, and polled until completion. Results arrive via the `QuantumJobCompleted` event — see `app/Listeners/HandleQuantumResults.php`.

## Standalone Examples

These scripts run synchronously (blocking until IBM Quantum responds) and are useful for quick experiments:

```bash
# Bell state via Sampler
php examples/bell_state.php

# VQE angle sweep via Estimator
php examples/vqe_estimator.php

# Backend discovery + filtering
php examples/backend_discovery.php

# Async submission + event-driven polling
php examples/async_job_with_events.php
```

## Web Routes

| Route | Description |
|---|---|
| `GET /` | Welcome / quick start |
| `GET /backends` | List & filter IBM Quantum backends |
| `GET /jobs` | All submitted jobs |
| `GET /jobs/{id}` | Job status & results (auto-refreshes until terminal) |
| `GET /submit/bell-state` | Submit a Bell state Sampler job |
| `GET /submit/ghz` | Submit a 3-qubit GHZ state Sampler job |
| `GET /submit/vqe` | Submit a VQE Estimator job |
| `GET /sessions/demo` | Submit a batch of jobs in a single session |

## Artisan Commands

```bash
# List jobs
php artisan qiskit:jobs
php artisan qiskit:jobs --status=completed

# Check job status
php artisan qiskit:status 42
php artisan qiskit:status cj1abc123 --ibm-id

# Cancel a job
php artisan qiskit:cancel 42

# List backends
php artisan qiskit:backends
php artisan qiskit:backends --online
```
