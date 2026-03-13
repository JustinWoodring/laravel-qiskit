<?php

namespace JustinWoodring\LaravelQiskit\Console\Commands;

use Illuminate\Console\Command;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;

class QiskitCancelCommand extends Command
{
    protected $signature = 'qiskit:cancel {id : Local model ID}';

    protected $description = 'Cancel a quantum job';

    public function handle(QiskitClient $client): int
    {
        $model = QuantumJob::findOrFail($this->argument('id'));

        if ($model->isTerminal()) {
            $this->error("Job #{$model->id} is already in a terminal state: {$model->status}");

            return self::FAILURE;
        }

        if ($model->ibm_job_id) {
            $response = $client->cancelJob($model->ibm_job_id);

            if ($response->failed()) {
                $this->error('Failed to cancel job on IBM Quantum: ' . json_encode($response->toArray()));

                return self::FAILURE;
            }
        }

        $model->cancel();

        $this->info("Job #{$model->id} has been cancelled.");

        return self::SUCCESS;
    }
}
