<?php

declare(strict_types=1);

namespace App\middleware;

use App\Repositories\AdminRepo\AdminRepository;
use App\Repositories\StudentRepo\StudentRepository;
use PDO;

/** Shared API boundary; business rules remain in services and repositories. */
final class ApiSecurity
{
    /** Both arguments must be canonical filesystem paths, never request URLs. */
    public static function endpointFromPaths(string $entry, string $apiRoot): ?string
    {
        if ($entry === '' || $apiRoot === '') return null;
        $entry = str_replace('\\', '/', $entry);
        $prefix = rtrim(str_replace('\\', '/', $apiRoot), '/') . '/';
        // Windows paths are case insensitive; Linux hosting paths are not.
        $matches = DIRECTORY_SEPARATOR === '\\'
            ? str_starts_with(strtolower($entry), strtolower($prefix))
            : str_starts_with($entry, $prefix);
        if (!$matches) return null;
        $endpoint = substr($entry, strlen($prefix));
        if ($endpoint === '' || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $endpoint)) return null;
        return $endpoint;
    }

    public static function policy(string $endpoint): ?array
    {
        if (in_array($endpoint, ['csrf.php', 'session_status.php'], true)) {
            return ['GET', 'public'];
        }
        $public = ['admin/auth/login.php', 'admin/auth/verify.php', 'admin/auth/sendReset.php',
            'admin/auth/password_reset.php', 'student/auth/login.php', 'student/auth/register.php',
            'student/auth/activate.php', 'student/auth/registration_verify.php',
            'student/auth/send_reset.php', 'student/auth/password_reset.php',
            'student/auth/two_factor_verify.php', 'student/auth/two_factor_resend.php',
            'student/auth/logout.php', 'logout.php'];
        if (in_array($endpoint, $public, true)) {
            return ['POST', 'public'];
        }
        $student = ['student/set_enrollment.php', 'student/select_teacher.php', 'evaluation/submit.php'];
        if (in_array($endpoint, $student, true)) {
            return ['POST', 'student'];
        }
        if (in_array($endpoint, ['evaluation/evaluation_status.php', 'evaluation/get_evaluation_summary.php'], true)) {
            return ['GET', 'student'];
        }
        if ($endpoint === 'teacher/get_teachers.php') {
            return ['GET', 'teacher_read'];
        }
        $read = ['admin/analytics/analytics.php', 'admin/dashboard/dashboard.php'];
        if (in_array($endpoint, $read, true)) {
            return ['GET', 'admin_read'];
        }
        $manageRead = ['student/get_student.php', 'student/get_all_students.php',
            'student/download_template.php', 'student/download_legend.php',
            'program/get_program.php', 'program/get_by_department.php',
            'questionnaires/get_sets.php', 'questionnaires/get_set.php', 'questionnaires/get_question.php',
            'evaluation/get.php', 'evaluation/get_periods.php', 'evaluation/get_active_period.php',
            'evaluation/get_dashboard.php'];
        if (in_array($endpoint, $manageRead, true)) {
            return ['GET', 'admin_manage'];
        }
        $manage = ['teacher/create.php', 'teacher/update.php', 'teacher/delete.php',
            'teacher/add_handle.php', 'teacher/delete_handle.php', 'student/create.php',
            'student/update.php', 'student/delete.php', 'student/reset.php', 'student/reactivate.php',
            'student/csv.php', 'program/create.php', 'program/update.php', 'program/delete.php',
            'questionnaires/add_set.php', 'questionnaires/update_set.php', 'questionnaires/delete_set.php',
            'questionnaires/add_question.php', 'questionnaires/update_question.php',
            'questionnaires/delete_question.php', 'questionnaires/activate_question.php',
            'evaluation/create.php', 'evaluation/update.php', 'evaluation/delete.php',
            'evaluation/force_active.php', 'evaluation/force_close.php', 'notification/notification.php'];
        if (in_array($endpoint, $manage, true)) {
            return ['POST', 'admin_manage'];
        }
        return null;
    }

    public static function handle(PDO $pdo, string $endpoint): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        $policy = self::policy($endpoint);
        if ($policy === null) {
            self::fail(404, 'Not found.');
        }
        [$method, $access] = $policy;
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
            header('Allow: ' . $method);
            self::fail(405, 'Method not allowed.');
        }
        if ($access === 'student') {
            self::student($pdo);
        }
        if (str_starts_with($access, 'admin_')) {
            self::admin($pdo, $access === 'admin_manage');
        }
        if ($access === 'teacher_read') {
            if (($_SESSION['admin_authenticated'] ?? false) === true) {
                self::admin($pdo, true);
            } else {
                $student = self::student($pdo);
                $dept = $_GET['department'] ?? null;
                if (!is_string($dept) || $dept !== $student['department'] || isset($_GET['id'])) {
                    self::fail(403, 'You cannot access these teachers.');
                }
            }
        }
        if ($method === 'POST') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!is_string($token) || !hash_equals(self::csrfToken(), $token)) {
                self::fail(403, 'Invalid security token. Reload the page and try again.');
            }
            $type = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
            $form = ['teacher/create.php', 'teacher/update.php', 'teacher/add_handle.php',
                'student/create.php', 'student/update.php', 'student/csv.php',
                'program/create.php', 'program/update.php', 'questionnaires/add_set.php',
                'questionnaires/update_set.php', 'questionnaires/add_question.php',
                'questionnaires/update_question.php', 'evaluation/create.php', 'evaluation/update.php'];
            $isForm = in_array($endpoint, $form, true);
            if ($isForm && !in_array($type, ['multipart/form-data', 'application/x-www-form-urlencoded'], true)) {
                self::fail(415, 'A form request is required.');
            }
            if (!$isForm && $type !== 'application/json') {
                self::fail(415, 'A JSON request is required.');
            }
            $max = $isForm ? 3 * 1024 * 1024 : 128 * 1024;
            if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $max) {
                self::fail(413, 'Request is too large.');
            }
            if ($isForm) {
                foreach ($_POST as $value) {
                    if (!is_string($value)) self::fail(400, 'Form fields must be strings.');
                }
            }
            if (!$isForm) {
                $raw = file_get_contents('php://input', false, null, 0, $max + 1);
                if ($raw === false || strlen($raw) > $max) {
                    self::fail(413, 'Request is too large.');
                }
                try {
                    $data = json_decode($raw, false, 32, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    self::fail(400, 'Invalid JSON request.');
                }
                if (!$data instanceof \stdClass) {
                    self::fail(400, 'A JSON object is required.');
                }
                foreach ($data as $key => $value) {
                    if ($key === 'answers') {
                        if (!$value instanceof \stdClass) self::fail(400, 'Answers must be a JSON object.');
                    } elseif ($key === 'teachers') {
                        if (!is_array($value)) self::fail(400, 'Teachers must be an array.');
                    } elseif (!is_scalar($value) && $value !== null) {
                        self::fail(400, 'Invalid request field.');
                    }
                }
            }
        }
    }

    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function admin(PDO $pdo, bool $manage = false): void
    {
        AdminAuthMiddleware::handleApi();
        self::validateLifetime();
        $admin = (new AdminRepository($pdo))->findById((int) $_SESSION['admin_id']);
        if (!$admin || !is_string($_SESSION['admin_auth_version'] ?? null)
            || !hash_equals(hash('sha256', $admin->passwordHash), $_SESSION['admin_auth_version'])) {
            unset($_SESSION['admin_authenticated'], $_SESSION['admin_id']);
            self::fail(401, 'Unauthenticated. Please log in again.');
        }
        $role = strtolower(str_replace(' ', '_', $admin->role));
        if (!in_array($role, $manage ? ['super_admin'] : ['super_admin', 'dean'], true)) {
            self::fail(403, 'You do not have permission to perform this operation.');
        }
    }

    public static function student(PDO $pdo): array
    {
        $session = $_SESSION['student'] ?? null;
        if (!is_array($session) || !is_string($session['student_id'] ?? null) || $session['student_id'] === '') {
            self::fail(401, 'Unauthenticated. Please log in again.');
        }
        self::validateLifetime();
        $student = (new StudentRepository($pdo))->findById($session['student_id']);
        if (!$student || !$student->isActive || $student->accountStatus !== 'active'
            || !is_string($session['auth_version'] ?? null)
            || !hash_equals($student->sessionVersion(), $session['auth_version'])) {
            unset($_SESSION['student']);
            self::fail(401, 'Unauthenticated. Please log in again.');
        }
        $_SESSION['student'] = $student->toSessionArray();
        return $_SESSION['student'];
    }

    private static function validateLifetime(): void
    {
        $now = time();
        $started = (int) ($_SESSION['authenticated_at'] ?? 0);
        $last = (int) ($_SESSION['last_activity'] ?? 0);
        if ($started === 0 || $last === 0 || $now - $last > 1800 || $now - $started > 28800) {
            unset($_SESSION['student'], $_SESSION['admin_authenticated'], $_SESSION['admin_id']);
            self::fail(401, 'Your session has expired. Please log in again.');
        }
        $_SESSION['last_activity'] = $now;
    }

    public static function publicMessage(\Throwable $error): string
    {
        if ($error instanceof \PDOException || $error instanceof \Error) {
            error_log('[API] ' . get_class($error) . ': ' . $error->getMessage());
            return 'Unable to process the request.';
        }
        return $error->getMessage();
    }

    public static function fail(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['status' => 'error', 'success' => false, 'code' => $status, 'message' => $message]);
        exit;
    }
}
