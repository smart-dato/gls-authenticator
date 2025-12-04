<?php

namespace SmartDato\GlsAuthenticator;

use Illuminate\Support\Facades\Cache;
use SmartDato\GlsAuthenticator\Contracts\TokenRepositoryInterface;
use SmartDato\GlsAuthenticator\Repositories\CacheTokenRepository;
use SmartDato\GlsAuthenticator\Services\GlsApiClient;
use SmartDato\GlsAuthenticator\Services\TokenService;
use SmartDato\GlsAuthenticator\ValueObjects\Credentials;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GlsAuthenticatorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('gls-authenticator')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        // Bind the token repository
        $this->app->singleton(TokenRepositoryInterface::class, function ($app) {
            $config = config('gls-authenticator.cache');
            $cache = Cache::store($config['store']);

            return new CacheTokenRepository(
                $cache,
                $config['prefix'],
                $config['expiration_buffer']
            );
        });

        // Bind the API client
        $this->app->singleton(GlsApiClient::class, function ($app) {
            return new GlsApiClient(config('gls-authenticator'));
        });

        // Bind the token service
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService(
                $app->make(GlsApiClient::class),
                $app->make(TokenRepositoryInterface::class)
            );
        });

        // Bind the main authenticator (not singleton for runtime config)
        $this->app->bind(GlsAuthenticator::class, function ($app) {
            $config = config('gls-authenticator');

            $defaultCredentials = Credentials::fromConfig($config);
            $defaultEnvironment = $config['environment'];
            $defaultScopes = ! empty($config['scopes'])
                ? explode(' ', $config['scopes'])
                : [];

            return new GlsAuthenticator(
                $app->make(TokenService::class),
                $defaultCredentials,
                $defaultEnvironment,
                $defaultScopes,
                $config['auth_method']
            );
        });
    }
}
