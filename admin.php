<?php
session_start();
if (isset($_POST['login'])) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
    } else {
        $error = "Access Denied.";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Handle Form Submission
if (isset($_POST['submit_blog']) && isset($_SESSION['admin'])) {
    $file = 'data/blogs.json';
    $current_data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    $image_path = "https://placehold.co/600x400/050510/00f3ff?text=New+Post"; // Default

    // Simple Image Upload (for local laragon)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "img/uploads/";
        if (!is_dir($target_dir))
            mkdir($target_dir);
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $new_blog = [
        "id" => uniqid(),
        "title" => $_POST['title'],
        "date" => date("Y-m-d"),
        "content" => $_POST['content'],
        "image" => $image_path
    ];

    array_unshift($current_data, $new_blog);
    file_put_contents($file, json_encode($current_data, JSON_PRETTY_PRINT));
    $success = "Log entry recorded.";
}

// Handle Delete
if (isset($_GET['delete']) && isset($_SESSION['admin'])) {
    $file = 'data/blogs.json';
    $current_data = json_decode(file_get_contents($file), true);
    foreach ($current_data as $key => $blog) {
        if ($blog['id'] == $_GET['delete']) {
            unset($current_data[$key]);
        }
    }
    file_put_contents($file, json_encode(array_values($current_data), JSON_PRETTY_PRINT));
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container mt-5">
        <?php if (!isset($_SESSION['admin'])): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="terminal-window">
                        <div class="terminal-header">
                            <div class="terminal-title">auth_required.exe</div>
                        </div>
                        <div class="terminal-body p-4">
                            <form method="POST">
                                <h4 class="text-danger mb-4">> AUTHENTICATION REQUIRED</h4>
                                <?php if (isset($error))
                                    echo "<p class='text-danger'>$error</p>"; ?>
                                <input type="password" name="password" class="form-control terminal-input mb-3"
                                    placeholder="enter_passcode">
                                <button type="submit" name="login" class="btn w-100 terminal-btn">>> ACCESS_SYSTEM
                                    >></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="nama">COMMAND CENTER</h2>
                <a href="?logout=true" class="btn btn-outline-danger btn-sm">LOGOUT</a>
            </div>

            <div class="row">
                <!-- Add New Blog -->
                <div class="col-lg-5">
                    <div class="terminal-window mb-4">
                        <div class="terminal-header">
                            <div class="terminal-title">new_entry.exe</div>
                        </div>
                        <div class="terminal-body p-4">
                            <?php if (isset($success))
                                echo "<p class='text-success'>$success</p>"; ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="text-primary">> TITLE</label>
                                    <input type="text" name="title" class="form-control terminal-input" required>
                                </div>
                                <div class="mb-3">
                                    <label class="text-primary">> CONTENT</label>
                                    <textarea name="content" class="form-control terminal-input" rows="5"
                                        required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="text-primary">> VISUAL_DATA (IMAGE)</label>
                                    <input type="file" name="image" class="form-control terminal-input">
                                </div>
                                <button type="submit" name="submit_blog" class="btn w-100 terminal-btn">>> UPLOAD_DATA
                                    >></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- List Blogs -->
                <div class="col-lg-7">
                    <div class="terminal-window">
                        <div class="terminal-header">
                            <div class="terminal-title">database_records.db</div>
                        </div>
                        <div class="terminal-body p-0">
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php
                                $blogs = json_decode(file_get_contents('data/blogs.json'), true);
                                if ($blogs) {
                                    foreach ($blogs as $blog) {
                                        echo "<li class='list-group-item bg-transparent text-white border-bottom border-secondary d-flex justify-content-between align-items-center'>";
                                        echo "<span><span class='text-primary'>></span> {$blog['title']} <small class='text-muted'>({$blog['date']})</small></span>";
                                        echo "<a href='?delete={$blog['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Terminate record?\")'>DELETE</a>";
                                        echo "</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>