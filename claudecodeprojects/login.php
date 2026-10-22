<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/captcha.php';
require __DIR__ . '/config/db.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';
$flash = flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaOk = verify_captcha($_POST['captcha'] ?? '');

    if (!verify_csrf()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } elseif (!$captchaOk) {
        $error = 'The captcha was wrong. Please try the new one.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // password_hash is NULL for Google/Facebook-only accounts, so they can't log in here
        if ($user && $user['password_hash'] !== null && password_verify($password, $user['password_hash'])) {
            login_user($user, 'password');
            header('Location: dashboard.php');
            exit;
        }
        // Same message for unknown username and wrong password
        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Log in</h1>

    <?php if ($flash): ?>
        <div class="alert alert-success"><p><?= e($flash) ?></p></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><p><?= e($error) ?></p></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <?= captcha_field() ?>

        <button type="submit">Log in</button>
    </form>

    <?php require __DIR__ . '/includes/social_buttons.php'; ?>

    <p class="switch">No account yet? <a href="register.php">Register</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
