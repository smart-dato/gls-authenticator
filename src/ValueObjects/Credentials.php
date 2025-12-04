<?php

namespace SmartDato\GlsAuthenticator\ValueObjects;

use SmartDato\GlsAuthenticator\Exceptions\InvalidCredentialsException;

final readonly class Credentials
{
    public function __construct(
        public string $clientId,
        public string $clientSecret
    ) {
        $this->validate();
    }

    /**
     * Create from configuration
     */
    public static function fromConfig(array $config): ?self
    {
        $clientId = $config['client_id'] ?? null;
        $clientSecret = $config['client_secret'] ?? null;

        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        return new self($clientId, $clientSecret);
    }

    /**
     * Validate credentials are not empty
     */
    protected function validate(): void
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new InvalidCredentialsException('Client ID and client secret cannot be empty');
        }
    }

    /**
     * Get Basic Auth header value
     */
    public function toBasicAuth(): string
    {
        return base64_encode("{$this->clientId}:{$this->clientSecret}");
    }

    /**
     * Get as array for form body
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }
}
