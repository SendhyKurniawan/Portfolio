<?php
// Admin credentials come from the environment (see .env.example).

function admin_password_valid(string $password): bool
{
    $hash = getenv('ADMIN_PASSWORD_HASH');
    if ($hash === false || $hash === '') {
        error_log('Admin login disabled: ADMIN_PASSWORD_HASH is not set');
        return false;
    }
    return password_verify($password, $hash);
}

function admin_credentials_valid(string $username, string $password): bool
{
    $expectedUser = getenv('ADMIN_USERNAME');
    if ($expectedUser === false || $expectedUser === '') {
        error_log('Admin login disabled: ADMIN_USERNAME is not set');
        return false;
    }
    // Evaluate both so a wrong username takes as long as a wrong password
    $userOk = hash_equals($expectedUser, $username);
    $passOk = admin_password_valid($password);
    return $userOk && $passOk;
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}
