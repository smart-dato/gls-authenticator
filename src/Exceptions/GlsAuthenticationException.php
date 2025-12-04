<?php

namespace SmartDato\GlsAuthenticator\Exceptions;

use Exception;

class GlsAuthenticationException extends Exception
{
    /**
     * @return static
     */
    public static function create(string $message, ?\Throwable $previous = null): self
    {
        return new static($message, 0, $previous);
    }
}
