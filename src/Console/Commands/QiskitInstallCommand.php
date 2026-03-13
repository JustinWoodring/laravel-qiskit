<?php

namespace JustinWoodring\LaravelQiskit\Console\Commands;

use Illuminate\Console\Command;

class QiskitInstallCommand extends Command
{
    protected $signature = 'qiskit:install';

    protected $description = 'Publish the Qiskit config and run migrations';

    public function handle(): int
    {
        $this->info('Installing Laravel Qiskit...');

        $this->call('vendor:publish', [
            '--tag' => 'qiskit-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'qiskit-migrations',
            '--force' => false,
        ]);

        $this->call('migrate');

        $this->info('Laravel Qiskit installed successfully.');

        return self::SUCCESS;
    }
}
