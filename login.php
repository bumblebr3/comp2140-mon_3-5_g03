
<?php
/* Authenticates an active user, regenerates the session ID after successful login, records the login time, and redirects by role. */

require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin_home.php' : 'security_homepage.php');
}

$error = '';

// Authenticate the submitted credentials only after validating the form token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare(
        'SELECT id, username, password_hash, role
         FROM users
         WHERE username = ? AND is_active = 1
         LIMIT 1'
    );

    $stmt->execute([$username]);
    $account = $stmt->fetch();

    // Passwords are checked against the stored hash; the submitted password is never stored.
    if ($account && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$account['id'];
        $_SESSION['username'] = $account['username'];
        $_SESSION['role'] = $account['role'];
        $_SESSION['last_activity'] = time();

        db()->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?'
        )->execute([$account['id']]);

        audit_log('login', 'User signed in.');

        redirect($account['role'] === 'admin' ? 'admin_home.php' : 'security_homepage.php');
    }

    // Record unsuccessful login attempts without storing the submitted password.
    audit_log(
        'login_failed',
        'Failed login attempt for username: ' . ($username !== '' ? $username : '[empty]')
    );

    $error = 'Username or password is incorrect.';
}

$pageTitle = 'Login';
?>

<link rel="stylesheet" href="assets/css/style.css">

<div class="login-wrap">
    <section class="card login-card">
        <div class="login-logo">
            <img src="assets/logo.png"/>
        </div>

        <h1>Welcome</h1>
        <p class="muted">Sign in to the Preston Hall visitor system.</p>

        <?php if ($error): ?>
            <p class="notice error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <label for="username">Username</label>
            <input id="username" name="username" type="text" required autocomplete="username">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">

            <button class="btn primary full" type="submit">Login</button>
        </form>

        <p class="small muted">
            For security, the system signs users out after 30 minutes of inactivity.
        </p>
    </section>
</div>
