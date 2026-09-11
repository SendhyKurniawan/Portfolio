<?php

const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
const UPLOAD_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];
const UPLOAD_PUBLIC_PREFIX = 'uploads/';

/**
 * Validate and store one entry from $_FILES as an image.
 * The stored name is random and its extension comes from the sniffed MIME type,
 * never from the client-supplied filename.
 *
 * @param callable(string, string): bool $move injectable for tests
 * @return array{path: ?string, error: ?string} path is null when no file was sent
 */
function store_image_upload(array $file, string $uploadDir, ?callable $move = null): array
{
    $move = $move ?? 'move_uploaded_file';
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => 'Image must be 5 MB or smaller.'];
    }
    if ($error !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null)) {
        return ['path' => null, 'error' => 'Image upload failed. Please try again.'];
    }
    if (($file['size'] ?? 0) > UPLOAD_MAX_BYTES) {
        return ['path' => null, 'error' => 'Image must be 5 MB or smaller.'];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extension = UPLOAD_IMAGE_TYPES[$mime] ?? null;
    if ($extension === null) {
        return ['path' => null, 'error' => 'Only JPG, PNG, GIF or WebP images are allowed.'];
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        error_log("Upload directory could not be created: $uploadDir");
        return ['path' => null, 'error' => 'Image could not be saved.'];
    }

    $name = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!$move($file['tmp_name'], rtrim($uploadDir, '/') . '/' . $name)) {
        error_log("Upload could not be moved into $uploadDir");
        return ['path' => null, 'error' => 'Image could not be saved.'];
    }
    return ['path' => UPLOAD_PUBLIC_PREFIX . $name, 'error' => null];
}

/**
 * Decide the image value to save for a blog/project form.
 * Precedence: new upload > "remove" checkbox > URL field > keep current.
 *
 * @return array{path: string, error: ?string}
 */
function resolve_image_choice(string $current, bool $remove, string $url, ?string $uploadedPath): array
{
    if ($uploadedPath !== null) {
        return ['path' => $uploadedPath, 'error' => null];
    }
    if ($remove) {
        return ['path' => '', 'error' => null];
    }
    $url = trim($url);
    if ($url !== '') {
        return is_http_url($url)
            ? ['path' => $url, 'error' => null]
            : ['path' => $current, 'error' => 'Image URL must start with http:// or https://.'];
    }
    // The URL field is pre-filled with the current URL, so clearing it means "remove"
    if (is_http_url($current)) {
        return ['path' => '', 'error' => null];
    }
    return ['path' => $current, 'error' => null];
}

// Remove a file we stored earlier; ignores remote URLs and anything outside uploads/
function delete_stored_upload(string $path, string $uploadDir): void
{
    if (!preg_match('#^' . preg_quote(UPLOAD_PUBLIC_PREFIX, '#') . '([A-Za-z0-9._-]+)$#', $path, $m)) {
        return;
    }
    $file = rtrim($uploadDir, '/') . '/' . $m[1];
    if (is_file($file) && !unlink($file)) {
        error_log("Could not delete upload: $file");
    }
}
