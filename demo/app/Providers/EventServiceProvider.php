<?php

namespace App\Providers;

use App\Listeners\HandleQuantumResults;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;
use JustinWoodring\LaravelQiskit\Events\QuantumJobFailed;
use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // The package auto-registers StartJobPolling for QuantumJobSubmitted,
        // but you can add your own listeners here too.
        QuantumJobSubmitted::class => [
            [HandleQuantumResults::class, 'handleSubmitted'],
        ],

        QuantumJobCompleted::class => [
            [HandleQuantumResults::class, 'handleCompleted'],
        ],

        QuantumJobFailed::class => [
            [HandleQuantumResults::class, 'handleFailed'],
        ],
    ];
}
