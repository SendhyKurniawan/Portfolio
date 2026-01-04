<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id && $pdo) {
    if ($type === 'project') {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($type === 'blog') {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header('Location: dashboard.php');
exit;
?>
