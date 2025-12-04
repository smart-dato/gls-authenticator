<?php

use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;
use SmartDato\GlsAuthenticator\Tests\Fixtures\GlsApiResponses;

it('retrieves and caches a token successfully', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    $token1 = GlsAuthenticator::getToken();
    $token2 = GlsAuthenticator::getToken();

    expect($token1->token)->toBeString();
    expect($token2->token)->toBe($token1->token);
    expect($token1->tokenType)->toBe('Bearer');
    expect($token1->expiresIn)->toBe(14400);

    Http::assertSentCount(1); // Only one API call due to caching
});

it('can get a token with authorization header', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    $token = GlsAuthenticator::getToken();
    $authHeader = $token->toAuthorizationHeader();

    expect($authHeader)->toStartWith('Bearer ');
    expect($authHeader)->toContain($token->token);
});

it('sends basic auth credentials correctly', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);
    config(['gls-authenticator.auth_method' => 'basic_auth']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::getToken();

    Http::assertSent(function ($request) {
        $auth = $request->header('Authorization')[0] ?? '';

        return str_contains($auth, 'Basic ');
    });
});

it('throws exception when credentials are missing', function () {
    config(['gls-authenticator.client_id' => null]);
    config(['gls-authenticator.client_secret' => null]);

    GlsAuthenticator::getToken();
})->throws(\SmartDato\GlsAuthenticator\Exceptions\MissingCredentialsException::class);

it('handles invalid credentials error from API', function () {
    config(['gls-authenticator.client_id' => 'invalid_id']);
    config(['gls-authenticator.client_secret' => 'invalid_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::invalidCredentials(), 400),
    ]);

    GlsAuthenticator::getToken();
})->throws(\SmartDato\GlsAuthenticator\Exceptions\InvalidCredentialsException::class);

it('handles server error from API', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::serverError(), 500),
    ]);

    GlsAuthenticator::getToken();
})->throws(\SmartDato\GlsAuthenticator\Exceptions\TokenRequestException::class);
