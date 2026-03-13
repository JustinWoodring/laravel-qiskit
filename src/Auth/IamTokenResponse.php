<?php

namespace JustinWoodring\LaravelQiskit\Auth;

use Carbon\Carbon;

readonly class IamTokenResponse
{
    public function __construct(
        public string $token,
        public int $expiresIn,
        public Carbon $fetchedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['access_token'],
            expiresIn: (int) $data['expires_in'],
            fetchedAt: Carbon::now(),
        );
    }

    public function cacheTtlSeconds(): int
    {
        return max(0, $this->expiresIn - 120);
    }
}
