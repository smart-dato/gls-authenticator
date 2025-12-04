<?php

namespace SmartDato\GlsAuthenticator\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use SmartDato\GlsAuthenticator\Exceptions\InvalidCredentialsException;
use SmartDato\GlsAuthenticator\Exceptions\TokenRequestException;
use SmartDato\GlsAuthenticator\ValueObjects\AccessToken;
use SmartDato\GlsAuthenticator\ValueObjects\TokenRequest;
use Throwable;

class GlsApiClient
{
    public function __construct(
        protected array $config
    ) {}

    /**
     * Request an access token from GLS API
     */
    public function requestToken(TokenRequest $request): AccessToken
    {
        try {
            $client = $this->buildHttpClient();
            $client = $this->prepareRequest($client, $request);
            $endpoint = $this->getEndpoint($request);

            $formData = ['grant_type' => $request->grantType];

            if ($request->hasScopes()) {
                $formData['scope'] = $request->getScopesString();
            }

            if ($request->authMethod === 'body') {
                $formData = array_merge($formData, $request->credentials->toArray());
            }

            $response = $client->post($endpoint, $formData);

            if ($response->failed()) {
                $response->throw();
            }

            return $this->parseResponse($response->json());
        } catch (Throwable $e) {
            $this->handleError($e, $request);
        }
    }

    /**
     * Get the endpoint URL for the request
     */
    protected function getEndpoint(TokenRequest $request): string
    {
        $baseUrl = $this->config['endpoints'][$request->environment] ?? '';

        if (empty($baseUrl)) {
            throw new TokenRequestException("No endpoint configured for environment: {$request->environment}");
        }

        return "{$baseUrl}/token";
    }

    /**
     * Build the HTTP client with configuration
     */
    protected function buildHttpClient(): PendingRequest
    {
        $timeout = $this->config['http']['timeout'] ?? 30;
        $retryTimes = $this->config['http']['retry']['times'] ?? 3;
        $retrySleep = $this->config['http']['retry']['sleep'] ?? 100;

        return Http::timeout($timeout)
            ->retry($retryTimes, $retrySleep)
            ->asForm();
    }

    /**
     * Prepare the request with authentication
     */
    protected function prepareRequest(PendingRequest $client, TokenRequest $request): PendingRequest
    {
        if ($request->authMethod === 'basic_auth') {
            return $client->withBasicAuth(
                $request->credentials->clientId,
                $request->credentials->clientSecret
            );
        }

        return $client;
    }

    /**
     * Parse the API response into an AccessToken
     */
    protected function parseResponse(array $response): AccessToken
    {
        return AccessToken::fromResponse($response);
    }

    /**
     * Handle API errors and throw appropriate exceptions
     */
    protected function handleError(Throwable $e, TokenRequest $request): never
    {
        if ($e instanceof RequestException) {
            $response = $e->response;

            if ($response->status() === 400) {
                $errors = $response->json('errors');
                throw new InvalidCredentialsException(
                    'Invalid GLS API credentials',
                    $errors,
                    $e
                );
            }

            if ($response->status() >= 500) {
                throw new TokenRequestException(
                    'GLS API server error',
                    $response->json('errors'),
                    $e
                );
            }
        }

        throw new TokenRequestException(
            'Failed to obtain access token: '.$e->getMessage(),
            null,
            $e
        );
    }
}
