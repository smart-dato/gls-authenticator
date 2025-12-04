# GLS Authentication API v2

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smart-dato/gls-authenticator.svg?style=flat-square)](https://packagist.org/packages/smart-dato/gls-authenticator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/gls-authenticator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/smart-dato/gls-authenticator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/gls-authenticator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/smart-dato/gls-authenticator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smart-dato/gls-authenticator.svg?style=flat-square)](https://packagist.org/packages/smart-dato/gls-authenticator)

A Laravel package for authenticating with the GLS Group Authentication API v2. This package provides a fluent, cache-enabled interface for obtaining OAuth 2.0 access tokens using the client credentials grant type.

## Features

- 🔐 **OAuth 2.0 Authentication** - Client credentials grant type
- 💾 **Automatic Token Caching** - Tokens cached until expiration (4 hours)
- 🔄 **Token Refresh Logic** - Automatic detection and refresh of expired tokens
- 🎯 **Scope Management** - Request tokens with specific OAuth scopes
- 🏢 **Multi-Tenant Support** - Runtime configuration for different credentials
- 🌍 **Environment Switching** - Support for sandbox and production environments
- 🚀 **Fluent API** - Chainable methods for clean, readable code
- ✅ **Fully Tested** - Comprehensive test coverage with Pest PHP

## Requirements

- PHP 8.4 or higher
- Laravel 11.0 or 12.0

## Installation

You can install the package via composer:

```bash
composer require smart-dato/gls-authenticator
```

Publish the config file with:

```bash
php artisan vendor:publish --tag="gls-authenticator-config"
```

## Configuration

Add your GLS API credentials to your `.env` file:

```env
GLS_CLIENT_ID=your_client_id
GLS_CLIENT_SECRET=your_client_secret
GLS_ENVIRONMENT=sandbox  # or 'production'

# Optional
GLS_SCOPES="parcel.create parcel.read"
GLS_CACHE_STORE=redis
GLS_CACHE_PREFIX=gls_auth
```

The published config file (`config/gls-authenticator.php`) includes:

```php
return [
    // Default environment: 'sandbox' or 'production'
    'environment' => env('GLS_ENVIRONMENT', 'sandbox'),

    // OAuth2 client credentials
    'client_id' => env('GLS_CLIENT_ID'),
    'client_secret' => env('GLS_CLIENT_SECRET'),

    // API endpoints
    'endpoints' => [
        'sandbox' => 'https://api-sandbox.gls-group.net/oauth2/v2',
        'production' => 'https://api.gls-group.net/oauth2/v2',
    ],

    // Default scopes (space-separated)
    'scopes' => env('GLS_SCOPES', ''),

    // Cache configuration
    'cache' => [
        'store' => env('GLS_CACHE_STORE'),
        'prefix' => env('GLS_CACHE_PREFIX', 'gls_auth'),
        'expiration_buffer' => env('GLS_CACHE_EXPIRATION_BUFFER', 300), // 5 minutes
    ],

    // HTTP client settings
    'http' => [
        'timeout' => env('GLS_HTTP_TIMEOUT', 30),
        'retry' => [
            'times' => env('GLS_HTTP_RETRY_TIMES', 3),
            'sleep' => env('GLS_HTTP_RETRY_SLEEP', 100), // milliseconds
        ],
    ],

    // Authentication method: 'basic_auth' or 'body'
    'auth_method' => env('GLS_AUTH_METHOD', 'basic_auth'),
];
```

## Usage

### Basic Usage

Get an access token using default configuration:

```php
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;

$token = GlsAuthenticator::getToken();

// Use the token
$authHeader = $token->toAuthorizationHeader(); // "Bearer eyJ..."
```

### Token Properties

```php
$token = GlsAuthenticator::getToken();

echo $token->token;          // The JWT access token
echo $token->tokenType;      // "Bearer"
echo $token->expiresIn;      // 14400 (4 hours in seconds)
echo $token->expiresAt();    // DateTimeInterface
echo $token->isExpired();    // false
echo $token->secondsUntilExpiration(); // 14399
```

### Multi-Tenant / Runtime Configuration

Perfect for applications serving multiple clients:

```php
// Each tenant has their own credentials
$tenant = Tenant::current();

$token = GlsAuthenticator::withCredentials(
    $tenant->gls_client_id,
    $tenant->gls_client_secret
)->getToken();
```

### Environment Switching

Switch between sandbox and production:

```php
// Use sandbox
$token = GlsAuthenticator::environment('sandbox')->getToken();

// Use production
$token = GlsAuthenticator::environment('production')->getToken();
```

### Scope Management

Request specific OAuth scopes:

```php
// Array of scopes
$token = GlsAuthenticator::scopes(['parcel.create', 'parcel.read'])->getToken();

// Space-separated string
$token = GlsAuthenticator::scopes('parcel.create parcel.read')->getToken();
```

### Fluent API Chaining

Combine multiple configuration options:

```php
$token = GlsAuthenticator::withCredentials($clientId, $clientSecret)
    ->environment('production')
    ->scopes(['parcel.create', 'parcel.read'])
    ->getToken();
```

### Force Fresh Token

Bypass cache and get a fresh token from the API:

```php
$token = GlsAuthenticator::fresh()->getToken();
```

### Cache Management

```php
// Clear token for current credentials
GlsAuthenticator::clearCache();

// Clear all cached tokens
GlsAuthenticator::clearAllTokens();
```

### Check Configuration

```php
if (GlsAuthenticator::isConfigured()) {
    $token = GlsAuthenticator::getToken();
} else {
    // Prompt user to configure credentials
}
```

## Token Caching

Tokens are automatically cached to minimize API calls:

- **Cache Duration**: Tokens expire in 4 hours (14,400 seconds)
- **Cache TTL**: Cached for 3 hours 55 minutes (expires 5 minutes early for safety)
- **Multi-Tenant Isolation**: Each unique credential set has a separate cache key
- **Scope-Specific**: Different scopes generate different cache keys

### Cache Key Format

```
{prefix}:{environment}:{client_id_hash}:{scopes_hash}
```

Example: `gls_auth:sandbox:a3f5c8d2:e9b4f1a6`

## Error Handling

The package throws specific exceptions for different error scenarios:

```php
use SmartDato\GlsAuthenticator\Exceptions\MissingCredentialsException;
use SmartDato\GlsAuthenticator\Exceptions\InvalidCredentialsException;
use SmartDato\GlsAuthenticator\Exceptions\TokenRequestException;

try {
    $token = GlsAuthenticator::getToken();
} catch (MissingCredentialsException $e) {
    // No credentials configured
    logger()->error('GLS credentials not configured');
} catch (InvalidCredentialsException $e) {
    // Invalid client_id or client_secret
    logger()->error('Invalid GLS credentials', ['errors' => $e->errors]);
} catch (TokenRequestException $e) {
    // API request failed (network error, server error, etc.)
    logger()->error('GLS API request failed', ['message' => $e->getMessage()]);
}
```

## Use Cases

### Making API Calls

```php
use Illuminate\Support\Facades\Http;

$token = GlsAuthenticator::getToken();

$response = Http::withToken($token->token)
    ->post('https://api.gls-group.net/parcel/v1/shipments', [
        // Your shipment data
    ]);
```

### Middleware for Multi-Tenant Apps

```php
namespace App\Http\Middleware;

use Closure;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;

class SetGlsCredentials
{
    public function handle($request, Closure $next)
    {
        $tenant = $request->user()->tenant;

        // Store token for later use
        $request->attributes->set('gls_token',
            GlsAuthenticator::withCredentials(
                $tenant->gls_client_id,
                $tenant->gls_client_secret
            )->getToken()
        );

        return $next($request);
    }
}
```

### Service Class Example

```php
namespace App\Services;

use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;
use Illuminate\Support\Facades\Http;

class GlsParcelService
{
    protected $token;

    public function __construct()
    {
        $this->token = GlsAuthenticator::getToken();
    }

    public function createShipment(array $data)
    {
        return Http::withToken($this->token->token)
            ->post('https://api.gls-group.net/parcel/v1/shipments', $data)
            ->json();
    }

    public function trackParcel(string $parcelNumber)
    {
        return Http::withToken($this->token->token)
            ->get("https://api.gls-group.net/parcel/v1/tracking/{$parcelNumber}")
            ->json();
    }
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Format code with Laravel Pint:

```bash
composer format
```

Run all quality checks:

```bash
composer test && composer analyse && composer format
```

### Testing Your Application

Use Laravel's HTTP facade to mock GLS API responses:

```php
use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Facades\GlsAuthenticator;

test('can authenticate with GLS API', function () {
    Http::fake([
        'api-sandbox.gls-group.net/*' => Http::response([
            'token_type' => 'Bearer',
            'access_token' => 'test_token_123',
            'expires_in' => 14400,
        ]),
    ]);

    $token = GlsAuthenticator::getToken();

    expect($token->token)->toBe('test_token_123');
    expect($token->tokenType)->toBe('Bearer');
});
```

## API Reference

For detailed API specifications, see the [OpenAPI documentation](documentation/authentification-service-v2.yaml).

### Endpoints

- **Sandbox**: `https://api-sandbox.gls-group.net/oauth2/v2`
- **Production**: `https://api.gls-group.net/oauth2/v2`

### Token Details

- **Grant Type**: `client_credentials`
- **Token Type**: `Bearer`
- **Validity**: 4 hours (14,400 seconds)
- **Rate Limit**: 100,000 requests per App ID per day

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [SmartDato](https://github.com/smart-dato)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
