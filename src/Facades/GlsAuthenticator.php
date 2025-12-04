<?php

namespace SmartDato\GlsAuthenticator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SmartDato\GlsAuthenticator\GlsAuthenticator
 */
class GlsAuthenticator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SmartDato\GlsAuthenticator\GlsAuthenticator::class;
    }
}
