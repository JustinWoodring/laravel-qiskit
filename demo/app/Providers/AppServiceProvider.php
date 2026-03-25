<?php

namespace App\Providers;

use App\Listeners\HandleQuantumResults;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCompleted;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(QuantumJobCompleted::class, HandleQuantumResults::class);
    }
}
