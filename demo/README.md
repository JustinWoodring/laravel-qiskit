# laravel-qiskit Demo

A real Laravel 13 application demonstrating the `justinwoodring/laravel-qiskit` package.

## Features

- **Submit jobs** — choose Bell state, GHZ state, or H⊗2; pick shots and backend
- **Live job table** — paginated list with status badges
- **Job detail page** — measurement counts rendered as bar charts, expectation values, raw payload
- **Session demo** — two jobs submitted inside a single IBM Quantum session
- **Backend browser** — lists all backends from your IBM Quantum account
- **Event listener** — `HandleQuantumResults` logs completed job counts to `storage/logs/laravel.log`
- **Standalone examples** — runnable PHP scripts in `examples/`

## Setup

```bash
cd demo

# Install dependencies (the parent package is symlinked via path repository)
composer install

# Copy env and fill in your IBM Quantum credentials
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
QISKIT_API_KEY=your-ibm-quantum-api-key
QISKIT_SERVICE_CRN=crn:v1:bluemix:public:quantum-computing:us-east:...
QISKIT_DEFAULT_BACKEND=ibm_brisbane
```

Get credentials at <https://quantum.ibm.com/account>.

```bash
# Publish config + run migrations
php artisan qiskit:install

# Start the dev server
php artisan serve
```

Open <http://localhost:8000>.

## Queue Worker

For async job submission (the default in the web UI), run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

Jobs are submitted to IBM Quantum via `DispatchQuantumJob`, then polled automatically by `PollQuantumJobStatus`. When a job completes, `QuantumJobCompleted` fires and `HandleQuantumResults` logs the counts.

## Standalone Examples

These scripts bootstrap Laravel and run synchronously — no queue worker needed:

```bash
# Bell state via Sampler
php examples/bell_state.php

# VQE angle sweep via Estimator
php examples/vqe_estimator.php

# Backend discovery
php examples/backend_discovery.php

# Async job with event listener
php examples/async_job_with_events.php
```

## Artisan Commands

```bash
# List recent jobs
php artisan qiskit:jobs

# Check a job by local ID
php artisan qiskit:status 1

# Check by IBM job ID
php artisan qiskit:status cj1abc123def --ibm-id

# Cancel a job
php artisan qiskit:cancel 1

# List backends
php artisan qiskit:backends --online
```
