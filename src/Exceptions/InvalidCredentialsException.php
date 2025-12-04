<?php

namespace SmartDato\GlsAuthenticator\Exceptions;

class InvalidCredentialsException extends GlsAuthenticationException
{
    public function __construct(
        string $message = 'Invalid GLS API credentials',
        public readonly ?array $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
