<?php
require __DIR__ . '/includes/auth.php';

// Only log out via POST with a valid token, so a link on another site can't log users out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

header('Location: login.php');
exit;
