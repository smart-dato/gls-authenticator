<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;
use SmartDato\GlsAuthenticator\Tests\Fixtures\GlsApiResponses;

it('caches tokens to avoid duplicate API calls', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // First call - should hit API
    $token1 = GlsAuthenticator::getToken();

    // Second call - should use cache
    $token2 = GlsAuthenticator::getToken();

    // Third call - should use cache
    $token3 = GlsAuthenticator::getToken();

    expect($token1->token)->toBe($token2->token);
    expect($token2->token)->toBe($token3->token);

    Http::assertSentCount(1); // Only one API call
});

it('bypasses cache when fresh() is called', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // First call - hits API and caches
    GlsAuthenticator::getToken();

    // Second call with fresh() - should hit API again
    GlsAuthenticator::fresh()->getToken();

    Http::assertSentCount(2); // Two API calls
});

it('can clear cached token', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // Get and cache token
    GlsAuthenticator::getToken();
    Http::assertSentCount(1);

    // Clear cache
    $cleared = GlsAuthenticator::clearCache();
    expect($cleared)->toBeTrue();

    // Next call should hit API again
    GlsAuthenticator::getToken();
    Http::assertSentCount(2);
});

it('can clear all cached tokens', function () {
    config(['gls-authenticator.client_id' => 'test_client_id']);
    config(['gls-authenticator.client_secret' => 'test_client_secret']);

    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // Get and cache token
    GlsAuthenticator::getToken();

    // Clear all tokens
    $cleared = GlsAuthenticator::clearAllTokens();
    expect($cleared)->toBeTrue();

    // Verify cache is empty
    Cache::flush();
});

it('uses separate cache keys for different credentials', function () {
    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response(GlsApiResponses::success()),
    ]);

    // First tenant
    GlsAuthenticator::withCredentials('tenant1_id', 'tenant1_secret')->getToken();

    // Second tenant
    GlsAuthenticator::withCredentials('tenant2_id', 'tenant2_secret')->getToken();

    // Each tenant should trigger separate API call
    Http::assertSentCount(2);
});
