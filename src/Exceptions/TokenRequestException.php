<?php

namespace SmartDato\GlsAuthenticator\Exceptions;

class TokenRequestException extends GlsAuthenticationException
{
    public function __construct(
        string $message,
        public readonly ?array $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
