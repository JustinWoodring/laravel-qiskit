<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IBM Quantum Credentials
    |--------------------------------------------------------------------------
    */

    'api_key' => env('QISKIT_API_KEY'),

    'service_crn' => env('QISKIT_SERVICE_CRN'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    'base_url' => env('QISKIT_BASE_URL', 'https://us-east.quantum-computing.cloud.ibm.com'),

    'iam_token_url' => env('QISKIT_IAM_TOKEN_URL', 'https://iam.cloud.ibm.com/identity/token'),

    /*
    |--------------------------------------------------------------------------
    | Default Backend
    |--------------------------------------------------------------------------
    */

    'default_backend' => env('QISKIT_DEFAULT_BACKEND', 'ibm_brisbane'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'store' => env('QISKIT_CACHE_STORE', null), // null = default cache store
        'prefix' => env('QISKIT_CACHE_PREFIX', 'qiskit_iam_token'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Polling Configuration
    |--------------------------------------------------------------------------
    */

    'polling' => [
        'interval' => env('QISKIT_POLL_INTERVAL', 10), // seconds between polls
        'max_attempts' => env('QISKIT_POLL_MAX_ATTEMPTS', 360), // 1 hour at 10s intervals
        'queue' => env('QISKIT_POLL_QUEUE', null), // null = default queue
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => env('QISKIT_HTTP_TIMEOUT', 30),
        'retry' => [
            'times' => env('QISKIT_HTTP_RETRY_TIMES', 3),
            'sleep' => env('QISKIT_HTTP_RETRY_SLEEP', 1000), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        'quantum_job' => \JustinWoodring\LaravelQiskit\Models\QuantumJob::class,
    ],

];
