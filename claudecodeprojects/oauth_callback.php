<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/oauth.php';
require __DIR__ . '/config/db.php';

function fail($message)
{
    flash($message);
    header('Location: login.php');
    exit;
}

$saved = $_SESSION['oauth_state'] ?? null;
unset($_SESSION['oauth_state']);

$state = $_GET['state'] ?? '';
if (!$saved || !is_string($state) || !hash_equals($saved['value'], $state)) {
    fail('Sign-in could not be verified. Please try again.');
}

$provider = $saved['provider'];
$label = oauth_providers()[$provider]['label'];

if (isset($_GET['error'])) {
    fail("$label sign-in was cancelled.");
}

$code = $_GET['code'] ?? '';
if (!is_string($code) || $code === '') {
    fail("$label sign-in failed. Please try again.");
}

$profile = oauth_fetch_profile($provider, $code);
if (!$profile) {
    fail("Could not get your details from $label. Please try again.");
}

$user = find_or_create_social_user($pdo, $provider, $profile);
login_user($user, $provider);
header('Location: dashboard.php');
exit;
