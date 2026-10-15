<?php
$autoLogoutAuthenticated = $autoLogoutRole === 'admin'
    ? !empty($_SESSION['admin_authenticated']) && !empty($_SESSION['admin_id'])
    : !empty($_SESSION['student']);
if ($autoLogoutAuthenticated):
    $autoLogoutApi = rtrim($_ENV['APP_API'] ?? '', '/');
    ?>

<script type="module">

import AutoLogout from <?= json_encode(BASE_URL . 'assets/js/common/autoLogout.js', JSON_HEX_TAG | JSON_HEX_AMP) ?>;

window.API_URL = <?= json_encode($autoLogoutApi, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

AutoLogout.init({
    idleTimeout: 120000,
    warningDuration: 30000,
    logoutUrl: <?= json_encode($autoLogoutRole === 'admin' ? 'logout.php' : 'student/auth/logout.php') ?>,
    loginUrl: <?= json_encode(BASE_URL . ($autoLogoutRole === 'admin' ? 'admin-login' : 'login'), JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    statusUrl: <?= json_encode($autoLogoutApi . '/session_status.php?role=' . $autoLogoutRole, JSON_HEX_TAG | JSON_HEX_AMP) ?>
});

</script>

<?php endif; ?>
