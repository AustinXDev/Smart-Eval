<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

final class GoogleDriveService
{
    private Drive $drive;

    public function __construct(
        GoogleTokenStorage $tokenStorage
    ) {
        $client = new Client();

        $client->setClientId(
            $_ENV['GOOGLE_CLIENT_ID']
        );

        $client->setClientSecret(
            $_ENV['GOOGLE_CLIENT_SECRET']
        );

        $client->setScopes([
            Drive::DRIVE_FILE
        ]);

        $client->setAccessType('offline');

        $refreshToken = $tokenStorage->get();

        $token = $client->fetchAccessTokenWithRefreshToken(
            $refreshToken
        );

        if (
            !is_array($token) ||
            isset($token['error']) ||
            empty($token['access_token'])
        ) {
            throw new RuntimeException(
                'Unable to refresh Google access token.'
            );
        }

        $this->drive = new Drive($client);
    }

    public function upload(
        string $filePath,
        string $fileName,
        ?string $folderId = null
    ): string {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException(
                'Backup file does not exist or is unreadable.'
            );
        }

        $metadata = [
            'name' => $fileName
        ];

        if ($folderId !== null && $folderId !== '') {
            $metadata['parents'] = [$folderId];
        }

        $file = new DriveFile($metadata);

        $result = $this->drive->files->create(
            $file,
            [
                'data' => file_get_contents($filePath),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'multipart',
                'fields' => 'id,name,size'
            ]
        );

        $id = $result->getId();

        if (!$id) {
            throw new RuntimeException(
                'Google Drive did not return a file ID.'
            );
        }

        return $id;
    }


    public function download(string $fileId): string
    {
        if (trim($fileId) === '') {
            throw new RuntimeException(
                'Google Drive file ID is required.'
            );
        }

        $response = $this->drive->files->get(
            $fileId,
            ['alt' => 'media']
        );

        if (!$response instanceof \Psr\Http\Message\ResponseInterface) {
            throw new RuntimeException(
                'Unexpected Google Drive download response type: '
                . get_debug_type($response)
            );
        }

        $body = (string) $response->getBody();

        if ($body === '') {
            throw new RuntimeException(
                'Downloaded backup is empty.'
            );
        }

        return $body;
    }

}
