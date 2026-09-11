<?php
require_once '../lib/app.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}
require_csrf();
require_once '../config/db.php';

// type => [table, has uploaded image]
$tables = [
    'project' => ['projects', true],
    'blog' => ['blogs', true],
    'guestbook' => ['guestbook', false],
];

$type = post_string('type');
$id = filter_var(post_string('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($pdo && isset($tables[$type]) && $id !== false) {
    [$table, $hasImage] = $tables[$type];
    try {
        $image = null;
        if ($hasImage) {
            $stmt = $pdo->prepare("SELECT image FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
        }
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        if (is_string($image)) {
            delete_stored_upload($image, UPLOAD_DIR);
        }
    } catch (PDOException $e) {
        error_log("Delete from $table failed: " . $e->getMessage());
    }
}

header('Location: dashboard.php');
exit;
