<?php

declare(strict_types=1);

namespace App\Services\LoggingServices;

use App\Repositories\LogRepo\ActivityLogRepository;

/**
 * Records one activity log entry.
 *
 *   $logger->log('teachers', 'create', 'Added teacher Maria Santos (00-0012)',
 *       targetType: 'teacher', targetId: '21', department: 'college');
 *
 * The actor (admin, student, or system for CLI/cron) is taken from the
 * session unless one is passed. Logging never throws: a failure is written
 * to the PHP error log so it can never break the action being logged.
 *
 * Privacy: callers must not pass passwords, tokens, codes, or anything that
 * links a student to a teacher they evaluated (scores, answers, comments).
 * Keys that look sensitive are removed from metadata as a safety net.
 */
class ActivityLogService
{
    // Secrets (passwords, tokens, one-time codes) and anything that could
    // expose an evaluation (answers, scores, comments, ratings).
    private const SENSITIVE_KEY = '/password|passwd|token|secret|csrf|^code$|^otp$|_otp$|verification_code|two_factor_code|answer|score|comment|rating/i';

    public function __construct(private ActivityLogRepository $logs)
    {
    }

    /**
     * @param array{type:string, id?:?string, name?:?string, role?:?string}|null $actor
     */
    public function log(
        string $module,
        string $action,
        string $description,
        ?string $targetType = null,
        ?string $targetId = null,
        ?string $department = null,
        array $metadata = [],
        ?array $actor = null,
    ): void {
        try {
            $actor ??= $this->currentActor();

            $this->logs->create([
                'actor_type'  => $actor['type'],
                'actor_id'    => self::clip($actor['id'] ?? null, 20),
                'actor_name'  => self::clip($actor['name'] ?? null, 100),
                'actor_role'  => self::clip($actor['role'] ?? null, 30),
                'module'      => self::clip($module, 40),
                'action'      => self::clip($action, 40),
                'description' => self::clip($description, 255) ?? '',
                'target_type' => self::clip($targetType, 40),
                'target_id'   => self::clip($targetId, 40),
                'department'  => self::department($department),
                'metadata'    => self::metadata($metadata),
                'ip_address'  => self::clip(PHP_SAPI === 'cli' ? null : ($_SERVER['REMOTE_ADDR'] ?? null), 45),
                'user_agent'  => self::clip(PHP_SAPI === 'cli' ? 'cli' : ($_SERVER['HTTP_USER_AGENT'] ?? null), 255),
            ]);
        } catch (\Throwable $e) {
            error_log('[ActivityLog] ' . get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * Who is acting in the current request.
     *
     * @return array{type:string, id:?string, name:?string, role:?string}
     */
    public function currentActor(): array
    {
        if (PHP_SAPI === 'cli') {
            return ['type' => 'system', 'id' => null, 'name' => 'System scheduler', 'role' => null];
        }

        $session = session_status() === PHP_SESSION_ACTIVE ? $_SESSION : [];

        if (($session['admin_authenticated'] ?? false) === true && !empty($session['admin_id'])) {
            return $this->adminActor((int) $session['admin_id'], $session);
        }

        if (!empty($session['student']['student_id'])) {
            return [
                'type' => 'student',
                'id' => (string) $session['student']['student_id'],
                'name' => $session['student']['full_name'] ?? null,
                'role' => 'student',
            ];
        }

        return ['type' => 'system', 'id' => null, 'name' => null, 'role' => null];
    }

    /**
     * Actor for an admin account, preferring the full name.
     *
     * @return array{type:string, id:?string, name:?string, role:?string}
     */
    public function adminActor(int $adminId, array $fallback = []): array
    {
        $admin = [];
        try {
            $admin = $this->logs->findAdmin($adminId);
        } catch (\Throwable) {
        }

        return [
            'type' => 'admin',
            'id' => (string) $adminId,
            'name' => ($admin['full_name'] ?? null) ?: ($admin['username'] ?? null) ?: ($fallback['admin_username'] ?? null),
            'role' => ($admin['role'] ?? null) ?: ($fallback['role'] ?? null),
        ];
    }

    private static function department(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['college', 'shs'], true) ? $value : null;
    }

    private static function metadata(array $metadata): ?string
    {
        $clean = self::scrub($metadata);
        if ($clean === []) {
            return null;
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return $json === false ? null : $json;
    }

    private static function scrub(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE_KEY, $key)) {
                continue;
            }
            if (is_array($value)) {
                $value = self::scrub($value);
            } elseif (is_string($value)) {
                $value = mb_substr($value, 0, 300);
            } elseif (!is_scalar($value) && $value !== null) {
                continue;
            }
            $clean[$key] = $value;
        }
        return $clean;
    }

    private static function clip(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
