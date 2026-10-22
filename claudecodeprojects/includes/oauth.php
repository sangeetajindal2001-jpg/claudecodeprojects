<?php
// Google and Facebook sign-in using the OAuth 2.0 authorization code flow.

function oauth_config()
{
    static $config = null;
    if ($config === null) {
        $file = __DIR__ . '/../config/oauth.php';
        $config = require (is_file($file) ? $file : __DIR__ . '/../config/oauth.example.php');
    }
    return $config;
}

function oauth_providers()
{
    return [
        'google' => [
            'label'     => 'Google',
            'auth_url'  => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'scope'     => 'openid email profile',
        ],
        'facebook' => [
            'label'     => 'Facebook',
            'auth_url'  => 'https://www.facebook.com/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/oauth/access_token',
            'scope'     => 'email,public_profile',
        ],
    ];
}

function oauth_enabled($provider)
{
    $config = oauth_config();
    return isset(oauth_providers()[$provider])
        && !empty($config[$provider]['client_id'])
        && !empty($config[$provider]['client_secret']);
}

function app_base_url()
{
    $config = oauth_config();
    if (!empty($config['base_url'])) {
        return rtrim($config['base_url'], '/');
    }
    $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $path   = implode('/', array_map('rawurlencode', explode('/', $dir)));
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $path;
}

function oauth_redirect_uri()
{
    return app_base_url() . '/oauth_callback.php';
}

// Sends a request and returns the decoded JSON response, or null on failure.
function http_json($url, ?array $post = null, array $headers = [])
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $status >= 400) {
        return null;
    }
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

// Swaps the one-time code for an access token, then fetches the user's profile.
// Returns ['id', 'email', 'email_verified', 'name'] or null.
function oauth_fetch_profile($provider, $code)
{
    $config = oauth_config()[$provider];
    $token = http_json(oauth_providers()[$provider]['token_url'], [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => oauth_redirect_uri(),
        'client_id'     => $config['client_id'],
        'client_secret' => $config['client_secret'],
    ]);
    if (empty($token['access_token'])) {
        return null;
    }

    if ($provider === 'google') {
        $info = http_json('https://openidconnect.googleapis.com/v1/userinfo', null, ['Authorization: Bearer ' . $token['access_token']]);
        if (empty($info['sub'])) {
            return null;
        }
        return [
            'id'             => $info['sub'],
            'email'          => $info['email'] ?? null,
            'email_verified' => !empty($info['email_verified']),
            'name'           => $info['name'] ?? ($info['email'] ?? 'Google user'),
        ];
    }

    $info = http_json('https://graph.facebook.com/me?' . http_build_query([
        'fields'       => 'id,name,email',
        'access_token' => $token['access_token'],
    ]));
    if (empty($info['id'])) {
        return null;
    }
    return [
        'id'             => $info['id'],
        'email'          => $info['email'] ?? null,
        // Facebook only returns an email address it has confirmed
        'email_verified' => !empty($info['email']),
        'name'           => $info['name'] ?? 'Facebook user',
    ];
}

// Finds the user linked to this social account, links it to an existing
// account with the same verified email, or creates a new user.
function find_or_create_social_user(PDO $pdo, $provider, array $profile)
{
    $stmt = $pdo->prepare('SELECT u.* FROM users u JOIN social_accounts s ON s.user_id = u.id WHERE s.provider = ? AND s.provider_id = ?');
    $stmt->execute([$provider, $profile['id']]);
    if ($user = $stmt->fetch()) {
        return $user;
    }

    $user = null;
    if ($profile['email'] && $profile['email_verified']) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$profile['email']]);
        $user = $stmt->fetch() ?: null;
    }

    $pdo->beginTransaction();
    try {
        if (!$user) {
            $username = unique_username($pdo, $profile['email'] ? strstr($profile['email'], '@', true) : $profile['name']);
            $email = $profile['email_verified'] ? $profile['email'] : null;
            $stmt = $pdo->prepare('INSERT INTO users (username, email, full_name, password_hash) VALUES (?, ?, ?, NULL)');
            $stmt->execute([$username, $email, mb_substr($profile['name'], 0, 100)]);
            $user = ['id' => (int) $pdo->lastInsertId()];
        }
        $stmt = $pdo->prepare('INSERT INTO social_accounts (user_id, provider, provider_id) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $provider, $profile['id']]);
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
        throw $ex;
    }
    return $user;
}

function unique_username(PDO $pdo, $base)
{
    $base = substr(preg_replace('/[^a-z0-9_]/', '', strtolower((string) $base)), 0, 20);
    if (strlen($base) < 3) {
        $base = 'user';
    }
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ?');
    $candidate = $base;
    while (true) {
        $stmt->execute([$candidate]);
        if (!$stmt->fetch()) {
            return $candidate;
        }
        $candidate = $base . random_int(100, 99999);
    }
}
