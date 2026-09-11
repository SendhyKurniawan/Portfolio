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
        . '<button type="submit" class="btn-retro btn-retro--danger" aria-label="Delete ' . e($label) . '"><i class="bi bi-trash"></i></button>'
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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>">
    <title>Dashboard - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="<?= e(asset_url('css/main.css', '../')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css', '../')) ?>">
    <script src="<?= e(asset_url('script/flavour-boot.js', '../')) ?>"></script>
</head>
<body class="admin-body">
    <header class="admin-top">
        <h1 class="nama admin-title">Control panel</h1>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn-gel btn-gel--chrome btn-gel--small">View site</a>
            <form method="post" action="logout.php" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn-gel btn-gel--small">Log out</button>
            </form>
        </div>
    </header>

    <?php if (!$pdo): ?>
        <div class="form-errors" role="alert">The database is offline. Check that the db container is running and the DB_* settings in .env are correct.</div>
    <?php endif; ?>

    <!-- Projects Management -->
    <section class="window admin-window">
        <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span><h2 class="window-title">Programs</h2>
            <a href="project_form.php" class="btn-gel btn-gel--chrome btn-gel--small bar-action"><i class="bi bi-plus-lg" aria-hidden="true"></i> New project</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th scope="col">ID</th><th scope="col">Title</th><th scope="col">Built with</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
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
                            <tr><td colspan="4" class="empty-row">No programs yet. Add one with New project.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Blog Management -->
    <section class="window admin-window">
        <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span><h2 class="window-title">System logs</h2>
            <a href="blog_form.php" class="btn-gel btn-gel--chrome btn-gel--small bar-action"><i class="bi bi-plus-lg" aria-hidden="true"></i> New log</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th scope="col">ID</th><th scope="col">Date</th><th scope="col">Title</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
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
                            <tr><td colspan="4" class="empty-row">No logs yet. Write one with New log.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Guestbook Inbox -->
    <section class="window admin-window">
        <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span><h2 class="window-title">Guestbook inbox</h2><span class="bar-count"><?= count($messages) ?> <?= count($messages) === 1 ? 'message' : 'messages' ?></span>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr><th scope="col">Received</th><th scope="col">From</th><th scope="col">Message</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
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
                            <tr><td colspan="4" class="empty-row">No guestbook messages yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</body>
</html>
