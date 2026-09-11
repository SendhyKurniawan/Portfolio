<?php
// The "My Pictures" window lists whatever sits in the art folders, so adding a picture
// is a matter of dropping the file in - no code or database change needed.

const GALLERY_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

/**
 * The pictures in one folder, in the order a person would count them.
 *
 * @return list<array{file: string, src: string, number: int}>
 */
function gallery_images(string $directory, string $webPath): array
{
    $files = is_dir($directory) ? (scandir($directory) ?: []) : [];
    $pictures = array_filter($files, static function (string $file) use ($directory): bool {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($extension, GALLERY_EXTENSIONS, true) && is_file($directory . '/' . $file);
    });

    // "2.jpg" before "10.jpg", which a plain sort would get backwards
    usort($pictures, 'strnatcasecmp');

    $number = 0;
    return array_map(static function (string $file) use ($webPath, &$number): array {
        // Filenames carry spaces and brackets, which browsers refuse to fetch unencoded
        return ['file' => $file, 'src' => $webPath . '/' . rawurlencode($file), 'number' => ++$number];
    }, $pictures);
}
