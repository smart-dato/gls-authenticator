<?php

namespace SmartDato\GlsAuthenticator\Exceptions;

class MissingCredentialsException extends GlsAuthenticationException
{
    public function __construct(
        string $message = 'No GLS API credentials provided',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
