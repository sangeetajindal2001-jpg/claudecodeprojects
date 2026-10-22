<?php
// Copy this file to config/oauth.php and fill in your keys.
// config/oauth.php is in .gitignore so your secrets are never committed.
//
// Redirect URI to register with both providers:
//   http://localhost:8080/claude%20code%20vs/test3/claudecodeprojects/oauth_callback.php
// (adjust host, port and path to where the app runs)

return [
    // Google Cloud Console > APIs & Services > Credentials > OAuth client ID (Web application)
    'google' => [
        'client_id'     => '',
        'client_secret' => '',
    ],

    // developers.facebook.com > My Apps > Create App > Facebook Login
    'facebook' => [
        'client_id'     => '', // App ID
        'client_secret' => '', // App Secret
    ],

    // Optional: full URL of the app folder, e.g. 'https://example.com/login'.
    // Leave empty to detect it from the current request.
    'base_url' => '',
];
