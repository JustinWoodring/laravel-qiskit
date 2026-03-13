<?php

namespace JustinWoodring\LaravelQiskit\Sessions;

use JustinWoodring\LaravelQiskit\Client\QiskitClient;

class SessionManager
{
    public function __construct(
        private readonly QiskitClient $client,
    ) {}

    public function open(string $backend, ?int $maxTtl = null): Session
    {
        $payload = ['backend_name' => $backend];

        if ($maxTtl !== null) {
            $payload['max_ttl'] = $maxTtl;
        }

        $response = $this->client->createSession($payload);

        return Session::fromArray($response->toArray());
    }

    public function get(string $id): Session
    {
        $response = $this->client->getSession($id);

        return Session::fromArray($response->toArray());
    }

    public function pause(string $id): Session
    {
        $response = $this->client->updateSession($id, ['state' => 'paused']);

        return Session::fromArray($response->toArray());
    }

    public function resume(string $id): Session
    {
        $response = $this->client->updateSession($id, ['state' => 'open']);

        return Session::fromArray($response->toArray());
    }

    public function close(string $id): void
    {
        $this->client->closeSession($id);
    }

    /**
     * Open a session, execute the callback, then close the session automatically.
     *
     * @param  callable(string $sessionId): mixed  $callback
     */
    public function run(string $backend, callable $callback, ?int $maxTtl = null): mixed
    {
        $session = $this->open($backend, $maxTtl);

        try {
            return $callback($session->id);
        } finally {
            $this->close($session->id);
        }
    }
}
