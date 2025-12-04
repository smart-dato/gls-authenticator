<?php

use SmartDato\GlsAuthenticator\GlsAuthenticator;
use SmartDato\GlsAuthenticator\ValueObjects\Credentials;

it('playgound', function () {
    $authenticator = new GlsAuthenticator(
        defaultCredentials: new Credentials(
            clientId: 'my-client-id',
            clientSecret: 'my-client-secret'
        ),
        defaultEnvironment: 'sandbox'
    );

    // Get and cache token
    $token = $authenticator->getToken();
    dump($token);
})->todo('This is just a playground test');
