<?php
require_once 'lib/app.php';
require_once 'config/db.php';
start_session();

// Guestbook: POST -> redirect -> GET so a refresh never resubmits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'])) {
    $result = handle_guestbook_post($pdo);
    flash_set($result['type'], $result['message'], $result['old']);
    header('Location: index.php#contact', true, 303);
    exit;
}
$flash = flash_take();
$old = $flash['old'] ?? [];
$hasResume = is_file(__DIR__ . '/resume.pdf');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sendhy Kurniawan | Creative Dev</title>
  <meta name="description" content="Portfolio of Sendhy Kurniawan: web projects, experiments and system logs, served Y2K style." />
  <meta name="theme-color" content="#000000" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💾</text></svg>" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Sendhy Kurniawan | Creative Dev" />
  <meta property="og:description" content="Web projects, experiments and system logs, served Y2K style." />
  <!-- Fonts (main.css @imports the same sheet; linking it here starts the download earlier) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
  <!-- Bootstrap Icon -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
  <!-- My CSS -->
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/hero.css">
  <link rel="stylesheet" href="css/projects.css">
  <link rel="stylesheet" href="css/guestbook.css">
  <link rel="stylesheet" href="css/modal.css">
</head>

<body>
  <!-- Navbar -->
  <a class="visually-hidden-focusable skip-link" href="#projects">Skip to content</a>
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark shadow-lg" id="main-navbar" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="https://win98icons.alexmeub.com/icons/png/computer_explorer-4.png" width="20" height="20" alt="">
        KURSE CO.
        <div class="window-controls ms-2 d-none d-sm-flex" aria-hidden="true">
            <div class="win-btn" title="Minimize">_</div>
            <div class="win-btn" title="Maximize">□</div>
            <div class="win-btn" title="Close" onclick="alert('SYSTEM ERROR: CANNOT CLOSE MAIN PROCESS')">X</div>
        </div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
        aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#projects">Projects</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#blog">System Logs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Guestbook</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar -->
  <div class="marquee-container">
    <div class="marquee-track">
      <span>+++ WELCOME TO THE CYBER ZONE +++ EST. 2026 +++ LAST UPDATED: TODAY +++ DON'T FORGET TO SIGN THE GUESTBOOK +++ CONSTRUCTING DIGITAL REALITIES +++&nbsp;</span>
      <span aria-hidden="true">+++ WELCOME TO THE CYBER ZONE +++ EST. 2026 +++ LAST UPDATED: TODAY +++ DON'T FORGET TO SIGN THE GUESTBOOK +++ CONSTRUCTING DIGITAL REALITIES +++&nbsp;</span>
    </div>
  </div>

  <main>
  <!-- Home -->
  <section class="py-5 px-3 px-md-5" id="home">
    <div class="container hero-container text-center d-flex flex-column justify-content-center align-items-center">
      <h1 class="hero-title" data-text="KURNIAWAN SENDHY">KURNIAWAN SENDHY</h1>
      <div class="hero-subtitle" data-text="[ ARCHITECTING DIGITAL EXPERIENCES ]">[ ARCHITECTING DIGITAL EXPERIENCES ]</div>
      
      <div class="mt-5 d-flex gap-3 justify-content-center flex-wrap">
        <a href="mailto:sendhy27@gmail.com" class="btn btn-outline-light">HIRE ME</a>
        <a href="#contact" class="btn btn-outline-light">CONTACT ME</a>
        <?php if ($hasResume): ?>
        <a href="resume.pdf" class="btn btn-outline-light" download><i class="bi bi-download" aria-hidden="true"></i> RESUME</a>
        <?php endif; ?>
      </div>
    </div>
  </section>


  <!-- Home -->

  <!-- projects -->
  <section id="projects" class="py-5 px-3 px-md-5">
    <div class="container">
      <h2 class="nama section-title text-center mb-5">PROJECTS_</h2>
      
      <div class="projects-grid">
        <?php
        $projects = [];
        if ($pdo) {
            // Auto-migration check
            try {
                $check = $pdo->query("SELECT 1 FROM projects LIMIT 1");
            } catch (Exception $e) {
                // Table likely missing, run migration
                include 'database/migrate_projects.php';
            }

            try {
                $stmt = $pdo->query("SELECT * FROM projects ORDER BY id ASC");
                $projects = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log('Project query failed: ' . $e->getMessage());
            }
        }

        if (!empty($projects)) {
            foreach ($projects as $project) {
                ?>
                <article class="y2k-project-card">
                  <div class="y2k-window-header">
                    <span><?= e($project['file_name']) ?></span>
                    <div class="y2k-window-controls" aria-hidden="true">
                      <div class="y2k-control-btn">_</div>
                      <div class="y2k-control-btn">X</div>
                    </div>
                  </div>
                  <div class="y2k-card-body">
                    <?php if (!empty($project['image'])): ?>
                    <div class="y2k-image-container">
                      <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?> screenshot" class="y2k-project-img" loading="lazy" decoding="async" width="600" height="200">
                    </div>
                    <?php endif; ?>
                    <div class="y2k-project-info">
                      <h3 class="y2k-project-title"><?= e($project['title']) ?></h3>
                      <p class="y2k-project-desc"><?= e($project['description']) ?></p>
                      <div class="y2k-tech-stack">
                        <?php foreach (array_filter(array_map('trim', explode(',', $project['tech_stack']))) as $tech): ?>
                          <span class="y2k-badge"><?= e($tech) ?></span>
                        <?php endforeach; ?>
                      </div>
                      <a href="<?= e(safe_url($project['link'])) ?>" target="_blank" rel="noopener noreferrer" class="y2k-action-btn">VISIT SITE <span class="visually-hidden">(<?= e($project['title']) ?>, opens in new tab)</span></a>
                    </div>
                  </div>
                </article>
                <?php
            }
        } else {
            echo "<div class='console-text text-center text-muted'>NO PROJECTS LOADED_</div>";
        }
        ?>
      </div>
    </div>
  </section>
  <!-- projects -->
  
  <!-- System Logs (Blog) -->
  <section id="blog" class="py-5 px-3 px-md-5">
    <div class="container">
      <h2 class="nama section-title text-center mb-5">SYSTEM_LOGS</h2>
      <div class="projects-grid">
        <?php
        if ($pdo) {
            $blogs = [];
            try {
                $blogs = $pdo->query("SELECT * FROM blogs ORDER BY date DESC")->fetchAll();
            } catch (PDOException $e) {
                error_log('Blog query failed: ' . $e->getMessage());
            }

            if ($blogs) {
                foreach ($blogs as $blog) {
                    $blogImage = $blog['image'] ?? '';
                    ?>
                    <article class="y2k-project-card">
                        <div class="y2k-window-header">
                            <span>LOG_FILE_<?= e(str_replace('-', '', $blog['date'])) ?>.TXT</span>
                            <div class="y2k-window-controls" aria-hidden="true">
                                <div class="y2k-control-btn">_</div>
                                <div class="y2k-control-btn">X</div>
                            </div>
                        </div>
                        <div class="y2k-card-body">
                            <?php if ($blogImage !== ''): ?>
                            <div class="y2k-image-container">
                                <img src="<?= e($blogImage) ?>" class="y2k-project-img" alt="" loading="lazy" decoding="async" width="600" height="200">
                            </div>
                            <?php endif; ?>
                            <div class="y2k-project-info">
                                <time class="y2k-badge mb-2" style="width: fit-content;" datetime="<?= e($blog['date']) ?>"><?= e($blog['date']) ?></time>
                                <h3 class="y2k-project-title"><?= e($blog['title']) ?></h3>
                                <p class="y2k-project-desc"><?= e(excerpt($blog['content'])) ?></p>
                                <button type="button" class="y2k-action-btn w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#blogModal"
                                    data-title="<?= e($blog['title']) ?>"
                                    data-date="<?= e($blog['date']) ?>"
                                    data-image="<?= e($blogImage) ?>"
                                    data-content="<?= e($blog['content']) ?>">
                                    [ ACCESS DATA ]
                                </button>
                            </div>
                        </div>
                    </article>
                    <?php
                }
            } else {
                echo "<div class='console-text text-center text-muted col-12'>> NO LOGS FOUND IN DATABASE_</div>";
            }
        } else {
            echo "<div class='console-text text-center text-danger col-12'>> CRITICAL ERROR: DATABASE DISCONNECTED_</div>";
        }
        ?>
      </div>
    </div>
  </section>
  <!-- System Logs -->

  <!-- Guestbook / Contact -->
  <section id="contact" class="py-5 px-3 px-md-5" aria-labelledby="contact-heading">
    <div class="container">
      <h2 id="contact-heading" class="nama section-title text-center mb-5">GUESTBOOK_</h2>
      <div class="social-wrapper">
        <div class="social-header">
          <span>KURSE.CO &raquo; Guestbook</span>
          <span class="social-breadcrumbs d-none d-sm-inline">Home &gt; Profile &gt; Sign Guestbook</span>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <h3 class="guestbook-title">Connections (3)</h3>
            <div class="row g-2">
              <div class="col-4">
                <a class="connection-card" href="https://www.instagram.com/kurniawansendhy/" target="_blank" rel="noopener noreferrer">
                  <span class="connection-img" aria-hidden="true"><i class="bi bi-instagram"></i></span>
                  <span class="connection-name">Instagram</span>
                </a>
              </div>
              <div class="col-4">
                <a class="connection-card" href="https://github.com/SendhyKurniawan" target="_blank" rel="noopener noreferrer">
                  <span class="connection-img" aria-hidden="true"><i class="bi bi-github"></i></span>
                  <span class="connection-name">GitHub</span>
                </a>
              </div>
              <div class="col-4">
                <a class="connection-card" href="mailto:sendhy27@gmail.com">
                  <span class="connection-img" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                  <span class="connection-name">Email</span>
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <div class="guestbook-main">
              <h3 class="guestbook-title">Sign my Guestbook!</h3>
              <?php if ($flash): ?>
                <div class="guestbook-flash guestbook-flash--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"
                  role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
                  <?= e($flash['message']) ?>
                </div>
              <?php endif; ?>
              <form method="post" action="index.php#contact">
                <?= csrf_field() ?>
                <!-- Honeypot: hidden from people, filled in by bots -->
                <div class="guestbook-hp" aria-hidden="true">
                  <label for="website">Leave this field empty</label>
                  <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-sm-6">
                    <label for="gb-name" class="guestbook-label">Name</label>
                    <input type="text" id="gb-name" name="nama" class="guestbook-input" maxlength="100" required
                      autocomplete="name" value="<?= e($old['name'] ?? '') ?>">
                  </div>
                  <div class="col-sm-6">
                    <label for="gb-email" class="guestbook-label">Email (only I can see it)</label>
                    <input type="email" id="gb-email" name="email" class="guestbook-input" maxlength="100" required
                      autocomplete="email" value="<?= e($old['email'] ?? '') ?>">
                  </div>
                </div>
                <label for="gb-message" class="guestbook-label">Message</label>
                <div class="formatting-toolbar" aria-hidden="true">
                  <span class="toolbar-btn">B</span>
                  <span class="toolbar-btn"><i>I</i></span>
                  <span class="toolbar-btn"><u>U</u></span>
                </div>
                <textarea id="gb-message" name="pesan" class="guestbook-input" rows="5" maxlength="2000" required><?= e($old['message'] ?? '') ?></textarea>
                <div class="d-flex justify-content-between align-items-center mt-2 gap-2 flex-wrap">
                  <small class="guestbook-note">Messages go straight to my inbox, not a public wall.</small>
                  <button type="submit" class="guestbook-submit">Post Comment</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Guestbook / Contact -->
  </main>

  <!-- Blog View Modal -->
  <div class="modal fade" id="blogModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content y2k-modal-content">
        <div class="modal-header y2k-modal-header">
          <h5 class="modal-title y2k-modal-title" id="modalTitle">LOG_VIEWER.EXE</h5>
          <button type="button" class="y2k-btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
        </div>
        <div class="modal-body y2k-modal-body">
          <img id="modalImage" class="w-100" alt="" hidden style="height: 300px; object-fit: cover; border-bottom: 2px solid var(--primary-color);">
          <div class="y2k-modal-data">
            <small class="text-info mb-2 d-block" id="modalDate" style="font-family: var(--main-font);">> TIMESTAMP: DATE</small>
            <p class="text-dark modal-text-content" id="modalContent">Log content...</p>
          </div>
        </div>
        <div class="modal-footer y2k-modal-footer">
            <button type="button" class="btn btn-sm" style="border: 2px outset #fff; background: #c0c0c0; color: #000; font-weight: bold;" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p class="text-white text-center mb-0">DESIGNED BY <a href="https://www.instagram.com/kurniawansendhy/" target="_blank" rel="noopener noreferrer">SENDHY
        KURNIAWAN</a> | SYSTEM ONLINE</p>
    <div class="text-center mt-3 footer-info">
       [ 800x600 ] [ IE 5.0 ] [ NOTEPAD ] <br>
       <span class="visitor-count">[ VISITOR COUNT: 031337 ]</span>
    </div>
  </footer>
  <!-- Footer -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <!-- My JS -->
  <script src="script/hero.js" defer></script>
  <script src="script/blog.js" defer></script>
  <script src="script/background.js" defer></script>


</body>

</html>