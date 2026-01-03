<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Sendhy Kurniawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .blog-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
        }

        .blog-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .blog-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark shadow-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">KURSE CO.</a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm">BACK TO HOME</a>
            </div>
        </div>
    </nav>

    <section class="container" style="margin-top: 120px;">
        <h2 class="nama text-center mb-5">SYSTEM LOGS</h2>

        <div class="row g-4">
            <?php
            $file = 'data/blogs.json';
            if (file_exists($file)) {
                $blogs = json_decode(file_get_contents($file), true);
                if ($blogs) {
                    foreach ($blogs as $blog) {
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="blog-card rounded overflow-hidden h-100">
                                <img src="<?php echo $blog['image']; ?>" class="blog-img" alt="Blog Image">
                                <div class="p-4">
                                    <small class="blog-date"><i class="bi bi-calendar-event"></i>
                                        <?php echo $blog['date']; ?></small>
                                    <h4 class="mt-2 mb-3 text-white" style="font-family: 'Orbitron', monospace;">
                                        <?php echo $blog['title']; ?>
                                    </h4>
                                    <p class="text-secondary"><?php echo substr($blog['content'], 0, 100) . '...'; ?></p>
                                    <button class="btn btn-outline-info btn-sm w-100 mt-2" data-bs-toggle="modal"
                                        data-bs-target="#blogModal" data-title="<?php echo htmlspecialchars($blog['title']); ?>"
                                        data-date="<?php echo $blog['date']; ?>" data-image="<?php echo $blog['image']; ?>"
                                        data-content="<?php echo htmlspecialchars($blog['content']); ?>">
                                        READ LOG
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-center text-muted'>No logs found in the system.</p>";
                }
            } else {
                echo "<p class='text-center text-danger'>System Error: Database not found.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Blog View Modal -->
    <div class="modal fade" id="blogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content"
                style="background: rgba(10, 10, 20, 0.95); border: 1px solid var(--primary-color); backdrop-filter: blur(10px);">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title nama" id="modalTitle">LOG TITLE</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <img src="" id="modalImage" class="w-100"
                        style="height: 300px; object-fit: cover; border-bottom: 1px solid var(--primary-color);">
                    <div class="p-4">
                        <small class="text-info mb-2 d-block" id="modalDate">DATE</small>
                        <p class="text-light" id="modalContent" style="white-space: pre-wrap;">Log content...</p>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">CLOSE
                        LOG</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const blogModal = document.getElementById('blogModal');
        blogModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const title = button.getAttribute('data-title');
            const date = button.getAttribute('data-date');
            const image = button.getAttribute('data-image');
            const content = button.getAttribute('data-content');

            blogModal.querySelector('#modalTitle').textContent = title;
            blogModal.querySelector('#modalDate').textContent = date;
            blogModal.querySelector('#modalImage').src = image;
            blogModal.querySelector('#modalContent').textContent = content;
        });
    </script>
</body>

</html>