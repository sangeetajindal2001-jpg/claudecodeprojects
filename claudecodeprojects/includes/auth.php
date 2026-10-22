<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    // Stop the browser from showing protected pages from cache after logout
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
}

function login_user(array $user, $method)
{
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['login_method'] = $method;
}

function flash($message = null)
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $message = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $message;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals(csrf_token(), $token);
}
