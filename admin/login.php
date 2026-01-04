<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple hardcoded credentials for now
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
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
                <div class="alert alert-danger p-1" style="font-size: 0.8rem;"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label" style="font-family: var(--main-font);">USERNAME:</label>
                    <input type="text" name="username" class="form-control" style="border-radius: 0; border: 2px inset #fff;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-family: var(--main-font);">PASSWORD:</label>
                    <input type="password" name="password" class="form-control" style="border-radius: 0; border: 2px inset #fff;">
                </div>
                <button type="submit" class="btn w-100" style="background: #c0c0c0; border: 2px outset #fff; font-weight: bold;">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
