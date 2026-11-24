<?php

declare(strict_types=1);

namespace App\Services\LoggingServices;

use App\Repositories\LogRepo\ActivityLogRepository;
use PDO;

/**
 * Activity logging for every API endpoint that changes data.
 *
 * Called once from app/api_bootstrap.php after the request passed security:
 *
 *     ApiActivityLogger::start($pdo, 'teacher/create.php');
 *
 * For a mapped endpoint it captures the request input, the session (who was
 * signed in before) and readable labels for the target record (looked up
 * BEFORE the endpoint runs, so deletes still have a name). When the request
 * finishes, it reads the JSON response and logs the entry if it succeeded.
 * Unmapped endpoints (all read-only GET requests) are not touched.
 *
 * Privacy:
 *   - Passwords, tokens and codes are never copied into the log.
 *   - Evaluation submissions are logged as "submitted X of Y" only: no
 *     teacher, load, score, answer or comment is ever recorded.
 */
final class ApiActivityLogger
{
    /**
     * module, action, text: description template ({field} from input/lookups);
     * target: record type; id: input key holding the target id;
     * lookup: [lookupType => inputKey] resolved before the request runs;
     * handler: special handling (auth, logout, submit);
     * actor: who acted when nobody is signed in yet;
     * counts: copy numeric results from the response into metadata;
     * keep: extra input keys to store in metadata.
     */
    private const ENDPOINTS = [
        // ---- Authentication ------------------------------------------------
        'admin/auth/login.php'               => ['module' => 'auth', 'handler' => 'admin_login'],
        'admin/auth/verify.php'              => ['module' => 'auth', 'handler' => 'admin_login'],
        'logout.php'                         => ['module' => 'auth', 'handler' => 'logout'],
        'admin/auth/sendReset.php'           => ['module' => 'auth', 'action' => 'password_reset_request', 'text' => 'Requested a password reset link', 'actor' => ['admin_username', 'admin_username']],
        'admin/auth/password_reset.php'      => ['module' => 'auth', 'action' => 'password_reset', 'text' => 'Reset account password', 'actor' => ['admin_reset', 'token']],
        'student/auth/login.php'             => ['module' => 'auth', 'handler' => 'student_login'],
        'student/auth/two_factor_verify.php' => ['module' => 'auth', 'handler' => 'student_login'],
        'student/auth/logout.php'            => ['module' => 'auth', 'handler' => 'logout'],
        'student/auth/register.php'          => ['module' => 'auth', 'action' => 'register', 'text' => 'Requested an account activation link', 'actor' => ['student_id', 'studentId']],
        'student/auth/activate.php'          => ['module' => 'auth', 'action' => 'activate', 'text' => 'Activated account', 'actor' => ['student_activation', 'token']],
        'student/auth/send_reset.php'        => ['module' => 'auth', 'action' => 'password_reset_request', 'text' => 'Requested a password reset link', 'actor' => ['student_email', 'studentEmail']],
        'student/auth/password_reset.php'    => ['module' => 'auth', 'action' => 'password_reset', 'text' => 'Reset account password', 'actor' => ['student_reset', 'token']],

        // ---- Student evaluation flow -------------------------------------
        'student/set_enrollment.php'         => ['module' => 'evaluation', 'action' => 'set_enrollment', 'text' => 'Set enrollment type to {enrollmentType}'],
        'student/select_teacher.php'         => ['module' => 'evaluation', 'action' => 'select_teachers', 'text' => 'Selected {teacher_count} teachers to evaluate'],
        'evaluation/submit.php'              => ['module' => 'evaluation', 'handler' => 'submit'],

        // ---- Teachers ------------------------------------------------------
        'teacher/create.php'        => ['module' => 'teachers', 'action' => 'create', 'target' => 'teacher', 'id' => 'teacher_id', 'text' => 'Added teacher {full_name} ({employee_id})'],
        'teacher/update.php'        => ['module' => 'teachers', 'action' => 'update', 'target' => 'teacher', 'id' => 'teacher_id', 'lookup' => ['teacher' => 'teacher_id'], 'text' => 'Updated teacher {full_name} ({employee_id})'],
        'teacher/delete.php'        => ['module' => 'teachers', 'action' => 'delete', 'target' => 'teacher', 'id' => 'teacher_id', 'lookup' => ['teacher' => 'teacher_id'], 'text' => 'Removed teacher {full_name} ({employee_id})', 'prefer_lookup' => true],
        'teacher/add_handle.php'    => ['module' => 'teachers', 'action' => 'assign_program', 'target' => 'teacher', 'id' => 'teacher_id', 'lookup' => ['teacher' => 'teacher_id', 'program' => 'program'], 'text' => 'Assigned {program_code} {year_level_label} to teacher {full_name}', 'prefer_lookup' => true],
        'teacher/delete_handle.php' => ['module' => 'teachers', 'action' => 'unassign_program', 'target' => 'teacher', 'id' => 'teacher_id', 'lookup' => ['teacher' => 'teacher_id', 'load' => 'load_id'], 'text' => 'Removed {program_code} {year_level_label} from teacher {full_name}', 'prefer_lookup' => true],
        'teacher/csv.php'           => ['module' => 'teachers', 'action' => 'import', 'text' => 'Imported teachers from CSV ({department_label})', 'counts' => true],

        // ---- Students (admin) ----------------------------------------------
        'student/create.php'     => ['module' => 'students', 'action' => 'create', 'target' => 'student', 'id' => 'student_id', 'text' => 'Added student {full_name} ({student_id})'],
        'student/update.php'     => ['module' => 'students', 'action' => 'update', 'target' => 'student', 'id' => 'student_id', 'lookup' => ['student' => 'old_student_id'], 'text' => 'Updated student {full_name} ({student_id})'],
        'student/delete.php'     => ['module' => 'students', 'action' => 'delete', 'target' => 'student', 'id' => 'student_id', 'lookup' => ['student' => 'student_id'], 'text' => 'Removed student {full_name} ({student_id})', 'prefer_lookup' => true],
        'student/reset.php'      => ['module' => 'students', 'action' => 'reset_password', 'target' => 'student', 'id' => 'student_id', 'lookup' => ['student' => 'student_id'], 'text' => 'Reset password for student {full_name} ({student_id})', 'prefer_lookup' => true],
        'student/reactivate.php' => ['module' => 'students', 'action' => 'reactivate', 'target' => 'student', 'id' => 'student_id', 'lookup' => ['student' => 'student_id'], 'text' => 'Reactivated student {full_name} ({student_id})', 'prefer_lookup' => true],
        'student/csv.php'        => ['module' => 'students', 'action' => 'import', 'text' => 'Imported students from CSV ({department_label})', 'counts' => true],

        // ---- Programs ------------------------------------------------------
        'program/create.php' => ['module' => 'programs', 'action' => 'create', 'target' => 'program', 'id' => 'program_id', 'text' => 'Added program {program_code} – {program_name}'],
        'program/update.php' => ['module' => 'programs', 'action' => 'update', 'target' => 'program', 'id' => 'program_id', 'lookup' => ['program' => 'program_id'], 'text' => 'Updated program {program_code} – {program_name}'],
        'program/delete.php' => ['module' => 'programs', 'action' => 'delete', 'target' => 'program', 'id' => 'program_id', 'lookup' => ['program' => 'program_id'], 'text' => 'Removed program {program_code} – {program_name}', 'prefer_lookup' => true],

        // ---- Questionnaires --------------------------------------------------
        'questionnaires/add_set.php'           => ['module' => 'questionnaires', 'action' => 'create_set', 'target' => 'question_set', 'id' => 'set_id', 'text' => 'Created question set "{set_name}"'],
        'questionnaires/update_set.php'        => ['module' => 'questionnaires', 'action' => 'update_set', 'target' => 'question_set', 'id' => 'set_id', 'lookup' => ['set' => 'set_id'], 'text' => 'Updated question set "{set_name}"'],
        'questionnaires/delete_set.php'        => ['module' => 'questionnaires', 'action' => 'delete_set', 'target' => 'question_set', 'id' => 'set_id', 'lookup' => ['set' => 'set_id'], 'text' => 'Removed question set "{set_name}"', 'prefer_lookup' => true],
        'questionnaires/add_question.php'      => ['module' => 'questionnaires', 'action' => 'create_question', 'target' => 'question_set', 'id' => 'set_id', 'lookup' => ['set' => 'set_id'], 'text' => 'Added a question to "{set_name}"', 'keep' => ['question_name', 'categories']],
        'questionnaires/update_question.php'   => ['module' => 'questionnaires', 'action' => 'update_question', 'target' => 'question', 'id' => 'question_id', 'lookup' => ['question' => 'question_id'], 'text' => 'Updated a question in "{set_name}"', 'keep' => ['question_text', 'category']],
        'questionnaires/delete_question.php'   => ['module' => 'questionnaires', 'action' => 'delete_question', 'target' => 'question', 'id' => 'question_id', 'lookup' => ['question' => 'question_id'], 'text' => 'Removed a question from "{set_name}"', 'prefer_lookup' => true],
        'questionnaires/activate_question.php' => ['module' => 'questionnaires', 'action' => 'activate_question', 'target' => 'question', 'id' => 'question_id', 'lookup' => ['question' => 'question_id'], 'text' => 'Restored a question in "{set_name}"', 'prefer_lookup' => true],

        // ---- Evaluation periods ---------------------------------------------
        'evaluation/create.php'       => ['module' => 'periods', 'action' => 'create', 'target' => 'period', 'id' => 'period_id', 'text' => 'Scheduled {department_label} evaluation period A.Y. {academic_year} {semester}', 'keep' => ['start_date', 'end_date']],
        'evaluation/update.php'       => ['module' => 'periods', 'action' => 'update', 'target' => 'period', 'id' => 'period_id', 'lookup' => ['period' => 'period_id'], 'text' => 'Updated {department_label} evaluation period A.Y. {academic_year} {semester}', 'prefer_lookup' => true, 'keep' => ['update_start_date', 'update_end_date']],
        'evaluation/delete.php'       => ['module' => 'periods', 'action' => 'delete', 'target' => 'period', 'id' => 'period_id', 'lookup' => ['period' => 'period_id'], 'text' => 'Deleted {department_label} evaluation period A.Y. {academic_year} {semester}', 'prefer_lookup' => true],
        'evaluation/force_active.php' => ['module' => 'periods', 'action' => 'activate', 'target' => 'period', 'id' => 'period_id', 'lookup' => ['period' => 'period_id'], 'text' => 'Activated {department_label} evaluation period A.Y. {academic_year} {semester}', 'prefer_lookup' => true],
        'evaluation/force_close.php'  => ['module' => 'periods', 'action' => 'close', 'target' => 'period', 'id' => 'period_id', 'lookup' => ['period' => 'period_id'], 'text' => 'Closed {department_label} evaluation period A.Y. {academic_year} {semester}', 'prefer_lookup' => true],

        // ---- Monitoring ------------------------------------------------------
        'notification/notification.php' => ['module' => 'monitoring', 'action' => 'notify', 'text' => 'Sent evaluation reminders to {department_label} students', 'counts' => true],

        // ---- Archive -------------------------------------------------------
        'admin/archive/verify.php'  => ['module' => 'archive', 'action' => 'verify_backup', 'target' => 'period', 'id' => 'period_id', 'text' => 'Verified archive backup for period #{period_id}'],
        'admin/archive/preview.php' => ['module' => 'archive', 'action' => 'preview_backup', 'text' => 'Validated a backup file for restoration'],
        'admin/archive/restore.php' => ['module' => 'archive', 'action' => 'restore', 'target' => 'period', 'id' => 'period_id', 'text' => 'Restored archived period #{period_id}'],
        'admin/archive/delete.php'  => ['module' => 'archive', 'action' => 'delete', 'target' => 'period', 'id' => 'period_id', 'text' => 'Deleted archived period #{period_id}'],

        // Account Management (super admin). Passwords and the PIN are never stored (see SECRET_KEY).
        'admin/accounts/create.php' => ['module' => 'accounts', 'action' => 'create', 'target' => 'admin', 'id' => 'admin_id', 'text' => 'Added administrator {full_name} ({username})', 'keep' => ['role', 'status']],
        'admin/accounts/update.php' => ['module' => 'accounts', 'action' => 'update', 'target' => 'admin', 'id' => 'admin_id', 'lookup' => ['admin' => 'admin_id'], 'text' => 'Updated administrator {full_name} ({username})', 'keep' => ['role', 'status']],
        'admin/accounts/delete.php' => ['module' => 'accounts', 'action' => 'delete', 'target' => 'admin', 'id' => 'admin_id', 'lookup' => ['admin' => 'admin_id'], 'text' => 'Removed administrator {full_name} ({username})', 'prefer_lookup' => true],
    ];

