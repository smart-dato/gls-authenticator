<?php

namespace SmartDato\GlsAuthenticator\Services;

use SmartDato\GlsAuthenticator\Contracts\TokenRepositoryInterface;
use SmartDato\GlsAuthenticator\Support\CacheKeyGenerator;
use SmartDato\GlsAuthenticator\ValueObjects\AccessToken;
use SmartDato\GlsAuthenticator\ValueObjects\TokenRequest;

class TokenService
{
    public function __construct(
        protected GlsApiClient $apiClient,
        protected TokenRepositoryInterface $tokenRepository
    ) {}

    /**
     * Get a token (from cache or fresh)
     */
    public function getToken(TokenRequest $request, bool $fresh = false): AccessToken
    {
        if ($fresh) {
            return $this->getFreshToken($request);
        }

        $cacheKey = $this->generateCacheKey($request);
        $cachedToken = $this->retrieveToken($cacheKey);

        if ($cachedToken !== null && $this->isTokenValid($cachedToken)) {
            return $cachedToken;
        }

        $token = $this->getFreshToken($request);
        $this->storeToken($cacheKey, $token);

        return $token;
    }

    /**
     * Get a fresh token from API (bypass cache)
     */
    public function getFreshToken(TokenRequest $request): AccessToken
    {
        $token = $this->apiClient->requestToken($request);
        $cacheKey = $this->generateCacheKey($request);

        $this->storeToken($cacheKey, $token);

        return $token;
    }

    /**
     * Clear cached token for a specific request
     */
    public function clearToken(TokenRequest $request): bool
    {
        $cacheKey = $this->generateCacheKey($request);

        return $this->tokenRepository->forget($cacheKey);
    }

    /**
     * Clear all cached tokens
     */
    public function clearAllTokens(): bool
    {
        return $this->tokenRepository->flush();
    }

    /**
     * Store a token in the repository
     */
    protected function storeToken(string $cacheKey, AccessToken $token): void
    {
        $this->tokenRepository->store($cacheKey, $token);
    }

    /**
     * Retrieve a token from the repository
     */
    protected function retrieveToken(string $cacheKey): ?AccessToken
    {
        return $this->tokenRepository->get($cacheKey);
    }

    /**
     * Check if a cached token is still valid
     */
    protected function isTokenValid(AccessToken $token): bool
    {
        // Token is valid if it's not expired (no buffer here, repository handles buffer)
        return ! $token->isExpired();
    }

    /**
     * Generate a cache key for the token request
     */
    protected function generateCacheKey(TokenRequest $request): string
    {
        return CacheKeyGenerator::generate($request);
    }
}
