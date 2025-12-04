<?php

namespace SmartDato\GlsAuthenticator\ValueObjects;

use SmartDato\GlsAuthenticator\Exceptions\InvalidConfigurationException;

final readonly class TokenRequest
{
    public function __construct(
        public Credentials $credentials,
        public string $environment,
        public array $scopes = [],
        public string $authMethod = 'basic_auth',
        public string $grantType = 'client_credentials'
    ) {
        $this->validate();
    }

    /**
     * Get the scopes as a space-separated string
     */
    public function getScopesString(): string
    {
        return implode(' ', $this->scopes);
    }

    /**
     * Check if request has scopes
     */
    public function hasScopes(): bool
    {
        return ! empty($this->scopes);
    }

    /**
     * Validate the request
     */
    protected function validate(): void
    {
        if (! in_array($this->environment, ['sandbox', 'production'])) {
            throw new InvalidConfigurationException(
                "Invalid environment: {$this->environment}. Must be 'sandbox' or 'production'"
            );
        }

        if (! in_array($this->authMethod, ['basic_auth', 'body'])) {
            throw new InvalidConfigurationException(
                "Invalid auth method: {$this->authMethod}. Must be 'basic_auth' or 'body'"
            );
        }

        if ($this->grantType !== 'client_credentials') {
            throw new InvalidConfigurationException(
                "Invalid grant type: {$this->grantType}. Must be 'client_credentials'"
            );
        }
    }

    /**
     * Get request hash for cache key generation
     */
    public function hash(): string
    {
        $components = [
            $this->environment,
            $this->credentials->clientId,
            $this->getScopesString(),
        ];

        return hash('sha256', implode('|', $components));
    }
}
