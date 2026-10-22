<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/captcha.php';
require __DIR__ . '/config/db.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$fullName = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if (!verify_captcha($_POST['captcha'] ?? '')) {
        $errors[] = 'The captcha was wrong. Please try the new one.';
    }
    if ($fullName === '' || mb_strlen($fullName) > 100) {
        $errors[] = 'Please enter your full name (max 100 characters).';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3 to 30 characters: letters, numbers and underscores only.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        foreach ($stmt->fetchAll() as $row) {
            if (strcasecmp($row['username'], $username) === 0) {
                $errors[] = 'That username is already taken.';
            }
            if ($row['email'] !== null && strcasecmp($row['email'], $email) === 0) {
                $errors[] = 'An account with this email already exists.';
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, full_name, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $fullName, password_hash($password, PASSWORD_DEFAULT)]);

        flash('Registration successful. Please log in.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Create account</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= e($fullName) ?>" autocomplete="name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" pattern="[A-Za-z0-9_]{3,30}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>

        <?= captcha_field() ?>

        <button type="submit">Register</button>
    </form>

    <?php require __DIR__ . '/includes/social_buttons.php'; ?>

    <p class="switch">Already have an account? <a href="login.php">Log in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
