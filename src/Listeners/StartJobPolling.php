<?php

namespace JustinWoodring\LaravelQiskit\Listeners;

use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;
use JustinWoodring\LaravelQiskit\Jobs\PollQuantumJobStatus;

class StartJobPolling
{
    public function handle(QuantumJobSubmitted $event): void
    {
        $interval = config('qiskit.polling.interval', 10);
        $queue = config('qiskit.polling.queue');

        $job = PollQuantumJobStatus::dispatch(
            $event->ibmJobId,
            $event->job->id,
        )->delay(now()->addSeconds($interval));

        if ($queue) {
            $job->onQueue($queue);
        }
    }
}
