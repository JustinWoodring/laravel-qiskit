<?php

namespace JustinWoodring\LaravelQiskit\Backends;

use JustinWoodring\LaravelQiskit\Client\QiskitClient;
use JustinWoodring\LaravelQiskit\Client\QiskitResponse;
use JustinWoodring\LaravelQiskit\Contracts\BackendRepositoryContract;
use JustinWoodring\LaravelQiskit\Exceptions\BackendUnavailableException;

class BackendRepository implements BackendRepositoryContract
{
    public function __construct(
        private readonly QiskitClient $client,
    ) {}

    /** @return Backend[] */
    public function all(): array
    {
        $response = $this->client->listBackends();

        $backends = $response->get('backends', $response->toArray());

        return array_map(fn (array $data) => Backend::fromArray($data), $backends);
    }

    /** @return Backend[] */
    public function available(): array
    {
        return $this->filter()->online()->get();
    }

    public function filter(): BackendFilter
    {
        return new BackendFilter($this->all());
    }

    public function find(string $id): Backend
    {
        foreach ($this->all() as $backend) {
            if ($backend->id === $id || $backend->name === $id) {
                return $backend;
            }
        }

        throw new BackendUnavailableException("Backend '{$id}' not found.");
    }

    public function status(string $id): QiskitResponse
    {
        return $this->client->getBackendStatus($id);
    }

    public function properties(string $id): QiskitResponse
    {
        return $this->client->getBackendProperties($id);
    }

    public function configuration(string $id): QiskitResponse
    {
        return $this->client->getBackendConfiguration($id);
    }
}
