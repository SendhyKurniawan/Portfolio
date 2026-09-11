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

$id = filter_var(post_string('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$approved = post_string('approved') === '1';

if ($pdo && $id !== false) {
    try {
        set_guestbook_approval($pdo, $id, $approved);
    } catch (PDOException $e) {
        error_log('Guestbook approval failed: ' . $e->getMessage());
    }
}

header('Location: dashboard.php');
exit;
