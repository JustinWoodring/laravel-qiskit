<?php

namespace JustinWoodring\LaravelQiskit\Client\Endpoints;

use JustinWoodring\LaravelQiskit\Client\QiskitResponse;

trait BackendEndpoints
{
    public function listBackends(): QiskitResponse
    {
        return $this->request('GET', '/v1/backends');
    }

    public function getBackendStatus(string $backendId): QiskitResponse
    {
        return $this->request('GET', "/v1/backends/{$backendId}/status");
    }

    public function getBackendProperties(string $backendId): QiskitResponse
    {
        return $this->request('GET', "/v1/backends/{$backendId}/properties");
    }

    public function getBackendConfiguration(string $backendId): QiskitResponse
    {
        return $this->request('GET', "/v1/backends/{$backendId}/configuration");
    }
}
