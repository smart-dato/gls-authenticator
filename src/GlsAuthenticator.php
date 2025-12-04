<?php

namespace SmartDato\GlsAuthenticator;

use SmartDato\GlsAuthenticator\Exceptions\MissingCredentialsException;
use SmartDato\GlsAuthenticator\Services\TokenService;
use SmartDato\GlsAuthenticator\ValueObjects\AccessToken;
use SmartDato\GlsAuthenticator\ValueObjects\Credentials;
use SmartDato\GlsAuthenticator\ValueObjects\TokenRequest;

class GlsAuthenticator
{
    protected ?Credentials $runtimeCredentials = null;

    protected ?string $runtimeEnvironment = null;

    protected array $runtimeScopes = [];

    protected bool $forceRefresh = false;

    protected string $authMethod;

    protected ?TokenService $tokenService = null;

    protected ?Credentials $defaultCredentials = null;

    protected ?string $defaultEnvironment = null;

    protected array $defaultScopes = [];

    public function __construct(
        ?TokenService $tokenService = null,
        ?Credentials $defaultCredentials = null,
        ?string $defaultEnvironment = null,
        array $defaultScopes = [],
        ?string $authMethod = null
    ) {
        $this->tokenService = $tokenService;
        $this->defaultCredentials = $defaultCredentials;
        $this->defaultEnvironment = $defaultEnvironment;
        $this->defaultScopes = $defaultScopes;
        $this->authMethod = $authMethod ?? 'basic_auth';
    }

    /**
     * Get the token service instance, resolving from container if needed
     */
    protected function getTokenService(): TokenService
    {
        if ($this->tokenService === null) {
            $this->tokenService = app(TokenService::class);
        }

        return $this->tokenService;
    }

    /**
     * Get an access token using current configuration
     */
    public function getToken(): AccessToken
    {
        $credentials = $this->runtimeCredentials ?? $this->defaultCredentials;

        if ($credentials === null) {
            throw new MissingCredentialsException(
                'No credentials configured. Use withCredentials() or configure default credentials.'
            );
        }

        $environment = $this->runtimeEnvironment ?? $this->defaultEnvironment ?? 'sandbox';
        $scopes = ! empty($this->runtimeScopes) ? $this->runtimeScopes : $this->defaultScopes;

        $request = new TokenRequest(
            credentials: $credentials,
            environment: $environment,
            scopes: $scopes,
            authMethod: $this->authMethod
        );

        return $this->getTokenService()->getToken($request, $this->forceRefresh);
    }

    /**
     * Get an access token with custom credentials (runtime configuration)
     */
    public function withCredentials(string $clientId, string $clientSecret): self
    {
        $clone = clone $this;
        $clone->runtimeCredentials = new Credentials($clientId, $clientSecret);

        return $clone;
    }

    /**
     * Use a specific environment (sandbox/production)
     */
    public function environment(string $environment): self
    {
        $clone = clone $this;
        $clone->runtimeEnvironment = $environment;

        return $clone;
    }

    /**
     * Request specific scopes
     */
    public function scopes(string|array $scopes): self
    {
        $clone = clone $this;
        $clone->runtimeScopes = is_array($scopes) ? $scopes : explode(' ', $scopes);

        return $clone;
    }

    /**
     * Force a fresh token (bypass cache)
     */
    public function fresh(): self
    {
        $clone = clone $this;
        $clone->forceRefresh = true;

        return $clone;
    }

    /**
     * Clear cached token for current credentials
     */
    public function clearCache(): bool
    {
        $credentials = $this->runtimeCredentials ?? $this->defaultCredentials;

        if ($credentials === null) {
            return false;
        }

        $environment = $this->runtimeEnvironment ?? $this->defaultEnvironment ?? 'sandbox';
        $scopes = ! empty($this->runtimeScopes) ? $this->runtimeScopes : $this->defaultScopes;

        $request = new TokenRequest(
            credentials: $credentials,
            environment: $environment,
            scopes: $scopes,
            authMethod: $this->authMethod
        );

        return $this->getTokenService()->clearToken($request);
    }

    /**
     * Clear all cached tokens
     */
    public function clearAllTokens(): bool
    {
        return $this->getTokenService()->clearAllTokens();
    }

    /**
     * Get the currently configured credentials
     */
    public function getCredentials(): ?Credentials
    {
        return $this->runtimeCredentials ?? $this->defaultCredentials;
    }

    /**
     * Check if the authenticator is configured
     */
    public function isConfigured(): bool
    {
        return $this->getCredentials() !== null;
    }
}
