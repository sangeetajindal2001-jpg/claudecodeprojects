<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/oauth.php';

$provider = $_GET['provider'] ?? '';

if (!isset(oauth_providers()[$provider])) {
    header('Location: login.php');
    exit;
}

if (!oauth_enabled($provider)) {
    flash(oauth_providers()[$provider]['label'] . ' sign-in is not set up yet. Add its keys to config/oauth.php (see README.md).');
    header('Location: login.php');
    exit;
}

// The state value ties the callback to this browser session (CSRF protection)
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = ['value' => $state, 'provider' => $provider];

$params = [
    'client_id'     => oauth_config()[$provider]['client_id'],
    'redirect_uri'  => oauth_redirect_uri(),
    'response_type' => 'code',
    'scope'         => oauth_providers()[$provider]['scope'],
    'state'         => $state,
];
if ($provider === 'google') {
    $params['prompt'] = 'select_account';
}

header('Location: ' . oauth_providers()[$provider]['auth_url'] . '?' . http_build_query($params));
exit;
