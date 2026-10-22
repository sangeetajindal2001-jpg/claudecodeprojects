# Login App (PHP + MySQL)

A login system built from [requirement.txt](requirement.txt):

| Requirement | How it is done |
| --- | --- |
| Login screen with username and password | `login.php`, `register.php` |
| Facebook and Google accounts | OAuth 2.0 sign-in: `oauth_start.php`, `oauth_callback.php`, `includes/oauth.php` |
| Store data in MySQL (phpMyAdmin) | Database `claudecode_login`, tables `users` and `social_accounts` |
| Captcha | Image captcha drawn with PHP GD: `captcha.php`, `includes/captcha.php` |
| Dark mode | Toggle button (top right); follows the system setting until you choose |

## Run it on XAMPP

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin, click **Import**, and import `database.sql`.
3. Open the app folder in the browser, for example
   `http://localhost:8080/claude%20code%20vs/test3/claudecodeprojects/`
   (use your own Apache port and path).

## Set up Google and Facebook sign-in

The buttons work only after you add keys. Until then they show a "not set up yet" message.

1. Copy `config/oauth.example.php` to `config/oauth.php`.
2. **Google:** in the [Google Cloud Console](https://console.cloud.google.com/apis/credentials), create an
   **OAuth client ID** of type **Web application**. Add the redirect URI below under
   **Authorized redirect URIs**. Paste the client ID and secret into `config/oauth.php`.
3. **Facebook:** at [Facebook for Developers](https://developers.facebook.com/apps), create an app and add
   **Facebook Login**. Add the redirect URI below under **Valid OAuth Redirect URIs**. Paste the App ID and
   App Secret into `config/oauth.php`.

Redirect URI (change the host, port and path to match yours):

```
http://localhost:8080/claude%20code%20vs/test3/claudecodeprojects/oauth_callback.php
```

`config/oauth.php` is listed in `.gitignore`, so your secrets are not committed.

## Security

- Passwords stored with `password_hash()`; checked with `password_verify()`
- PDO prepared statements for every query
- CSRF tokens on every form, and an OAuth `state` check on sign-in
- Output escaped with `htmlspecialchars()`
- Session ID renewed after login
- Each captcha code works once
