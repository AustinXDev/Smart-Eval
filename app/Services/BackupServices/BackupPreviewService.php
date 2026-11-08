<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use InvalidArgumentException;
use DateTimeImmutable;
use RuntimeException;

/** Read-only validation. This service never inserts or restores database records. */
final class BackupPreviewService
{
    // Leave room for multipart overhead below the existing 3 MiB request limit.
    public const MAX_BYTES = 2 * 1024 * 1024;

    public function __construct(
        private ArchiveReadRepository $archive,
        private BackupEncryptionService $encryption
    ) {
    }

    public function previewUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('The upload failed or exceeded the server upload limit.');
        }

        $name = $file['name'] ?? '';
        $source = $file['tmp_name'] ?? '';

        if (!is_string($name) || !preg_match('/\.json\.enc$/i', $name)
            || !is_string($source) || !is_uploaded_file($source)) {
            throw new InvalidArgumentException('Choose an encrypted SMART-EVAL .json.enc backup.');
        }

        $size = filesize($source);
        if ($size === false || $size <= 36 || $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('Backup files must be no larger than 2 MiB.');
        }

        // A random private directory in the OS temporary area, never an uploaded filename.
        $root = realpath(sys_get_temp_dir());
        $webRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        if (!$root || ($webRoot && $this->within($root, $webRoot))) {
            throw new RuntimeException('Private upload storage is unavailable.');
        }

        $directory = $root . DIRECTORY_SEPARATOR . 'smarteval-preview-' . bin2hex(random_bytes(16));

        if (!mkdir($directory, 0700)) {
            throw new RuntimeException('Private upload storage is unavailable.');
        }

        $encrypted = $directory . '/backup.enc';
        $plaintext = $directory . '/backup.json';

        try {

            if (!move_uploaded_file($source, $encrypted)) {
                throw new RuntimeException('Unable to store the uploaded backup.');
            }

            chmod($encrypted, 0600);

            try {

                $this->encryption->decryptFile($encrypted, $plaintext);

            } catch (RuntimeException $e) {

                error_log('[Archive preview decryption] ' . $e->getMessage());
                throw new InvalidArgumentException('The backup is damaged, unsupported, or encrypted with a different key.');

            }

            try {

                $payload = json_decode(file_get_contents($plaintext), true, 64, JSON_THROW_ON_ERROR);

            } catch (\JsonException $e) {

                throw new InvalidArgumentException('The decrypted backup is not valid JSON.');

            }

            if (!is_array($payload)) {
                throw new InvalidArgumentException('Invalid backup document.');
            }

            $checksum = hash_file('sha256', $encrypted);

            if ($checksum === false) {
                throw new RuntimeException(
                    'Unable to calculate backup checksum.'
                );
            }

            $preview = self::validatePayload(
                $payload,
                $this->archive->backupSchema()
            );

            $preview['filename'] = basename(
                str_replace('\\', '/', $name)
            );

            $preview['existing_period'] =
                $this->archive->periodExists(
                    $preview['period_id']
                );

            $eligibility = $this->archive->getRecoveryEligibility(
                $preview['period_id'],
                $checksum
            );

            $preview['restore_enabled'] =
                !$preview['existing_period']
                && $eligibility['eligible'];

            $preview['notice'] = $preview['existing_period']
                ? 'This evaluation period already exists in the archive database.'
                : $eligibility['reason'];

            return $preview;

        } finally {

            foreach ([$plaintext, $encrypted] as $path) {
                if (is_file($path) && !unlink($path)) {
                    error_log('[Archive preview] Temporary file cleanup failed.');
                }
            }

            if (is_dir($directory) && !rmdir($directory)) {
                error_log('[Archive preview] Temporary directory cleanup failed.');
            }

        }
    }

    private function within(string $path, string $root): bool
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (DIRECTORY_SEPARATOR === '\\') {
            $path = strtolower($path);
            $root = strtolower($root);
        }
        return $path === $root || str_starts_with($path, $root . '/');
    }

    /** Also used by offline tests; accepts only the established version-one export. */
    public static function validatePayload(array $payload, array $schema): array
    {

        $manifest = $payload['manifest'] ?? null;

        $tables = $payload['tables'] ?? null;

        if (!is_array($manifest) || !is_array($tables)
            || ($manifest['format'] ?? null) !== 'smart-eval-period-backup'
            || ($manifest['version'] ?? null) !== 1) {
            throw new InvalidArgumentException('Unsupported backup format or version.');
        }

        $periodId = filter_var($manifest['period_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$periodId || $periodId < 1) {
            throw new InvalidArgumentException('Invalid backup period ID.');
        }

        $date = $manifest['exported_at'] ?? null;

        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $date)) {

            throw new InvalidArgumentException('Invalid backup export date.');

        }

        try {

            new DateTimeImmutable($date);
            $errors = DateTimeImmutable::getLastErrors();
            if ($errors && ($errors['warning_count'] || $errors['error_count'])) {
                throw new \Exception();
            }

        } catch (\Exception $e) {

            throw new InvalidArgumentException('Invalid backup export date.');

        }

        $counts = $manifest['record_counts'] ?? null;

        if (!is_array($counts)) {
            throw new InvalidArgumentException('Missing manifest record counts.');
        }

        $required = DatabaseExportService::TABLES;

        foreach ([$tables, $counts] as $set) {
            if (array_diff($required, array_keys($set)) || array_diff(array_keys($set), $required)) {
                throw new InvalidArgumentException('The backup must contain exactly the 11 supported tables.');
            }
        }

        foreach ($required as $table) {

            $rows = $tables[$table];

            if (!is_array($rows) || !array_is_list($rows) || !is_int($counts[$table]) || $counts[$table] !== count($rows)) {
                throw new InvalidArgumentException("Invalid rows or record count for $table.");
            }

            $columns = $schema[$table]['columns'] ?? [];

            if (!$columns) {
                throw new RuntimeException('Archive schema is unavailable.');
            }
            $fields = array_column($columns, 'Field');

            foreach ($rows as $row) {

                if (!is_array($row) || array_diff($fields, array_keys($row)) || array_diff(array_keys($row), $fields)) {
                    throw new InvalidArgumentException("Backup columns do not match the archive schema for $table.");
                }

                if ((string) ($row['period_id'] ?? '') !== (string) $periodId) {
                    throw new InvalidArgumentException("Mismatched period reference in $table.");
                }

                foreach ($columns as $column) {
                    self::validateValue($row[$column['Field']], $column, $table);
                }

            }

            // Reject duplicate primary and unique keys within the uploaded backup.
            $indexes = [];
            foreach ($schema[$table]['indexes'] ?? [] as $index) {
                if ((int) $index['Non_unique'] === 0) {
                    $indexes[$index['Key_name']][(int) $index['Seq_in_index']] = $index['Column_name'];
                }
            }

            foreach ($indexes as $fields) {
                ksort($fields);
                $seen = [];
                foreach ($rows as $row) {
                    $values = array_map(static fn ($field) => $row[$field], $fields);
                    if (in_array(null, $values, true)) {
                        continue;
                    }
                    $key = json_encode(array_map('strval', array_values($values)));
                    if (isset($seen[$key])) {
                        throw new InvalidArgumentException("Duplicate record key in $table.");
                    }
                    $seen[$key] = true;
                }
            }

        }

        if (count($tables['evaluation_periods']) !== 1) {
            throw new InvalidArgumentException('Exactly one evaluation period is required.');
        }

        $period = $tables['evaluation_periods'][0];

        if ((int) $period['is_closed'] !== 1 || !in_array($period['target_dept'], ['college', 'shs'], true)) {

            throw new InvalidArgumentException('The backup must describe a closed College or SHS evaluation period.');

        }

        // All rows have already been constrained to this one period.
        $maps = [];

        foreach ([
            'student_snapshots' => 'student_pk',
            'teacher_snapshots' => 'teacher_id',
            'program_snapshots' => 'program_id',
            'question_set_snapshots' => 'set_id',
            'question_snapshots' => 'question_id',
            'evaluation_status' => 'eval_id',
        ] as $table => $key) {
            $maps[$table] = [];
            foreach ($tables[$table] as $row) {
                $maps[$table][(string) $row[$key]] = $row;
            }
        }

        $relations = [
            ['evaluation_periods', 'set_id', 'question_set_snapshots'],
            ['question_snapshots', 'set_id', 'question_set_snapshots'],
            ['student_snapshots', 'program_id', 'program_snapshots'],
            ['evaluation_status', 'student_pk', 'student_snapshots'],
            ['evaluation_status', 'teacher_id', 'teacher_snapshots'],
            ['evaluation_answers', 'eval_id', 'evaluation_status'],
            ['evaluation_answers', 'question_id', 'question_snapshots'],
            ['teacher_notification_queue', 'teacher_id', 'teacher_snapshots'],
        ];

        foreach ($relations as [$table, $key, $parent]) {
            foreach ($tables[$table] as $row) {
                if ($row[$key] !== null && !isset($maps[$parent][(string) $row[$key]])) {
                    throw new InvalidArgumentException("Unresolved $key reference in $table.");
                }
            }
        }

        foreach ($tables['evaluation_status'] as $row) {
            if ($row['student_pk'] !== null
                && (string) $maps['student_snapshots'][(string) $row['student_pk']]['student_id'] !== (string) $row['student_id']) {
                throw new InvalidArgumentException('An evaluation student identifier does not match its snapshot.');
            }
        }

        return [
            'period_id' => (int) $periodId,
            'academic_year' => $period['academic_year'],
            'semester' => $period['semester'],
            'department' => $period['target_dept'],
            'exported_at' => $date,
            'students' => $counts['student_snapshots'],
            'teachers' => $counts['teacher_snapshots'],
            'submissions' => count(array_filter($tables['evaluation_status'], static fn ($r) => (int) $r['is_submitted'] === 1)),
            'answers' => $counts['evaluation_answers'],
            'total_records' => array_sum($counts),
            'record_counts' => $counts,
        ];

    }

    private static function validateValue(mixed $value, array $column, string $table): void
    {
        $field = $column['Field'];
        $type = strtolower($column['Type']);

        $invalid = static function () use ($table, $field): never {
            throw new InvalidArgumentException("Invalid value for $table.$field.");
        };

        if ($value === null) {
            if ($column['Null'] !== 'YES') {
                $invalid();
            }
            return;
        }

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $invalid();
        }

        if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)\b/', $type, $match)) {

            if (!preg_match('/^-?\d+$/D', (string) $value)) {
                $invalid();
            }
            $bits = ['tinyint' => 8, 'smallint' => 16, 'mediumint' => 24, 'int' => 32, 'bigint' => 64][$match[1]];
            $unsigned = str_contains($type, 'unsigned');
            $min = $unsigned ? 0 : -(2 ** ($bits - 1));
            $max = $unsigned ? 2 ** $bits - 1 : 2 ** ($bits - 1) - 1;
            if ((float) $value < $min || (float) $value > $max) {
                $invalid();
            }

        } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $type, $match)) {

            if (!preg_match('/^-?\d+(?:\.\d+)?$/D', (string) $value)) {
                $invalid();
            }
            $parts = explode('.', ltrim((string) $value, '-'));

            if (strlen(ltrim($parts[0], '0')) > (int) $match[1] - (int) $match[2]
                || strlen($parts[1] ?? '') > (int) $match[2]) {
                $invalid();
            }

        } elseif (preg_match('/^(?:var)?char\((\d+)\)/', $type, $match)) {

            if (!is_string($value) || preg_match_all('/./us', $value) > (int) $match[1]) {
                $invalid();
            }

        } elseif (str_starts_with($type, 'enum(')) {

            $allowed = str_getcsv(substr($column['Type'], 5, -1), ',', "'", '\\');
            if (!in_array($value, $allowed, true)) {
                $invalid();
            }

        } elseif (in_array($type, ['datetime', 'timestamp', 'date'], true)) {

            $format = $type === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
            if (!is_string($value)) {
                $invalid();
            }

            $parsed = DateTimeImmutable::createFromFormat('!' . $format, $value);
            if (!$parsed || $parsed->format($format) !== $value) {
                $invalid();
            }

        } elseif (str_contains($type, 'text') && !is_string($value)) {

            $invalid();

        }
    }
}
