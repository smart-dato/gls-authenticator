<?php

use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;
use SmartDato\GlsAuthenticator\Tests\Fixtures\GlsApiResponses;

it('requests specific scopes as array', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::scopes(['scope1', 'scope2'])->getToken();

    Http::assertSent(function ($request) {
        return $request['scope'] === 'scope1 scope2';
    });
});

it('requests specific scopes as string', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::scopes('scope1 scope2 scope3')->getToken();

    Http::assertSent(function ($request) {
        return $request['scope'] === 'scope1 scope2 scope3';
    });
});

it('caches tokens separately per scope', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // Request with scope1
    GlsAuthenticator::scopes(['scope1'])->getToken();

    // Request with scope2 - different cache key
    GlsAuthenticator::scopes(['scope2'])->getToken();

    // Request with scope1 again - should use cache
    GlsAuthenticator::scopes(['scope1'])->getToken();

    // Should have made 2 API calls (one for each unique scope)
    Http::assertSentCount(2);
});

it('omits scope parameter when no scopes specified', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::getToken();

    Http::assertSent(function ($request) {
        return ! isset($request['scope']);
    });
});

it('uses default scopes from configuration', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);
    config(['gls-authenticator.scopes' => 'default1 default2']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::getToken();

    Http::assertSent(function ($request) {
        return $request['scope'] === 'default1 default2';
    });
});

it('runtime scopes override default scopes', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);
    config(['gls-authenticator.scopes' => 'default1 default2']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::scopes(['runtime1'])->getToken();

    Http::assertSent(function ($request) {
        return $request['scope'] === 'runtime1';
    });
});
