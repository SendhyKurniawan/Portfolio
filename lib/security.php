<?php

// Escape for HTML text and attribute context
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(string $token): bool
{
    $expected = $_SESSION['csrf_token'] ?? '';
    return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function require_csrf(): void
{
    if (!csrf_valid(post_string('csrf_token'))) {
        http_response_code(400);
        exit('Invalid or expired form token. Go back, reload the page and try again.');
    }
}
