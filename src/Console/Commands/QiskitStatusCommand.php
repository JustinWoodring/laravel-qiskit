<?php

namespace JustinWoodring\LaravelQiskit\Console\Commands;

use Illuminate\Console\Command;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class QiskitStatusCommand extends Command
{
    protected $signature = 'qiskit:status {id : Local model ID or IBM Job ID} {--ibm-id : Treat ID as an IBM Job ID}';

    protected $description = 'Display the current status of a quantum job';

    public function handle(QiskitClient $client): int
    {
        $id = $this->argument('id');

        if ($this->option('ibm-id')) {
            $model = QuantumJob::where('ibm_job_id', $id)->firstOrFail();
        } else {
            $model = QuantumJob::findOrFail($id);
        }

        $this->info("Local Job ID: {$model->id}");
        $this->info("IBM Job ID:   " . ($model->ibm_job_id ?? 'not submitted'));
        $this->info("Backend:      {$model->backend}");
        $this->info("Primitive:    {$model->primitive_type}");
        $this->info("Status:       {$model->status}");
        $this->info("Poll Count:   {$model->poll_count}");

        if ($model->submitted_at) {
            $this->info("Submitted:    {$model->submitted_at->toDateTimeString()}");
        }

        if ($model->completed_at) {
            $this->info("Completed:    {$model->completed_at->toDateTimeString()}");
        }

        if ($model->error_message) {
            $this->error("Error: {$model->error_message}");
        }

        if ($model->ibm_job_id && ! $model->isTerminal()) {
            $this->newLine();
            $this->info('Fetching live status from IBM Quantum...');

            $response = $client->getJob($model->ibm_job_id);
            $state = $response->get('state', []);

            $this->info("IBM Status:   " . ($state['status'] ?? 'unknown'));
        }

        return self::SUCCESS;
    }
}
