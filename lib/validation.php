<?php

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function is_http_url(string $value): bool
{
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
    return $scheme === 'http' || $scheme === 'https';
}

// href-safe URL: anything that is not http(s) collapses to "#"
function safe_url(?string $value): string
{
    $value = $value ?? '';
    return is_http_url($value) ? $value : '#';
}

function is_valid_date(string $value): bool
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function excerpt(string $text, int $limit = 100): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    $cut = mb_substr($text, 0, $limit);
    $lastSpace = mb_strrpos($cut, ' ');
    if ($lastSpace !== false && $lastSpace > $limit * 0.6) {
        $cut = mb_substr($cut, 0, $lastSpace);
    }
    return rtrim($cut, " .,;:-") . '...';
}

/**
 * Check required/max-length rules for a set of fields.
 *
 * @param array<string, string> $values
 * @param array<string, array{label: string, max: int, required?: bool}> $rules
 * @return array<string, string> field => error message
 */
function validate_lengths(array $values, array $rules): array
{
    $errors = [];
    foreach ($rules as $field => $rule) {
        $value = $values[$field] ?? '';
        $required = $rule['required'] ?? true;
        if ($required && $value === '') {
            $errors[$field] = $rule['label'] . ' is required.';
        } elseif (mb_strlen($value) > $rule['max']) {
            $errors[$field] = $rule['label'] . ' must be ' . $rule['max'] . ' characters or fewer.';
        }
    }
    return $errors;
}
