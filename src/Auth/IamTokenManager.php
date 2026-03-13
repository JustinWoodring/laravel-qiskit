<?php

namespace JustinWoodring\LaravelQiskit\Auth;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use JustinWoodring\LaravelQiskit\Exceptions\AuthenticationException;

class IamTokenManager
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly CacheRepository $cache,
        private readonly string $apiKey,
        private readonly string $iamTokenUrl,
        private readonly string $cachePrefix,
    ) {}

    public function getToken(): string
    {
        $cacheKey = $this->cacheKey();

        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        return $this->fetchAndCache();
    }

    public function forgetToken(): void
    {
        $this->cache->forget($this->cacheKey());
    }

    private function fetchAndCache(): string
    {
        $response = $this->http
            ->asForm()
            ->post($this->iamTokenUrl, [
                'grant_type' => 'urn:ibm:params:oauth:grant-type:apikey',
                'apikey' => $this->apiKey,
            ]);

        if ($response->failed()) {
            throw new AuthenticationException(
                'Failed to obtain IAM token: ' . $response->body()
            );
        }

        $tokenResponse = IamTokenResponse::fromArray($response->json());

        $this->cache->put(
            $this->cacheKey(),
            $tokenResponse->token,
            $tokenResponse->cacheTtlSeconds()
        );

        return $tokenResponse->token;
    }

    private function cacheKey(): string
    {
        return $this->cachePrefix . '_' . md5($this->apiKey);
    }
}
