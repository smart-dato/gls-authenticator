<?php

namespace SmartDato\GlsAuthenticator\Exceptions;

class InvalidConfigurationException extends GlsAuthenticationException
{
    public function __construct(
        string $message = 'Invalid GLS authenticator configuration',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
