<?php

namespace JustinWoodring\LaravelQiskit;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use JustinWoodring\LaravelQiskit\Auth\IamTokenManager;
use JustinWoodring\LaravelQiskit\Backends\BackendRepository;
use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Console\Commands\QiskitBackendsCommand;
use JustinWoodring\LaravelQiskit\Console\Commands\QiskitCancelCommand;
use JustinWoodring\LaravelQiskit\Console\Commands\QiskitInstallCommand;
use JustinWoodring\LaravelQiskit\Console\Commands\QiskitListJobsCommand;
use JustinWoodring\LaravelQiskit\Console\Commands\QiskitStatusCommand;
use JustinWoodring\LaravelQiskit\Events\QuantumJobSubmitted;
use JustinWoodring\LaravelQiskit\Listeners\StartJobPolling;
use JustinWoodring\LaravelQiskit\Sessions\SessionManager;
use JustinWoodring\LaravelQiskit\Support\QiskitManager;

class QiskitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/qiskit.php', 'qiskit');

        $this->app->singleton(IamTokenManager::class, function ($app) {
            $cache = $app->make('cache');
            $store = config('qiskit.cache.store');

            return new IamTokenManager(
                http: $app->make(HttpFactory::class),
                cache: $store ? $cache->store($store) : $cache->store(),
                apiKey: config('qiskit.api_key'),
                iamTokenUrl: config('qiskit.iam_token_url'),
                cachePrefix: config('qiskit.cache.prefix', 'qiskit_iam_token'),
            );
        });

        $this->app->singleton(QiskitClient::class, function ($app) {
            return new QiskitClient(
                http: $app->make(HttpFactory::class),
                tokenManager: $app->make(IamTokenManager::class),
                baseUrl: config('qiskit.base_url'),
                serviceCrn: config('qiskit.service_crn'),
                timeout: config('qiskit.http.timeout', 30),
                retryTimes: config('qiskit.http.retry.times', 3),
                retrySleep: config('qiskit.http.retry.sleep', 1000),
            );
        });

        $this->app->singleton(BackendRepository::class, function ($app) {
            return new BackendRepository(
                client: $app->make(QiskitClient::class),
            );
        });

        $this->app->singleton(SessionManager::class, function ($app) {
            return new SessionManager(
                client: $app->make(QiskitClient::class),
            );
        });

        $this->app->singleton(QiskitManager::class, function ($app) {
            return new QiskitManager(
                client: $app->make(QiskitClient::class),
                backendRepository: $app->make(BackendRepository::class),
                sessionManager: $app->make(SessionManager::class),
            );
        });

        $this->app->alias(QiskitManager::class, 'qiskit');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/qiskit.php' => config_path('qiskit.php'),
            ], 'qiskit-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'qiskit-migrations');

            $this->commands([
                QiskitInstallCommand::class,
                QiskitListJobsCommand::class,
                QiskitStatusCommand::class,
                QiskitCancelCommand::class,
                QiskitBackendsCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Event::listen(QuantumJobSubmitted::class, StartJobPolling::class);
    }
}
