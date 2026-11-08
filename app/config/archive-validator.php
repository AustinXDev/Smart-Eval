<?php

declare(strict_types=1);

use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Services\BackupServices\BackupPreviewService;

if (!isset($archiveReadRepo) || !$archiveReadRepo instanceof ArchiveReadRepository) {

    throw new RuntimeException(
        'Archive read repository is unavailable.'
    );

}

$schema = $archiveReadRepo->backupSchema();

return static function (array $payload) use ($schema): void {

    BackupPreviewService::validatePayload(
        $payload,
        $schema
    );

};
