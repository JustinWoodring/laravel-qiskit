<?php

namespace JustinWoodring\LaravelQiskit\Client\Endpoints;

use JustinWoodring\LaravelQiskit\Client\QiskitResponse;

trait SessionEndpoints
{
    public function createSession(array $payload): QiskitResponse
    {
        return $this->request('POST', '/v1/sessions', $payload);
    }

    public function getSession(string $sessionId): QiskitResponse
    {
        return $this->request('GET', "/v1/sessions/{$sessionId}");
    }

    public function updateSession(string $sessionId, array $payload): QiskitResponse
    {
        return $this->request('PATCH', "/v1/sessions/{$sessionId}", $payload);
    }

    public function closeSession(string $sessionId): QiskitResponse
    {
        return $this->request('DELETE', "/v1/sessions/{$sessionId}/close");
    }
}
