<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KURSE CO.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <style>
        .admin-window {
            background: #c0c0c0;
            border: 2px outset #fff;
            box-shadow: 8px 8px 0 #000;
            margin-bottom: 30px;
        }
        .window-header {
            background: linear-gradient(90deg, navy, #1084d0);
            color: white;
            padding: 5px 10px;
            font-family: var(--main-font);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .window-body {
            padding: 20px;
        }
        .retro-table {
            width: 100%;
            background: #fff;
            border: 2px inset #dfdfdf;
        }
        .retro-table th {
            background: #d0d0d0;
            border-bottom: 1px solid #000;
            padding: 8px;
            font-family: var(--header-font);
            font-size: 0.8rem;
        }
        .retro-table td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
            font-family: var(--main-font);
            color: #000;
        }
        .btn-retro {
            background: #c0c0c0;
            border: 2px outset #fff;
            color: #000;
            font-weight: bold;
            text-decoration: none;
            padding: 2px 8px;
            font-size: 0.8rem;
            display: inline-block;
        }
        .btn-retro:active {
            border: 2px inset #fff;
        }
    </style>
</head>
<body class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="nama" style="font-size: 2rem;">ADMIN_PANEL_V1.0</h1>
        <a href="logout.php" class="btn-retro">LOGOUT</a>
    </div>

    <!-- Projects Management -->
    <div class="admin-window">
        <div class="window-header">
            <span>PROJECTS_MANAGER.EXE</span>
            <a href="project_form.php" class="btn-retro ms-2" style="border: 1px outset #fff;">+ ADD NEW</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TITLE</th>
                            <th>TECH STACK</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($pdo) {
                            $stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['tech_stack']) . "</td>";
                                echo "<td>
                                        <a href='project_form.php?id=" . $row['id'] . "' class='btn-retro'><i class='bi bi-pencil'></i></a>
                                        <a href='delete.php?type=project&id=" . $row['id'] . "' class='btn-retro text-danger' onclick='return confirm(\"Confirm delete?\")'><i class='bi bi-trash'></i></a>
                                      </td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Blog Management -->
    <div class="admin-window">
        <div class="window-header">
            <span>BLOG_MANAGER.EXE</span>
            <a href="blog_form.php" class="btn-retro ms-2" style="border: 1px outset #fff;">+ ADD NEW</a>
        </div>
        <div class="window-body">
            <div class="table-responsive">
                <table class="retro-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DATE</th>
                            <th>TITLE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($pdo) {
                            $stmt = $pdo->query("SELECT * FROM blogs ORDER BY date DESC");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['date'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>
                                        <a href='blog_form.php?id=" . $row['id'] . "' class='btn-retro'><i class='bi bi-pencil'></i></a>
                                        <a href='delete.php?type=blog&id=" . $row['id'] . "' class='btn-retro text-danger' onclick='return confirm(\"Confirm delete?\")'><i class='bi bi-trash'></i></a>
                                      </td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
