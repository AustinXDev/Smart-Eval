<?php 
//header("Location: views/auth/Login.view.php");
//exit();

require_once __DIR__ . '/../app/init.php';

define('BASE_URL', rtrim($_ENV['APP_URL'], '/') . '/');

$request = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

$request = strtolower(rtrim($request, '/'));

switch ($request) {
    case '':
        header("Location: " . BASE_URL . "Login");
        exit();
    case 'login':
        require_once __DIR__ . '/../views/auth/login.view.php';
        break;

    case 'register':
        require_once __DIR__ . '/../views/auth/register.view.php';
        break;

    case 'create-password':
        require_once __DIR__ . '/../views/auth/create_password.view.php';
        break;

    case 'activate-account':
        require_once __DIR__ . '/../views/auth/activate.view.php';
        break;
    
    case 'forgot-password':
        require_once __DIR__ . '/../views/auth/forgot_password.view.php';
        break;

    case 'reset-password':
        require_once __DIR__ . '/../views/auth/new_password.view.php';
        break;
    
    case 'admin-login':
      require_once __DIR__ . '/../views/admin/login.view.php';
      break;

    case 'admin-forgot-password':
      require_once __DIR__ . '/../views/admin/forgot_password.view.php';
      break;

    case 'admin-reset-password':
      require_once __DIR__ . '/../views/admin/new_password.view.php'; 
      break;

    case 'teachers':
        require_once __DIR__ . '/../views/admin/teachers.view.php';
        break;

    case 'unavailable':
        require_once __DIR__ . '/../views/student/no_evaluation.php';
        break;
    
    case 'evaluation-done':
        require_once __DIR__ . '/../views/student/evaluation_done.php';
        break;

    case 'teacher-selection':
        require_once __DIR__ . '/../views/student/select_teachers.php';
        break;

    case 'enrollment-selection':
        require_once __DIR__ . '/../views/student/enrollment_selection.php';
        break;

    case 'evaluation':
        require_once __DIR__ . '/../views/student/evaluation.view.php';
        break;

    case 'evaluation-periods':
        require_once __DIR__ . '/../views/admin/evaluation_periods.view.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/../views/admin/dashboard.view.php';
        break;

    case 'manage-teachers':
        require_once __DIR__ . '/../views/admin/teachers.view.php';
        break;

    case 'manage-students':
        require_once __DIR__ . '/../views/admin/students.view.php';
        break;

    case 'manage-evaluation-period':
        require_once __DIR__ . '/../views/admin/evaluation_periods.view.php';
        break;

    case 'manage-questionnaires':
        require_once __DIR__ . '/../views/admin/manage_questionnaires.view.php';
        break;

    case 'manage-program':
        require_once __DIR__ . '/../views/admin/manage_programs.view.php';
        break;

    case 'reports-analytics':
        require_once __DIR__ . '/../views/admin/report_analytics.view.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        break;
}
?>