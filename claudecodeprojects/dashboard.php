<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/db.php';

require_login();

$stmt = $pdo->prepare('SELECT username, email, full_name, password_hash, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// User was deleted from the database while logged in
if (!$user) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT provider FROM social_accounts WHERE user_id = ? ORDER BY provider');
$stmt->execute([$_SESSION['user_id']]);
$linked = array_map('ucfirst', $stmt->fetchAll(PDO::FETCH_COLUMN));

$methodNames = ['password' => 'Username and password', 'google' => 'Google', 'facebook' => 'Facebook'];

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Welcome, <?= e($user['full_name']) ?>!</h1>
    <p>You are logged in.</p>

    <dl class="details">
        <dt>Username</dt>
        <dd><?= e($user['username']) ?></dd>
        <dt>Email</dt>
        <dd><?= e($user['email'] ?? 'Not provided') ?></dd>
        <dt>Signed in with</dt>
        <dd><?= e($methodNames[$_SESSION['login_method'] ?? 'password'] ?? 'Unknown') ?></dd>
        <dt>Linked accounts</dt>
        <dd><?= e($linked ? implode(', ', $linked) : 'None') ?></dd>
        <dt>Password set</dt>
        <dd><?= $user['password_hash'] !== null ? 'Yes' : 'No (social sign-in only)' ?></dd>
        <dt>Member since</dt>
        <dd><?= e(date('d M Y', strtotime($user['created_at']))) ?></dd>
    </dl>

    <form method="post" action="logout.php">
        <?= csrf_field() ?>
        <button type="submit" class="secondary">Log out</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
