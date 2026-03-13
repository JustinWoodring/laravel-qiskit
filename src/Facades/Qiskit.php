<?php

namespace JustinWoodring\LaravelQiskit\Facades;

use Illuminate\Support\Facades\Facade;
use JustinWoodring\LaravelQiskit\Backends\BackendRepository;
use JustinWoodring\LaravelQiskit\Circuit\Circuit;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Models\QuantumJob;
use JustinWoodring\LaravelQiskit\Primitives\Estimator;
use JustinWoodring\LaravelQiskit\Primitives\Sampler;
use JustinWoodring\LaravelQiskit\Sessions\SessionManager;
use JustinWoodring\LaravelQiskit\Support\PendingJob;
use JustinWoodring\LaravelQiskit\Support\QiskitManager;

/**
 * @method static Sampler sampler(?string $backend = null)
 * @method static Estimator estimator(?string $backend = null)
 * @method static PendingJob run(Circuit|string $circuit, ?string $backend = null)
 * @method static QuantumJob job(int|string $id)
 * @method static \Illuminate\Database\Eloquent\Builder jobs()
 * @method static SessionManager sessions()
 * @method static BackendRepository backends()
 * @method static QiskitClient client()
 *
 * @see QiskitManager
 */
class Qiskit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'qiskit';
    }
}
