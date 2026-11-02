<?php

declare(strict_types=1);

use App\Services\BackupServices\GoogleDriveService;
use App\Services\BackupServices\GoogleTokenStorage;

require_once __DIR__ . '/../app/init.php';

Dotenv\Dotenv::createImmutable(
    dirname(__DIR__)
)->load();

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$testFile = tempnam(
    sys_get_temp_dir(),
    'smart_eval_'
);

if ($testFile === false) {
    throw new RuntimeException(
        'Unable to create temporary test file.'
    );
}

try {
    file_put_contents(
        $testFile,
        'Smart-Eval Google Drive upload test.'
    );

    $drive = new GoogleDriveService(
        new GoogleTokenStorage()
    );

    $fileId = $drive->upload(
        $testFile,
        'smart-eval-upload-test.txt',
        $_ENV['GOOGLE_DRIVE_FOLDER_ID'] ?? null
    );

    echo "Upload successful.\n";
    echo "Google Drive File ID: {$fileId}\n";

} finally {
    if (is_file($testFile)) {
        unlink($testFile);
    }
}
