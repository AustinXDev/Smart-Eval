<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use Google\Client;
use RuntimeException;

class GoogleOAuthService
{
    private Client $client;

    public function __construct()
    {

        $this->client = new Client();

        $this->client->setClientId(
            $_ENV['GOOGLE_CLIENT_ID']
        );

        $this->client->setClientSecret(
            $_ENV['GOOGLE_CLIENT_SECRET']
        );

        $this->client->setRedirectUri(
            $_ENV['GOOGLE_REDIRECT_URI']
        );

        $this->client->setScopes([
            'https://www.googleapis.com/auth/drive.file'
        ]);

        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

    }

    public function getAuthorizationURL(
        string $state
    ): string {

        $this->client->setState($state);

        return $this->client->createAuthUrl();

    }

    public function exchangeCode(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new RuntimeException(
                'Google OAuth authorization failed.'
            );
        }

        if (empty($token['refresh_token'])) {
            throw new RuntimeException(
                'No refresh token was returned.'
            );
        }

        return $token;
    }

}
