<?php
require_once '../lib/app.php';
require_admin();
require_once '../config/db.php';

function fetch_rows(?PDO $pdo, string $sql): array
{
    if (!$pdo) {
        return [];
    }
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log('Dashboard query failed: ' . $e->getMessage());
        return [];
    }
}

function delete_button(string $type, int $id, string $label): string
{
    return '<form method="post" action="delete.php" class="inline-form" onsubmit="return confirm(\'Confirm delete?\')">'
        . csrf_field()
        . '<input type="hidden" name="type" value="' . e($type) . '">'
        . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button type="submit" class="btn-retro text-danger" aria-label="Delete ' . e($label) . '"><i class="bi bi-trash"></i></button>'
        . '</form>';
}

$projects = fetch_rows($pdo, 'SELECT id, title, tech_stack FROM projects ORDER BY id DESC');
$blogs = fetch_rows($pdo, 'SELECT id, date, title FROM blogs ORDER BY date DESC');
$messages = fetch_rows($pdo, 'SELECT id, name, email, message, created_at FROM guestbook ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Dashboard - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="nama" style="font-size: 2rem;">ADMIN_PANEL_V1.0</h1>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn-retro">VIEW SITE</a>
            <form method="post" action="logout.php" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn-retro">LOGOUT</button>
            </form>
        </div>
    </div>

    <?php if (!$pdo): ?>
        <div class="form-errors" role="alert">DATABASE OFFLINE: check the db container and DB_* settings in .env.</div>
    <?php endif; ?>

    <!-- Projects Management -->
    <div class="admin-window">
        <div class="window-header">
            <span>PROJECTS_MANAGER.EXE</span>
            <a href="project_form.php" class="btn-retro ms-2">+ ADD NEW</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th>ID</th><th>TITLE</th><th>TECH STACK</th><th>ACTIONS</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= e($row['title']) ?></td>
                                <td><?= e($row['tech_stack']) ?></td>
                                <td>
                                    <a href="project_form.php?id=<?= (int) $row['id'] ?>" class="btn-retro" aria-label="Edit <?= e($row['title']) ?>"><i class="bi bi-pencil"></i></a>
                                    <?= delete_button('project', (int) $row['id'], $row['title']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$projects): ?>
                            <tr><td colspan="4" class="empty-row">NO PROJECTS YET</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Blog Management -->
    <div class="admin-window">
        <div class="window-header">
            <span>BLOG_MANAGER.EXE</span>
            <a href="blog_form.php" class="btn-retro ms-2">+ ADD NEW</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th>ID</th><th>DATE</th><th>TITLE</th><th>ACTIONS</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogs as $row): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= e($row['date']) ?></td>
                                <td><?= e($row['title']) ?></td>
                                <td>
                                    <a href="blog_form.php?id=<?= (int) $row['id'] ?>" class="btn-retro" aria-label="Edit <?= e($row['title']) ?>"><i class="bi bi-pencil"></i></a>
                                    <?= delete_button('blog', (int) $row['id'], $row['title']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$blogs): ?>
                            <tr><td colspan="4" class="empty-row">NO LOGS YET</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Guestbook Inbox -->
    <div class="admin-window">
        <div class="window-header">
            <span>INBOX.EXE (<?= count($messages) ?>)</span>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th>RECEIVED</th><th>FROM</th><th>MESSAGE</th><th>ACTIONS</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $row): ?>
                            <tr>
                                <td><?= e($row['created_at']) ?></td>
                                <td>
                                    <?= e($row['name']) ?><br>
                                    <?php if ($row['email']): ?>
                                        <a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a>
                                    <?php endif; ?>
                                </td>
                                <td class="message-cell"><?= e($row['message']) ?></td>
                                <td><?= delete_button('guestbook', (int) $row['id'], 'message from ' . $row['name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$messages): ?>
                            <tr><td colspan="4" class="empty-row">INBOX EMPTY</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
