# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package that provides OAuth 2.0 authentication integration with the GLS Group Authentication API v2. The package is built using Spatie's Laravel Package Tools and follows Laravel package conventions.

**Package Name:** `smart-dato/gls-authenticator`
**Namespace:** `SmartDato\GlsAuthenticator`
**PHP Version:** ^8.4
**Laravel Versions:** ^11.0 || ^12.0

## API Integration

This package integrates with GLS Group's Authentication API v2 (OAuth 2.0):
- **Sandbox:** `https://api-sandbox.gls-group.net/oauth2/v2`
- **Production:** `https://api.gls-group.net/oauth2/v2`
- **API Spec:** See `documentation/authentification-service-v2.yaml`

The API issues OAuth access tokens using the `client_credentials` grant type. Tokens:
- Are valid for up to 4 hours
- Support scope-based access control
- Accept authentication via Basic Auth header or request body parameters

## Development Commands

### Testing
```bash
composer test              # Run all tests with Pest
composer test-coverage     # Run tests with coverage report
vendor/bin/pest           # Direct Pest execution
vendor/bin/pest --filter TestName  # Run specific test
```

### Code Quality
```bash
composer analyse          # Run PHPStan static analysis (level 5)
composer format           # Format code with Laravel Pint
vendor/bin/pint          # Direct Pint execution
```

### Setup
```bash
composer install                                    # Install dependencies
php artisan vendor:publish --tag="gls-authenticator-config"  # Publish config
```

## Architecture

### Service Provider Pattern
The package uses Spatie's `laravel-package-tools` for streamlined service provider setup:
- **Service Provider:** `GlsAuthenticatorServiceProvider` extends `PackageServiceProvider`
- Automatically registers configuration file `config/gls-authenticator.php`
- No commands, migrations, or views currently configured

### Facade Pattern
- **Facade:** `SmartDato\GlsAuthenticator\Facades\GlsAuthenticator`
- **Underlying Class:** `SmartDato\GlsAuthenticator\GlsAuthenticator`
- Registered automatically via Laravel's package discovery

### Testing Setup
Tests use Orchestra Testbench for Laravel package testing:
- **Base Test Class:** `SmartDato\GlsAuthenticator\Tests\TestCase`
- **Test Framework:** Pest PHP with Laravel plugin
- **PHPUnit Config:** `phpunit.xml.dist`
- **Architecture Tests:** `tests/ArchTest.php` prevents debugging functions (dd, dump, ray)
- All tests inherit from `TestCase` via `uses(TestCase::class)->in(__DIR__)` in `tests/Pest.php`

### Code Quality Standards
- **PHPStan:** Level 5 analysis with Larastan, checks Octane compatibility and model properties
- **Laravel Pint:** Code style formatting (Laravel preset)
- **Architecture Tests:** Enforces no debugging functions in production code

## File Structure

```
src/
├── Facades/
│   └── GlsAuthenticator.php    # Laravel facade
├── GlsAuthenticator.php         # Main service class (currently empty)
└── GlsAuthenticatorServiceProvider.php  # Package service provider

config/
└── gls-authenticator.php        # Package configuration (currently empty)

tests/
├── ArchTest.php                 # Architecture tests
├── ExampleTest.php              # Example test file
├── Pest.php                     # Pest configuration
└── TestCase.php                 # Base test case

documentation/
└── authentification-service-v2.yaml  # OpenAPI 3.0 spec for GLS Auth API
```

## Important Notes

- The package is in early development - main classes are placeholder stubs
- Configuration file is currently empty but can be extended for API credentials
- Uses Orchestra Testbench for isolated Laravel package testing environment
- PHPStan baseline is included (`phpstan-baseline.neon`) for managing existing issues
- Requires PHP 8.4+ (strict version requirement)
