<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use RuntimeException;

final class BackupEncryptionService
{
    public function __construct(
        private string $encryptionKey
    ) {
        if (strlen($this->encryptionKey) !== 32) {
            throw new RuntimeException(
                'Encryption key must be exactly 32 bytes.'
            );
        }
    }

    public function encryptFile(string $sourcePath): array
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException(
                'Source backup file not found.'
            );
        }

        $plaintext = file_get_contents($sourcePath);

        if ($plaintext === false) {
            throw new RuntimeException(
                'Unable to read backup file.'
            );
        }

        $iv = random_bytes(12);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new RuntimeException(
                'Backup encryption failed.'
            );
        }

        // File layout:
        // MAGIC (8 bytes) + IV (12 bytes)
        // + TAG (16 bytes) + CIPHERTEXT
        $encryptedData =
            'SMARTEV1' .
            $iv .
            $tag .
            $ciphertext;

        $encryptedPath = $sourcePath . '.enc';

        $written = file_put_contents(
            $encryptedPath,
            $encryptedData,
            LOCK_EX
        );

        if (
            $written === false ||
            $written !== strlen($encryptedData)
        ) {
            throw new RuntimeException(
                'Unable to write encrypted backup.'
            );
        }

        @chmod($encryptedPath, 0600);

        $checksum = hash_file(
            'sha256',
            $encryptedPath
        );

        if ($checksum === false) {
            throw new RuntimeException(
                'Unable to calculate encrypted checksum.'
            );
        }

        return [
            'file_path' => $encryptedPath,
            'file_name' => basename($encryptedPath),
            'checksum_sha256' => $checksum,
            'file_size' => filesize($encryptedPath),
        ];
    }

    public function decryptFile(
        string $encryptedPath,
        string $outputPath
    ): void {
        $data = file_get_contents($encryptedPath);

        if ($data === false || strlen($data) < 36) {
            throw new RuntimeException(
                'Invalid encrypted backup file.'
            );
        }

        if (substr($data, 0, 8) !== 'SMARTEV1') {
            throw new RuntimeException(
                'Unsupported backup format.'
            );
        }

        $iv = substr($data, 8, 12);
        $tag = substr($data, 20, 16);
        $ciphertext = substr($data, 36);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException(
                'Backup decryption failed.'
            );
        }

        $written = file_put_contents(
            $outputPath,
            $plaintext,
            LOCK_EX
        );

        if (
            $written === false ||
            $written !== strlen($plaintext)
        ) {
            throw new RuntimeException(
                'Unable to write decrypted backup.'
            );
        }

        @chmod($outputPath, 0600);
    }
}
