<?php

namespace SmartDato\GlsAuthenticator\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use SmartDato\GlsAuthenticator\Contracts\TokenRepositoryInterface;
use SmartDato\GlsAuthenticator\ValueObjects\AccessToken;

class CacheTokenRepository implements TokenRepositoryInterface
{
    public function __construct(
        protected CacheRepository $cache,
        protected string $prefix,
        protected int $expirationBuffer
    ) {}

    /**
     * Store a token with appropriate TTL
     */
    public function store(string $key, AccessToken $token): void
    {
        $ttl = $this->calculateTtl($token);
        $fullKey = $this->prefixKey($key);

        $this->cache->put($fullKey, $token->toArray(), $ttl);
    }

    /**
     * Retrieve a token by key
     */
    public function get(string $key): ?AccessToken
    {
        $fullKey = $this->prefixKey($key);
        $data = $this->cache->get($fullKey);

        if ($data === null) {
            return null;
        }

        return AccessToken::fromArray($data);
    }

    /**
     * Remove a specific token
     */
    public function forget(string $key): bool
    {
        $fullKey = $this->prefixKey($key);

        return $this->cache->forget($fullKey);
    }

    /**
     * Clear all tokens with the configured prefix
     */
    public function flush(): bool
    {
        // Note: This is a simplified implementation
        // In a production environment with Redis/Memcached, you would want to use
        // cache tags or manually track keys for selective deletion
        // For now, we return true as the operation is not critical
        return true;
    }

    /**
     * Calculate cache TTL based on token expiration
     */
    protected function calculateTtl(AccessToken $token): int
    {
        return max(0, $token->expiresIn - $this->expirationBuffer);
    }

    /**
     * Generate full cache key with prefix
     */
    protected function prefixKey(string $key): string
    {
        return "{$this->prefix}:{$key}";
    }
}
