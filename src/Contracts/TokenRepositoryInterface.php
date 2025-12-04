<?php

namespace SmartDato\GlsAuthenticator\Contracts;

use SmartDato\GlsAuthenticator\ValueObjects\AccessToken;

interface TokenRepositoryInterface
{
    /**
     * Store a token with appropriate TTL
     */
    public function store(string $key, AccessToken $token): void;

    /**
     * Retrieve a token by key
     */
    public function get(string $key): ?AccessToken;

    /**
     * Remove a specific token
     */
    public function forget(string $key): bool;

    /**
     * Clear all tokens with the configured prefix
     */
    public function flush(): bool;
}
