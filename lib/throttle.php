<?php
// Per-client attempt limits keyed by IP, so clearing cookies does not reset them.
// State is one small JSON file of timestamps per bucket+client under the temp dir.

function throttle_dir(): string
{
    return getenv('THROTTLE_DIR') ?: sys_get_temp_dir() . '/portfolio-throttle';
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Keep attempts inside the window and say whether one more is allowed.
 *
 * @param int[] $attempts unix timestamps
 * @return array{attempts: int[], retry_after: int} retry_after is 0 when allowed
 */
function throttle_evaluate(array $attempts, int $now, int $max, int $window): array
{
    $recent = array_values(array_filter($attempts, fn ($t) => is_int($t) && $t > $now - $window));
    $retryAfter = count($recent) < $max ? 0 : min($recent) + $window - $now;
    return ['attempts' => $recent, 'retry_after' => $retryAfter];
}

function throttle_file(string $bucket, string $key): string
{
    return throttle_dir() . '/' . hash('sha256', $bucket . '|' . $key) . '.json';
}

/** Seconds until the client may try again; 0 means go ahead. */
function throttle_retry_after(string $bucket, string $key, int $max, int $window, ?int $now = null): int
{
    $file = throttle_file($bucket, $key);
    $attempts = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
    return throttle_evaluate(is_array($attempts) ? $attempts : [], $now ?? time(), $max, $window)['retry_after'];
}

function throttle_record(string $bucket, string $key, int $window, ?int $now = null): void
{
    $now = $now ?? time();
    $dir = throttle_dir();
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
        error_log("Throttle directory could not be created: $dir");
        return;
    }
    $handle = fopen(throttle_file($bucket, $key), 'c+');
    if ($handle === false) {
        error_log("Throttle state not writable in $dir");
        return;
    }
    flock($handle, LOCK_EX);
    $stored = json_decode(stream_get_contents($handle) ?: '[]', true);
    $recent = throttle_evaluate(is_array($stored) ? $stored : [], $now, PHP_INT_MAX, $window)['attempts'];
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode([...$recent, $now]));
    flock($handle, LOCK_UN);
    fclose($handle);
}

function throttle_clear(string $bucket, string $key): void
{
    $file = throttle_file($bucket, $key);
    if (is_file($file) && !unlink($file)) {
        error_log("Throttle state could not be cleared: $file");
    }
}

function minutes_text(int $seconds): string
{
    $minutes = max(1, (int) ceil($seconds / 60));
    return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
}
