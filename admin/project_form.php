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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>">
    <title><?= $id ? 'Edit' : 'Add' ?> Project - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/main.css', '../')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css', '../')) ?>">
    <script src="<?= e(asset_url('script/flavour-boot.js', '../')) ?>"></script>
</head>
<body class="admin-body">
    <div class="window admin-window admin-window--form">
        <div class="window-bar"><span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span><h1 class="window-title admin-form-title"><?= $id ? 'Edit project' : 'New project' ?></h1></div>
        <div class="window-body">
            <?php if ($errors): ?>
                <div class="form-errors" role="alert">
                    <ul><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="title" class="field-label">Title</label>
                    <input type="text" id="title" name="title" maxlength="255" class="field" value="<?= e($project['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="file_name" class="field-label">Window title <span class="field-hint">(shown in the title bar, e.g. shop.exe)</span></label>
                    <input type="text" id="file_name" name="file_name" maxlength="255" class="field" value="<?= e($project['file_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="field-label">Description</label>
                    <textarea id="description" name="description" maxlength="5000" class="field" rows="3" required><?= e($project['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="tech_stack" class="field-label">Built with <span class="field-hint">(comma separated)</span></label>
                    <input type="text" id="tech_stack" name="tech_stack" maxlength="255" class="field" value="<?= e($project['tech_stack']) ?>" required>
                </div>

                <div class="mb-3 image-box">
                    <span class="field-label">Image</span>
                    <?php if (!empty($project['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= e(is_http_url($project['image']) ? $project['image'] : '../' . $project['image']) ?>" alt="Current project image" class="image-preview">
                            <br>
                            <input type="checkbox" name="remove_image" id="remove_image"> <label for="remove_image" class="remove-image-label">Remove image</label>
                        </div>
                    <?php endif; ?>

                    <label for="image_file" class="field-hint d-block mb-1">Upload a new image (JPG, PNG, GIF or WebP, up to 5 MB). It replaces the current one.</label>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp" class="field mb-3">

                    <label for="image_url" class="field-hint d-block mb-1">Or paste an image URL</label>
                    <input type="url" id="image_url" name="image_url" class="field" placeholder="https://..." value="<?= is_http_url($project['image']) ? e($project['image']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="link" class="field-label">Site link</label>
                    <input type="url" id="link" name="link" maxlength="255" class="field" placeholder="https://..." value="<?= e($project['link']) ?>" required>
                </div>
                <div class="admin-form-actions">
                    <a href="dashboard.php" class="btn-gel btn-gel--chrome">Cancel</a>
                    <button type="submit" class="btn-gel">Save</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
