<?php

namespace SmartDato\GlsAuthenticator\Tests\Fixtures;

class GlsApiResponses
{
    public static function success(): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => 'eyJraWQiOiIxMzM3IiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYifQ.eyJhY2Nlc3NfdG9rZW4iOiJDR3ZQQm1JUkJzVzVTbDVHUjZIWEdUb29JUUlJIiwic2NwIjoiIiwic3ViIjoiSFF5R1FibXA0RzdaeG91SkZ5cjd0T2twSUNoTTViRloiLCJpc3MiOiJodHRwczpcL1wvYXBpLWRldi5nbHMtZ3JvdXAubmV0XC9vYXV0aDJcL3YxIiwiZXhwIjoxNTk5MDM3NzkzLCJpYXQiOjE1OTkwMzU5OTMsImNsaWVudF9pZCI6IkhReUdRYm1wNEc3WnhvdUpGeXI3dE9rcElDaE01YkZaIiwianRpIjoiMzk3YjZhZGEtNmE2Zi00Y2YyLTk1NjMtMGIzZDhmMjI3NTQxIn0',
            'expires_in' => 14400,
        ];
    }

    public static function invalidCredentials(): array
    {
        return [
            'errors' => [
                [
                    'message' => 'Invalid client identifier',
                    'type' => '400',
                ],
            ],
        ];
    }

    public static function serverError(): array
    {
        return [
            'errors' => [
                [
                    'message' => 'Internal server error',
                    'type' => '500',
                ],
            ],
        ];
    }
}
