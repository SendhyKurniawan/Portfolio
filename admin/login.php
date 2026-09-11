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
        $error = 'Session expired, please try again.';
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
        $error = 'Invalid credentials!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/modal.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-window {
            width: 400px;
            background: #c0c0c0;
            border: 2px outset #fff;
            box-shadow: 8px 8px 0 #000;
        }
        .login-header {
            background: linear-gradient(90deg, navy, #1084d0);
            color: white;
            padding: 5px;
            font-family: var(--main-font);
            font-weight: bold;
        }
        .login-body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="login-window">
        <div class="login-header">ADMIN_LOGIN.EXE</div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger p-1" role="alert" style="font-size: 0.8rem;"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label" style="font-family: var(--main-font);">USERNAME:</label>
                    <input type="text" id="username" name="username" autocomplete="username" required class="form-control" style="border-radius: 0; border: 2px inset #fff;">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label" style="font-family: var(--main-font);">PASSWORD:</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required class="form-control" style="border-radius: 0; border: 2px inset #fff;">
                </div>
                <button type="submit" class="btn w-100" style="background: #c0c0c0; border: 2px outset #fff; font-weight: bold;">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
