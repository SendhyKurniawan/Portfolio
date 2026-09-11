<?php
require_once '../lib/app.php';
require_admin();
require_once '../config/db.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$blog = [
    'title' => '',
    'content' => '',
    'image' => '',
    'date' => date('Y-m-d')
];
$errors = [];

if ($id && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch();
    if ($fetched) $blog = $fetched;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    require_csrf();

    $values = [
        'title' => trim(post_string('title')),
        'content' => trim(post_string('content')),
        'date' => trim(post_string('date')),
    ];
    $errors = validate_lengths($values, [
        'title' => ['label' => 'Title', 'max' => 255],
        'content' => ['label' => 'Content', 'max' => 60000],
        'date' => ['label' => 'Date', 'max' => 10],
    ]);
    if (!isset($errors['date']) && !is_valid_date($values['date'])) {
        $errors['date'] = 'Date must be a valid YYYY-MM-DD date.';
    }

    $upload = $errors ? ['path' => null, 'error' => null] : store_image_upload($_FILES['image_file'] ?? [], UPLOAD_DIR);
    if ($upload['error']) {
        $errors['image'] = $upload['error'];
    }
    $image = resolve_image_choice($blog['image'] ?? '', isset($_POST['remove_image']), post_string('image_url'), $upload['path']);
    if ($image['error']) {
        $errors['image'] = $image['error'];
    }

    if (!$errors) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE blogs SET title=?, content=?, image=?, date=? WHERE id=?");
                $stmt->execute([$values['title'], $values['content'], $image['path'], $values['date'], $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO blogs (title, content, image, date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$values['title'], $values['content'], $image['path'], $values['date']]);
            }
            if ($image['path'] !== ($blog['image'] ?? '')) {
                delete_stored_upload($blog['image'] ?? '', UPLOAD_DIR);
            }
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            error_log('Blog save failed: ' . $e->getMessage());
            $errors['db'] = 'Could not save the log entry. Please try again.';
        }
    }

    // Re-showing the form after a failed save: drop the image uploaded in this request
    if ($upload['path']) {
        delete_stored_upload($upload['path'], UPLOAD_DIR);
    }
    $blog = array_merge($blog, $values);
}
$currentImage = $blog['image'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>">
    <title><?= $id ? 'Edit' : 'Add' ?> Blog - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/main.css', '../')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/admin.css', '../')) ?>">
</head>
<body class="admin-body">
    <div class="window admin-window admin-window--form">
        <div class="window-bar"><span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span><h1 class="window-title admin-form-title"><?= $id ? 'Edit log' : 'New log' ?></h1></div>
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
                    <input type="text" id="title" name="title" maxlength="255" class="field" value="<?= e($blog['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="date" class="field-label">Date</label>
                    <input type="date" id="date" name="date" class="field" value="<?= e($blog['date']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="field-label">Content</label>
                    <textarea id="content" name="content" class="field" rows="6" required><?= e($blog['content']) ?></textarea>
                </div>

                <div class="mb-3 image-box">
                    <span class="field-label">Image</span>
                    <?php if ($currentImage !== ''): ?>
                        <div class="mb-2">
                            <img src="<?= e(is_http_url($currentImage) ? $currentImage : '../' . $currentImage) ?>" alt="Current log image" class="image-preview">
                            <br>
                            <input type="checkbox" name="remove_image" id="remove_image"> <label for="remove_image" class="remove-image-label">Remove image</label>
                        </div>
                    <?php endif; ?>

                    <label for="image_file" class="field-hint d-block mb-1">Upload a new image (JPG, PNG, GIF or WebP, up to 5 MB). It replaces the current one.</label>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp" class="field mb-3">

                    <label for="image_url" class="field-hint d-block mb-1">Or paste an image URL</label>
                    <input type="url" id="image_url" name="image_url" class="field" placeholder="https://..." value="<?= is_http_url($currentImage) ? e($currentImage) : '' ?>">
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
