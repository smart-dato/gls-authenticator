<?php

// config for SmartDato/GlsAuthenticator
return [

    /*
    |--------------------------------------------------------------------------
    | Default Environment
    |--------------------------------------------------------------------------
    |
    | Determines which GLS API environment to use by default.
    | Options: 'sandbox', 'production'
    |
    */
    'environment' => env('GLS_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Default OAuth2 client credentials for GLS API authentication.
    | These can be overridden at runtime for multi-tenant scenarios.
    | Set to null to require runtime configuration.
    |
    */
    'client_id' => env('GLS_CLIENT_ID'),
    'client_secret' => env('GLS_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | GLS API base URLs for different environments.
    | You can override these to use custom endpoints if needed.
    |
    */
    'endpoints' => [
        'sandbox' => env('GLS_SANDBOX_ENDPOINT', 'https://api-sandbox.gls-group.net/oauth2/v2'),
        'production' => env('GLS_PRODUCTION_ENDPOINT', 'https://api.gls-group.net/oauth2/v2'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Scopes
    |--------------------------------------------------------------------------
    |
    | Default scopes to request when obtaining an access token.
    | If empty, the token will contain all available scopes for the client.
    |
    */
    'scopes' => env('GLS_SCOPES', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching OAuth2 access tokens.
    |
    */
    'cache' => [
        // Cache store to use (null = default Laravel cache driver)
        'store' => env('GLS_CACHE_STORE'),

        // Cache key prefix to avoid collisions
        'prefix' => env('GLS_CACHE_PREFIX', 'gls_auth'),

        // Buffer time in seconds before token expiration to refresh
        // Tokens are valid for 4 hours (14400s), cache expires 5 minutes earlier
        'expiration_buffer' => env('GLS_CACHE_EXPIRATION_BUFFER', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client settings for API requests.
    |
    */
    'http' => [
        // Request timeout in seconds
        'timeout' => env('GLS_HTTP_TIMEOUT', 30),

        // Number of retry attempts on failure
        'retry' => [
            'times' => env('GLS_HTTP_RETRY_TIMES', 3),
            'sleep' => env('GLS_HTTP_RETRY_SLEEP', 100), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Method
    |--------------------------------------------------------------------------
    |
    | Method for sending credentials to the API.
    | Options: 'basic_auth', 'body'
    | - 'basic_auth': Sends credentials via Authorization header (recommended)
    | - 'body': Sends credentials in request body as form parameters
    |
    */
    'auth_method' => env('GLS_AUTH_METHOD', 'basic_auth'),

];
