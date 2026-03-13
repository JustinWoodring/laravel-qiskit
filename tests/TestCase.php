<?php

namespace JustinWoodring\LaravelQiskit\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use JustinWoodring\LaravelQiskit\QiskitServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QiskitServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Qiskit' => \JustinWoodring\LaravelQiskit\Facades\Qiskit::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // SQLite in-memory for tests
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Fake credentials
        $app['config']->set('qiskit.api_key', 'test-api-key');
        $app['config']->set('qiskit.service_crn', 'test-crn');
        $app['config']->set('qiskit.base_url', 'https://fake.quantum-computing.cloud.ibm.com');
        $app['config']->set('qiskit.iam_token_url', 'https://fake.iam.cloud.ibm.com/identity/token');
        $app['config']->set('qiskit.default_backend', 'ibm_test');

        // Use array cache for tests
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
