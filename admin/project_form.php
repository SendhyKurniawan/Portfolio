<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$project = [
    'title' => '',
    'description' => '',
    'tech_stack' => '',
    'image' => '',
    'link' => '',
    'file_name' => ''
];

if ($id && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch();
    if ($fetched) $project = $fetched;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $tech_stack = $_POST['tech_stack'];
    $link = $_POST['link'];
    $file_name = $_POST['file_name'];
    
    // Image Handling
    $image_path = $project['image']; // Default to existing
    
    // Check if "Remove Image" is selected
    if (isset($_POST['remove_image'])) {
        $image_path = '';
    }
    
    // Check if new file uploaded
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . basename($_FILES['image_file']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
            $image_path = 'uploads/' . $fileName; // Path to save in DB
        }
    } 
    // Fallback to URL input if provided and no file uploaded (and not removed)
    elseif (!empty($_POST['image_url']) && !isset($_POST['remove_image']) && empty($_FILES['image_file']['name'])) {
        $image_path = $_POST['image_url'];
    }

    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, tech_stack=?, image=?, link=?, file_name=? WHERE id=?");
        $stmt->execute([$title, $description, $tech_stack, $image_path, $link, $file_name, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, tech_stack, image, link, file_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $tech_stack, $image_path, $link, $file_name]);
    }
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Project - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .admin-window {
            max-width: 600px;
            margin: 50px auto;
            background: #c0c0c0;
            border: 2px outset #fff;
            box-shadow: 8px 8px 0 #000;
        }
        .window-header {
            background: linear-gradient(90deg, navy, #1084d0);
            color: white;
            padding: 5px 10px;
            font-family: var(--main-font);
            font-weight: bold;
        }
        .window-body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-window">
        <div class="window-header"><?php echo $id ? 'EDIT_PROJECT.EXE' : 'NEW_PROJECT.EXE'; ?></div>
        <div class="window-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Title</label>
                    <input type="text" name="title" class="form-control rounded-0" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">File Name (For window header, e.g. APP.EXE)</label>
                    <input type="text" name="file_name" class="form-control rounded-0" value="<?php echo htmlspecialchars($project['file_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Description</label>
                    <textarea name="description" class="form-control rounded-0" rows="3" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Tech Stack (comma separated)</label>
                    <input type="text" name="tech_stack" class="form-control rounded-0" value="<?php echo htmlspecialchars($project['tech_stack']); ?>" required>
                </div>
                
                <div class="mb-3 p-2 bg-white border">
                    <label class="form-label text-dark fw-bold">Image</label>
                    <?php if (!empty($project['image'])): ?>
                        <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($project['image']); ?>" style="height: 100px; object-fit: cover; border: 1px solid #000;">
                            <br>
                            <input type="checkbox" name="remove_image" id="remove_image"> <label for="remove_image" class="text-danger small">Remove Image</label>
                        </div>
                    <?php endif; ?>
                    
                    <label class="small text-muted">Upload New Image (Overrides existing)</label>
                    <input type="file" name="image_file" class="form-control rounded-0 mb-2">
                    
                    <label class="small text-muted">OR Image URL (Fallback)</label>
                    <input type="text" name="image_url" class="form-control rounded-0" placeholder="https://..." value="<?php echo filter_var($project['image'], FILTER_VALIDATE_URL) ? htmlspecialchars($project['image']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Project Link</label>
                    <input type="text" name="link" class="form-control rounded-0" value="<?php echo htmlspecialchars($project['link']); ?>" required>
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
