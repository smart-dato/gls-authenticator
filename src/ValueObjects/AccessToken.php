<?php

namespace SmartDato\GlsAuthenticator\ValueObjects;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class AccessToken
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public int $expiresIn,
        public DateTimeInterface $issuedAt
    ) {}

    /**
     * Create from API response
     */
    public static function fromResponse(array $response): self
    {
        return new self(
            token: $response['access_token'],
            tokenType: $response['token_type'],
            expiresIn: (int) $response['expires_in'],
            issuedAt: new DateTimeImmutable
        );
    }

    /**
     * Get the token expiration timestamp
     */
    public function expiresAt(): DateTimeInterface
    {
        if ($this->issuedAt instanceof DateTimeImmutable) {
            return $this->issuedAt->modify("+{$this->expiresIn} seconds");
        }

        return (clone $this->issuedAt)->modify("+{$this->expiresIn} seconds");
    }

    /**
     * Check if the token is expired
     */
    public function isExpired(int $buffer = 0): bool
    {
        $expirationTime = $this->expiresAt()->getTimestamp() - $buffer;

        return time() >= $expirationTime;
    }

    /**
     * Get seconds until expiration
     */
    public function secondsUntilExpiration(): int
    {
        $seconds = $this->expiresAt()->getTimestamp() - time();

        return max(0, $seconds);
    }

    /**
     * Get the authorization header value
     */
    public function toAuthorizationHeader(): string
    {
        return "{$this->tokenType} {$this->token}";
    }

    /**
     * Convert to array for caching
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'issued_at' => $this->issuedAt->format(DateTime::ATOM),
        ];
    }

    /**
     * Create from cached array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            tokenType: $data['token_type'],
            expiresIn: $data['expires_in'],
            issuedAt: new DateTimeImmutable($data['issued_at'])
        );
    }
}
