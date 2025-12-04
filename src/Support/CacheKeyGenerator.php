<?php

namespace SmartDato\GlsAuthenticator\Support;

use SmartDato\GlsAuthenticator\ValueObjects\TokenRequest;

class CacheKeyGenerator
{
    /**
     * Generate a cache key from a token request
     */
    public static function generate(TokenRequest $request): string
    {
        $components = [
            $request->environment,
            hash('sha256', $request->credentials->clientId),
        ];

        if ($request->hasScopes()) {
            $components[] = hash('sha256', $request->getScopesString());
        }

        return implode(':', $components);
    }

    /**
     * Generate a hash from request components
     */
    protected static function hash(array $components): string
    {
        return hash('sha256', implode('|', $components));
    }
}
