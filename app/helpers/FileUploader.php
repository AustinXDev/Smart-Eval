<?php

namespace App\helpers;

use RuntimeException;

use function Safe\imagedestroy;

class FileUploader
{
    private const MAX_SIZE = 2 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];

    private const WEBP_QUALITY = 80;

    public function __construct(
        private string $uploadDirectory
    ) {
        $this->ensureDirectory();
    }

    /**
     * Upload an image.
     */
    public function upload(array $file): string
    {
        $this->validateUpload($file);

        $mimeType = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new RuntimeException(
                'Only JPG and PNG files are allowed.'
            );
        }

        $image = $this->createImage(
            $file['tmp_name'],
            $mimeType
        );

        $filename = bin2hex(random_bytes(16))  . '.webp';

        $target = $this->buildPath($filename);

        if (!imagewebp(
            $image,
            $target,
            self::WEBP_QUALITY
        )) {
            imagedestroy($image);

            throw new RuntimeException(
                'Failed to process image.'
            );
        }

        imagedestroy($image);

        return $filename;
    }


    /**
     * Create GD image resource from uploaded file.
     */
    private function createImage(
        string $path,
        string $mimeType
    ) {
        return match ($mimeType) {

            'image/jpeg' => imagecreatefromjpeg($path),

            'image/png' => $this->createPngImage($path),

            default => throw new RuntimeException(
                'Unsupported image type.'
            ),
        };
    }

    /**
     * Create PNG image while preserving transparency.
     */
    private function createPngImage(string $path)
    {
        $image = imagecreatefrompng($path);

        if ($image === false) {
            throw new RuntimeException(
                'Unable to read PNG image.'
            );
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Delete an uploaded image.
     */
    public function delete(string $fileName): void
    {
        if (
            $fileName === '' ||
            $fileName === 'default_teacher.webp' ||
            $fileName === 'default_teacher.png'
        ) {
            return;
        }

        // Prevent path traversal
        $fileName = basename($fileName);

        $path = $this->buildPath($fileName);

        if (is_file($path) && !unlink($path)) {
            if (!unlink($path)) {
                throw new RuntimeException(
                    'Failed to delete old image.'
                );
            }
        }
    }

    /**
     * Check if an image exists.
     */
    public function exists(string $fileName): bool
    {
        if ($fileName === '') {
            return false;
        }

        return is_file(
            $this->buildPath(basename($fileName))
        );
    }

    /**
     * Validate uploaded file.
     */
    private function validateUpload(array $file): void
    {
        if (
            !isset($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            throw new RuntimeException(
                'Invalid uploaded file.'
            );
        }

        if (
            !isset($file['error']) ||
            $file['error'] !== UPLOAD_ERR_OK
        ) {
            throw new RuntimeException(
                'File upload failed.'
            );
        }

        if ($file['size'] > self::MAX_SIZE) {
            throw new RuntimeException(
                'Image must be less than 2MB.'
            );
        }
    }

    /**
     * Ensure upload directory exists.
     */
    private function ensureDirectory(): void
    {
        if (is_dir($this->uploadDirectory)) {
            return;
        }

        if (
            !mkdir(
                $this->uploadDirectory,
                0755,
                true
            ) &&
            !is_dir($this->uploadDirectory)
        ) {
            throw new RuntimeException(
                'Unable to create upload directory.'
            );
        }
    }

    /**
     * Build absolute file path.
     */
    private function buildPath(string $fileName): string
    {
        return rtrim(
            $this->uploadDirectory,
            DIRECTORY_SEPARATOR
        )
        . DIRECTORY_SEPARATOR
        . $fileName;
    }
}
