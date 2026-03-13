<?php

namespace JustinWoodring\LaravelQiskit\Console\Commands;

use Illuminate\Console\Command;
use JustinWoodring\LaravelQiskit\Backends\Backend;
use JustinWoodring\LaravelQiskit\Backends\BackendRepository;

class QiskitBackendsCommand extends Command
{
    protected $signature = 'qiskit:backends {--online : Show only online backends}';

    protected $description = 'List available IBM Quantum backends';

    public function handle(BackendRepository $repository): int
    {
        $backends = $this->option('online')
            ? $repository->available()
            : $repository->all();

        if (empty($backends)) {
            $this->info('No backends found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Status', 'Qubits', 'Queue Depth', 'Simulator'],
            array_map(fn (Backend $b) => [
                $b->name,
                $b->status,
                $b->qubits,
                $b->queueDepth,
                $b->isSimulator ? 'Yes' : 'No',
            ], $backends)
        );

        return self::SUCCESS;
    }
}
