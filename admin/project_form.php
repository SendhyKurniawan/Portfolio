<?php
require_once '../lib/app.php';
require_admin();
require_once '../config/db.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$project = [
    'title' => '',
    'description' => '',
    'tech_stack' => '',
    'image' => '',
    'link' => '',
    'file_name' => ''
];
$errors = [];

if ($id && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch();
    if ($fetched) $project = $fetched;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    require_csrf();

    $values = [
        'title' => trim(post_string('title')),
        'description' => trim(post_string('description')),
        'tech_stack' => trim(post_string('tech_stack')),
        'link' => trim(post_string('link')),
        'file_name' => trim(post_string('file_name')),
    ];
    $errors = validate_lengths($values, [
        'title' => ['label' => 'Title', 'max' => 255],
        'file_name' => ['label' => 'File name', 'max' => 255],
        'description' => ['label' => 'Description', 'max' => 5000],
        'tech_stack' => ['label' => 'Tech stack', 'max' => 255],
        'link' => ['label' => 'Project link', 'max' => 255],
    ]);
    if (!isset($errors['link']) && !is_http_url($values['link'])) {
        $errors['link'] = 'Project link must start with http:// or https://.';
    }

    $upload = $errors ? ['path' => null, 'error' => null] : store_image_upload($_FILES['image_file'] ?? [], UPLOAD_DIR);
    if ($upload['error']) {
        $errors['image'] = $upload['error'];
    }
    $image = resolve_image_choice($project['image'], isset($_POST['remove_image']), post_string('image_url'), $upload['path']);
    if ($image['error']) {
        $errors['image'] = $image['error'];
    }

    if (!$errors) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, tech_stack=?, image=?, link=?, file_name=? WHERE id=?");
                $stmt->execute([$values['title'], $values['description'], $values['tech_stack'], $image['path'], $values['link'], $values['file_name'], $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO projects (title, description, tech_stack, image, link, file_name) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$values['title'], $values['description'], $values['tech_stack'], $image['path'], $values['link'], $values['file_name']]);
            }
            if ($image['path'] !== $project['image']) {
                delete_stored_upload($project['image'], UPLOAD_DIR);
            }
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            error_log('Project save failed: ' . $e->getMessage());
            $errors['db'] = 'Could not save the project. Please try again.';
        }
    }

    // Re-showing the form after a failed save: drop the image uploaded in this request
    if ($upload['path']) {
        delete_stored_upload($upload['path'], UPLOAD_DIR);
    }
    $project = array_merge($project, $values);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= $id ? 'Edit' : 'Add' ?> Project - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-window admin-window--form">
        <div class="window-header"><?= $id ? 'EDIT_PROJECT.EXE' : 'NEW_PROJECT.EXE' ?></div>
        <div class="window-body">
            <?php if ($errors): ?>
                <div class="form-errors" role="alert">
                    <ul><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="title" class="form-label text-dark fw-bold">Title</label>
                    <input type="text" id="title" name="title" maxlength="255" class="form-control rounded-0" value="<?= e($project['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="file_name" class="form-label text-dark fw-bold">File Name (For window header, e.g. APP.EXE)</label>
                    <input type="text" id="file_name" name="file_name" maxlength="255" class="form-control rounded-0" value="<?= e($project['file_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label text-dark fw-bold">Description</label>
                    <textarea id="description" name="description" maxlength="5000" class="form-control rounded-0" rows="3" required><?= e($project['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="tech_stack" class="form-label text-dark fw-bold">Tech Stack (comma separated)</label>
                    <input type="text" id="tech_stack" name="tech_stack" maxlength="255" class="form-control rounded-0" value="<?= e($project['tech_stack']) ?>" required>
                </div>

                <div class="mb-3 p-2 bg-white border">
                    <span class="form-label text-dark fw-bold d-block">Image</span>
                    <?php if (!empty($project['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= e(is_http_url($project['image']) ? $project['image'] : '../' . $project['image']) ?>" alt="Current project image" style="height: 100px; object-fit: cover; border: 1px solid #000;">
                            <br>
                            <input type="checkbox" name="remove_image" id="remove_image"> <label for="remove_image" class="text-danger small">Remove Image</label>
                        </div>
                    <?php endif; ?>

                    <label for="image_file" class="small text-muted">Upload New Image (JPG, PNG, GIF or WebP, max 5 MB; overrides existing)</label>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control rounded-0 mb-2">

                    <label for="image_url" class="small text-muted">OR Image URL (Fallback)</label>
                    <input type="url" id="image_url" name="image_url" class="form-control rounded-0" placeholder="https://..." value="<?= is_http_url($project['image']) ? e($project['image']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="link" class="form-label text-dark fw-bold">Project Link</label>
                    <input type="url" id="link" name="link" maxlength="255" class="form-control rounded-0" placeholder="https://..." value="<?= e($project['link']) ?>" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary rounded-0">CANCEL</a>
                    <button type="submit" class="btn btn-primary rounded-0">SAVE</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