    /**
     * Never copied from the request into the log: passwords, tokens, one-time
     * codes and secrets. ("code" only as a whole key or *_otp/verification
     * code, so fields like program_code are still usable.)
     */
    private const SECRET_KEY = '/password|passwd|token|secret|csrf|^code$|^otp$|_otp$|verification_code|two_factor_code|trust_device|pin/i';

    private static bool $started = false;

    public static function start(PDO $pdo, string $endpoint): void
    {
        $config = self::ENDPOINTS[$endpoint] ?? null;

        if ($config === null || self::$started || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        self::$started = true;

        try {
            $repo = new ActivityLogRepository($pdo);
            $service = new ActivityLogService($repo);

            $input = self::readInput();
            $before = $service->currentActor();
            $context = self::lookups($repo, $config['lookup'] ?? [], $input);
            $actor = isset($config['actor']) ? self::actorFromInput($repo, $service, $config['actor'], $input) : null;

            // Evaluation submit: remember the progress BEFORE this request, so only
            // the final submission (not complete -> complete) is logged.
            if (($config['handler'] ?? null) === 'submit' && $before['type'] === 'student' && !empty($before['id'])) {
                $context['__progress_before'] = $repo->studentSubmissionProgress((string) $before['id']);
            }

            ob_start();

            register_shutdown_function(static function () use ($repo, $service, $endpoint, $config, $input, $before, $context, $actor): void {
                try {
                    self::finish($repo, $service, $endpoint, $config, $input, $before, $context, $actor);
                } catch (\Throwable $e) {
                    error_log('[ActivityLog] ' . $endpoint . ': ' . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            // Logging must never block the request.
            error_log('[ActivityLog] start ' . $endpoint . ': ' . $e->getMessage());
        }
    }

    private static function finish(
        ActivityLogRepository $repo,
        ActivityLogService $service,
        string $endpoint,
        array $config,
        array $input,
        array $before,
        array $context,
        ?array $actor,
    ): void {
        $raw = ob_get_level() > 0 ? (string) ob_get_contents() : '';
        $response = json_decode($raw, true);
        $response = is_array($response) ? $response : [];
        $code = http_response_code();
        $ok = (($response['status'] ?? null) === 'success' || ($response['success'] ?? null) === true)
            && (!is_int($code) || $code < 400);

        $handler = $config['handler'] ?? null;

        if ($handler === 'admin_login' || $handler === 'student_login') {
            self::finishLogin($service, $endpoint, $handler, $input, $before, $ok);
            return;
        }

        if ($handler === 'logout') {
            if ($ok && $before['type'] !== 'system') {
                $service->log('auth', 'logout', 'Signed out', actor: $before);
            }
            return;
        }

        if (!$ok) {
            return;
        }

        if ($handler === 'submit') {
            self::finishSubmit($repo, $service, $before, $context['__progress_before'] ?? null);
            return;
        }

        // Context: looked-up labels + request values (request wins, except on
        // deletes and other "prefer_lookup" actions where the stored record wins).
        $values = self::safeInput($input);
        $values = !empty($config['prefer_lookup'])
            ? array_merge($values, array_filter($context, static fn ($v) => $v !== null && $v !== ''))
            : array_merge($context, array_filter($values, static fn ($v) => $v !== null && $v !== ''));

        $values['teacher_count'] = is_array($input['teachers'] ?? null) ? count($input['teachers']) : null;
        $values['department'] = $values['department'] ?? $values['target_dept'] ?? $values['update_department'] ?? null;
        $values['department_label'] = self::departmentLabel($values['department'] ?? null);
        $values['year_level_label'] = self::yearLevelLabel($values['year_level'] ?? $values['level'] ?? null);

        $targetId = isset($config['id'])
            ? (self::scalar($values[$config['id']] ?? null)
                ?? self::scalar($response['data'][$config['id']] ?? null)
                ?? self::scalar($response[$config['id']] ?? null)
                ?? self::scalar($response['data']['id'] ?? null))
            : null;

        $metadata = [];
        foreach ($config['keep'] ?? [] as $key) {
            if (isset($values[$key]) && $values[$key] !== '') {
                $metadata[$key] = $values[$key];
            }
        }
        if (!empty($config['counts'])) {
            $metadata += self::numericSummary($response);
        }

        $service->log(
            $config['module'],
            $config['action'],
            self::render($config['text'], $values),
            $config['target'] ?? null,
            $targetId,
            $values['department'] ?? null,
            $metadata,
            $actor,
        );
    }

    private static function finishLogin(ActivityLogService $service, string $endpoint, string $handler, array $input, array $before, bool $ok): void
    {
        $after = $service->currentActor();
        $wanted = $handler === 'admin_login' ? 'admin' : 'student';

        // Signed in during this request (password, or 2FA completing the login).
        if ($after['type'] === $wanted && $before['type'] !== $wanted) {
            $via = str_contains($endpoint, 'verify') ? ' (two-factor verified)' : '';
            $service->log('auth', 'login', 'Signed in' . $via, actor: $after);
            return;
        }

        // Rejected credentials or code. Successful first steps (e.g. "code
        // sent") are not logged; the login is logged when it completes.
        if (!$ok) {
            $who = $wanted === 'admin'
                ? (self::scalar($input['admin_username'] ?? null) ?? self::scalar($_SESSION['2fa_admin_id'] ?? null))
                : self::scalar($input['student_id'] ?? null);

            $service->log(
                'auth',
                'login_failed',
                str_contains($endpoint, 'verify') ? 'Failed two-factor verification' : 'Failed sign-in attempt',
                actor: ['type' => $wanted, 'id' => null, 'name' => $who, 'role' => null],
            );
        }
    }

    /**
     * Logs only the FINAL evaluation submission: the request that takes the
     * student from "not all submitted" to "all submitted" (the last teacher,
     * after Review & Confirm). Per-teacher saves before it and later edits of
     * submitted evaluations are not logged, so there is one entry per student
     * per period.
     *
     * @param array{period_id:?int, required:int, submitted:int}|null $progressBefore
     */
    private static function finishSubmit(ActivityLogRepository $repo, ActivityLogService $service, array $before, ?array $progressBefore): void
    {
        $actor = $service->currentActor();
        $actor = $actor['type'] === 'student' ? $actor : $before;
        if ($actor['type'] !== 'student' || empty($actor['id'])) {
            return;
        }

        // Totals only: never which teacher, scores, answers or comments.
        $after = $repo->studentSubmissionProgress((string) $actor['id']);

        $isComplete = static fn (?array $p): bool =>
            $p !== null && $p['required'] > 0 && $p['submitted'] >= $p['required'];

        // Same period, already complete before this request: an edit, not the final submit.
        $wasComplete = $isComplete($progressBefore) && ($progressBefore['period_id'] ?? null) === $after['period_id'];

        if (!$isComplete($after) || $wasComplete) {
            return;
        }

        $student = $repo->findStudent((string) $actor['id']);
        $total = $after['required'];

        $service->log(
            'evaluation',
            'complete',
            "Submitted final evaluation: completed all {$total} teacher evaluation" . ($total === 1 ? '' : 's'),
            'period',
            $after['period_id'] !== null ? (string) $after['period_id'] : null,
            $student['department'] ?? null,
            ['submitted' => $after['submitted'], 'required' => $total],
            $actor,
        );
    }

    // ----------------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------------

    private static function readInput(): array
    {
        $json = json_decode((string) file_get_contents('php://input'), true);
        return array_merge(is_array($json) ? $json : [], $_POST);
    }

    /** Request values without secrets or nested structures. */
    private static function safeInput(array $input): array
    {
        $safe = [];
        foreach ($input as $key => $value) {
            if (!is_string($key) || preg_match(self::SECRET_KEY, $key) || !is_scalar($value)) {
                continue;
            }
            $safe[$key] = trim((string) $value);
        }
        return $safe;
    }

    private static function lookups(ActivityLogRepository $repo, array $lookups, array $input): array
    {
        $context = [];
        foreach ($lookups as $type => $key) {
            $id = self::scalar($input[$key] ?? null);
            if ($id === null) {
                continue;
            }
            $row = match ($type) {
                'teacher'  => $repo->findTeacher((int) $id),
                'admin'    => $repo->findAdmin((int) $id),
                'student'  => $repo->findStudent($id),
                'program'  => $repo->findProgram((int) $id),
                'load'     => $repo->findTeacherLoad((int) $id),
                'set'      => $repo->findQuestionSet((int) $id),
                'question' => $repo->findQuestion((int) $id),
                'period'   => $repo->findPeriod((int) $id),
                default    => [],
            };
            // The teacher's own department wins over the program's.
            $context = $type === 'teacher' ? array_merge($context, $row) : array_merge($row, $context);
        }
        return array_map(static fn ($v) => $v === null ? null : (string) $v, $context);
    }

    /** Actor for public endpoints where nobody is signed in yet. */
    private static function actorFromInput(ActivityLogRepository $repo, ActivityLogService $service, array $actor, array $input): ?array
    {
        [$type, $key] = $actor;
        $value = self::scalar($input[$key] ?? null);
        if ($value === null) {
            return null;
        }

        $student = match ($type) {
            'student_id'         => $repo->findStudent($value),
            'student_email'      => $repo->findStudentByEmail($value),
            'student_activation' => $repo->findStudentByActivationToken(hash('sha256', $value)),
            'student_reset'      => $repo->findStudentByResetToken(hash('sha256', $value)),
            default              => null,
        };
        if ($student !== null) {
            return $student === [] ? null : ['type' => 'student', 'id' => $student['student_id'], 'name' => $student['full_name'] ?? null, 'role' => 'student'];
        }

        $admin = match ($type) {
            'admin_username' => $repo->findAdminByUsername($value),
            'admin_reset'    => $repo->findAdminByResetToken(hash('sha256', $value)),
            default          => [],
        };
        return $admin === [] ? null : $service->adminActor((int) $admin['admin_id']);
    }

    private static function render(string $template, array $values): string
    {
        $text = preg_replace_callback('/\{([a-z_]+)\}/i', static function (array $m) use ($values): string {
            $value = $values[$m[1]] ?? null;
            return is_scalar($value) ? trim((string) $value) : '';
        }, $template) ?? $template;

        // Tidy parts left empty by missing values.
        $text = preg_replace(['/\(\s*\)/', '/""/', '/\s+–\s*$/', '/\s{2,}/', '/\s+([,.)])/'], ['', '', '', ' ', '$1'], $text) ?? $text;
        return trim($text);
    }

    private static function numericSummary(array $response): array
    {
        $summary = [];
        foreach ([$response, is_array($response['data'] ?? null) ? $response['data'] : []] as $level) {
            foreach ($level as $key => $value) {
                if (is_string($key) && (is_int($value) || is_float($value)) && count($summary) < 10) {
                    $summary[$key] = $value;
                }
            }
        }
        return $summary;
    }

    private static function departmentLabel(?string $department): ?string
    {
        return match (strtolower(trim((string) $department))) {
            'college' => 'College',
            'shs' => 'SHS',
            default => null,
        };
    }

    private static function yearLevelLabel(mixed $level): ?string
    {
        $n = (int) $level;
        return match (true) {
            $n === 11 || $n === 12 => "Grade {$n}",
            $n >= 1 && $n <= 4 => [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'][$n] . ' Year',
            default => null,
        };
    }

    private static function scalar(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
