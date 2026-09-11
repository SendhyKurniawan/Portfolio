<?php
require_once '../lib/app.php';
start_session();

if (is_admin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wait = throttle_retry_after('login', client_ip(), LOGIN_MAX_FAILURES, LOGIN_WINDOW_SECONDS);
    if (!csrf_valid(post_string('csrf_token'))) {
        $error = 'Session expired. Please try again.';
    } elseif ($wait > 0) {
        $error = 'Too many failed attempts. Try again in ' . minutes_text($wait) . '.';
    } elseif (admin_credentials_valid(post_string('username'), post_string('password'))) {
        throttle_clear('login', client_ip());
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        throttle_record('login', client_ip(), LOGIN_WINDOW_SECONDS);
        $error = 'Invalid credentials. Check your username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>">
    <title>Admin login - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('css/main.css', '../')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css', '../')) ?>">
</head>
<body class="admin-body admin-body--login">
    <main class="window login-window">
        <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <h1 class="window-title admin-form-title">Log in to KURSE CO.</h1>
        </div>
        <div class="window-body">
            <?php if ($error): ?>
                <div class="form-errors" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="field-label">Username</label>
                    <input type="text" id="username" name="username" autocomplete="username" required class="field">
                </div>
                <div>
                    <label for="password" class="field-label">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required class="field">
                </div>
                <button type="submit" class="btn-gel login-submit">Log in</button>
            </form>
        </div>
    </main>
</body>
</html>
