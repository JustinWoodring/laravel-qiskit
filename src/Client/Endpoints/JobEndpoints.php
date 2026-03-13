<?php

namespace JustinWoodring\LaravelQiskit\Client\Endpoints;

use JustinWoodring\LaravelQiskit\Client\QiskitResponse;

trait JobEndpoints
{
    public function submitJob(array $payload): QiskitResponse
    {
        return $this->request('POST', '/v1/jobs', $payload);
    }

    public function getJob(string $jobId): QiskitResponse
    {
        return $this->request('GET', "/v1/jobs/{$jobId}");
    }

    public function getJobResults(string $jobId): QiskitResponse
    {
        return $this->request('GET', "/v1/jobs/{$jobId}/results");
    }

    public function cancelJob(string $jobId): QiskitResponse
    {
        return $this->request('POST', "/v1/jobs/{$jobId}/cancel");
    }

    public function deleteJob(string $jobId): QiskitResponse
    {
        return $this->request('DELETE', "/v1/jobs/{$jobId}");
    }

    public function listJobs(int $limit = 20, int $offset = 0): QiskitResponse
    {
        return $this->request('GET', '/v1/jobs', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
