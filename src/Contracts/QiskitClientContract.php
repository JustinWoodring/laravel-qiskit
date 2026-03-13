<?php

namespace JustinWoodring\LaravelQiskit\Contracts;

use JustinWoodring\LaravelQiskit\Client\QiskitResponse;

interface QiskitClientContract
{
    public function request(string $method, string $path, array $data = []): QiskitResponse;

    public function submitJob(array $payload): QiskitResponse;

    public function getJob(string $jobId): QiskitResponse;

    public function getJobResults(string $jobId): QiskitResponse;

    public function cancelJob(string $jobId): QiskitResponse;

    public function deleteJob(string $jobId): QiskitResponse;

    public function listJobs(int $limit = 20, int $offset = 0): QiskitResponse;

    public function listBackends(): QiskitResponse;

    public function getBackendStatus(string $backendId): QiskitResponse;

    public function getBackendProperties(string $backendId): QiskitResponse;

    public function getBackendConfiguration(string $backendId): QiskitResponse;
}
