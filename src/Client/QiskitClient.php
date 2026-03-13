<?php

namespace JustinWoodring\LaravelQiskit\Client;

use Illuminate\Http\Client\Factory as HttpFactory;
use JustinWoodring\LaravelQiskit\Auth\IamTokenManager;
use JustinWoodring\LaravelQiskit\Client\Endpoints\BackendEndpoints;
use JustinWoodring\LaravelQiskit\Client\Endpoints\JobEndpoints;
use JustinWoodring\LaravelQiskit\Client\Endpoints\SessionEndpoints;
use JustinWoodring\LaravelQiskit\Contracts\QiskitClientContract;
use JustinWoodring\LaravelQiskit\Exceptions\AuthenticationException;

class QiskitClient implements QiskitClientContract
{
    use JobEndpoints, SessionEndpoints, BackendEndpoints;

    public function __construct(
        private readonly HttpFactory $http,
        private readonly IamTokenManager $tokenManager,
        private readonly string $baseUrl,
        private readonly string $serviceCrn,
        private readonly int $timeout,
        private readonly int $retryTimes,
        private readonly int $retrySleep,
    ) {}

    public function request(string $method, string $path, array $data = []): QiskitResponse
    {
        $response = $this->makeRequest($method, $path, $data);

        if ($response->status() === 401) {
            $this->tokenManager->forgetToken();
            $response = $this->makeRequest($method, $path, $data);

            if ($response->status() === 401) {
                throw new AuthenticationException('IBM Quantum authentication failed after token refresh.');
            }
        }

        return new QiskitResponse(
            status: $response->status(),
            body: $response->json() ?? [],
        );
    }

    private function makeRequest(string $method, string $path, array $data): \Illuminate\Http\Client\Response
    {
        $pending = $this->http
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, throw: false)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->tokenManager->getToken(),
                'IBM-API-Version' => '2024-09-30',
                'Service-CRN' => $this->serviceCrn,
                'Accept' => 'application/json',
            ]);

        $url = rtrim($this->baseUrl, '/') . $path;

        return match (strtoupper($method)) {
            'GET' => $pending->get($url, $data ?: null),
            'POST' => $pending->post($url, $data),
            'PATCH' => $pending->patch($url, $data),
            'DELETE' => $pending->delete($url, $data),
            default => $pending->send($method, $url, ['json' => $data]),
        };
    }
}
