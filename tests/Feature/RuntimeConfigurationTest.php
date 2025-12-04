<?php

use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;
use SmartDato\GlsAuthenticator\Tests\Fixtures\GlsApiResponses;

it('uses runtime credentials over default config', function () {
    config(['gls-authenticator.client_id' => 'default_id']);
    config(['gls-authenticator.client_secret' => 'default_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    GlsAuthenticator::withCredentials('runtime_id', 'runtime_secret')->getToken();

    Http::assertSent(function ($request) {
        $auth = $request->header('Authorization')[0] ?? '';
        $expected = base64_encode('runtime_id:runtime_secret');

        return str_contains($auth, $expected);
    });
});

it('can switch environments dynamically', function () {
    config(['gls-authenticator.client_id' => 'test_id']);
    config(['gls-authenticator.client_secret' => 'test_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
        'api.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // Use sandbox
    GlsAuthenticator::environment('sandbox')->getToken();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api-sandbox.gls-group.net');
    });

    // Use production
    GlsAuthenticator::environment('production')->getToken();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.gls-group.net');
    });
});

it('supports fluent chaining of configuration methods', function () {
    Http::fake([
        'api.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    $token = GlsAuthenticator::withCredentials('test_id', 'test_secret')
        ->environment('production')
        ->scopes(['scope1', 'scope2'])
        ->getToken();

    expect($token)->toBeInstanceOf(\SmartDato\GlsAuthenticator\ValueObjects\AccessToken::class);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.gls-group.net')
            && $request['scope'] === 'scope1 scope2';
    });
});

it('checks if authenticator is configured', function () {
    config(['gls-authenticator.client_id' => null]);
    config(['gls-authenticator.client_secret' => null]);

    expect(GlsAuthenticator::isConfigured())->toBeFalse();

    $configured = GlsAuthenticator::withCredentials('test_id', 'test_secret');
    expect($configured->isConfigured())->toBeTrue();
});

it('can get current credentials', function () {
    config(['gls-authenticator.client_id' => 'default_id']);
    config(['gls-authenticator.client_secret' => 'default_secret']);

    $credentials = GlsAuthenticator::getCredentials();

    expect($credentials)->not->toBeNull();
    expect($credentials->clientId)->toBe('default_id');
});
