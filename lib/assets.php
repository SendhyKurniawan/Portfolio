<?php

const ASSET_ROOT = __DIR__ . '/..';

/**
 * URL for a static file with its modification time as a version, so browsers
 * fetch a fresh copy after each change instead of reusing a stale cache.
 * $prefix is the relative hop from the page to the web root (e.g. '../').
 */
function asset_url(string $path, string $prefix = ''): string
{
    $root = realpath(ASSET_ROOT);
    $file = realpath(ASSET_ROOT . '/' . $path);
    $insideRoot = $root !== false && $file !== false && str_starts_with($file, $root . DIRECTORY_SEPARATOR);
    if (!$insideRoot || !is_file($file)) {
        return $prefix . $path;
    }
    return $prefix . $path . '?v=' . filemtime($file);
}

/**
 * Full https://host/path URL, which social previews need because they read the
 * page from somewhere else. The Host header is the visitor's to set, so only the
 * hostname and port shape is kept; anything else is dropped.
 */
function absolute_url(string $path, ?array $server = null): string
{
    $server ??= $_SERVER;
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d+)?/', (string) ($server['HTTP_HOST'] ?? ''), $host)) {
        return '';
    }
    $https = ($server['HTTPS'] ?? 'off') !== 'off';

    return ($https ? 'https' : 'http') . '://' . $host[0] . '/' . ltrim($path, '/');
}
