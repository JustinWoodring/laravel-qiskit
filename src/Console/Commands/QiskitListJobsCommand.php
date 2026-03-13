<?php

namespace JustinWoodring\LaravelQiskit\Console\Commands;

use Illuminate\Console\Command;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class QiskitListJobsCommand extends Command
{
    protected $signature = 'qiskit:jobs {--status= : Filter by status} {--limit=20 : Number of records to show}';

    protected $description = 'List quantum jobs from the database';

    public function handle(): int
    {
        $query = QuantumJob::query()->latest()->limit((int) $this->option('limit'));

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $jobs = $query->get();

        if ($jobs->isEmpty()) {
            $this->info('No jobs found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'IBM Job ID', 'Backend', 'Primitive', 'Status', 'Poll Count', 'Submitted At'],
            $jobs->map(fn (QuantumJob $job) => [
                $job->id,
                $job->ibm_job_id ?? '-',
                $job->backend,
                $job->primitive_type,
                $job->status,
                $job->poll_count,
                $job->submitted_at?->toDateTimeString() ?? '-',
            ])
        );

        return self::SUCCESS;
    }
}
