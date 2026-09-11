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

$projects = [];
$blogs = [];
if ($pdo) {
    // Auto-migration check
    try {
        $pdo->query("SELECT 1 FROM projects LIMIT 1");
    } catch (Exception $e) {
        // Table likely missing, run migration
        include 'database/migrate_projects.php';
    }

    try {
        $projects = $pdo->query("SELECT * FROM projects ORDER BY id ASC")->fetchAll();
    } catch (Exception $e) {
        error_log('Project query failed: ' . $e->getMessage());
    }
    try {
        $blogs = $pdo->query("SELECT * FROM blogs ORDER BY date DESC")->fetchAll();
    } catch (PDOException $e) {
        error_log('Blog query failed: ' . $e->getMessage());
    }
}

$ticker = "+++ WELCOME TO THE CYBER ZONE +++ EST. 2026 +++ LAST UPDATED: TODAY +++ DON'T FORGET TO SIGN THE GUESTBOOK +++ CONSTRUCTING DIGITAL REALITIES";
$sparkle = '<path fill="currentColor" d="M12 0C12.9 7.6 16.4 11.1 24 12 16.4 12.9 12.9 16.4 12 24 11.1 16.4 7.6 12.9 0 12 7.6 11.1 11.1 7.6 12 0Z"/>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sendhy Kurniawan | KURSE CO.</title>
  <meta name="description" content="Sendhy Kurniawan designs and builds websites. Projects, notes and a guestbook, served Millennium Edition style." />
  <meta name="theme-color" content="#eef1f8" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Sendhy Kurniawan | KURSE CO." />
  <meta property="og:description" content="Websites designed and built by Sendhy Kurniawan." />
  <!-- Fonts (main.css @imports the same sheet; linking it here starts the download earlier) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Michroma&family=VT323&display=swap" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="<?= e(asset_url('css/main.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/navbar.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/hero.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/projects.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/guestbook.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/modal.css')) ?>">
</head>

<body>
  <a class="visually-hidden-focusable skip-link" href="#projects">Skip to programs</a>

  <header class="site-header">
    <nav class="nav-capsule navbar navbar-expand-md" aria-label="Main navigation">
      <a class="nav-brand" href="#home"><span class="nav-orb" aria-hidden="true"></span>KURSE CO.</a>
      <button class="navbar-toggler nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav"
        aria-controls="siteNav" aria-expanded="false" aria-label="Open menu">
        <i class="bi bi-list" aria-hidden="true"></i>
      </button>
      <div class="collapse navbar-collapse" id="siteNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#projects">Programs</a></li>
          <li class="nav-item"><a class="nav-link" href="#blog">System logs</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Guestbook</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <section class="hero" id="home" aria-labelledby="hero-title">
      <div class="container">
        <div class="ticker lcd">
          <div class="ticker-track">
            <span><?= e($ticker) ?></span>
            <span aria-hidden="true"><?= e($ticker) ?></span>
          </div>
        </div>

        <div class="hero-grid">
          <div class="hero-copy">
            <h1 class="chrome-wordmark" id="hero-title">
              <span class="visually-hidden">Sendhy Kurniawan: </span>
              <span class="chrome-line">KURSE</span>
              <span class="chrome-line">CO.</span>
            </h1>
            <p class="hero-lede">Hi, I'm Sendhy Kurniawan. I design and build websites, from school event pages to shop fronts.</p>
            <div class="hero-actions">
              <a href="mailto:sendhy27@gmail.com" class="btn-gel btn-gel--tangerine">Hire me</a>
              <a href="#contact" class="btn-gel btn-gel--chrome">Sign the guestbook</a>
              <?php if ($hasResume): ?>
              <a href="resume.pdf" class="btn-gel btn-gel--chrome" download><i class="bi bi-download" aria-hidden="true"></i> Resume</a>
              <?php endif; ?>
            </div>
          </div>

          <div class="disc-wrap" aria-hidden="true">
            <div class="disc"></div>
            <div class="disc-shine"></div>
            <div class="disc-rim"></div>
            <svg class="sparkle sparkle--a" viewBox="0 0 24 24"><?= $sparkle ?></svg>
            <svg class="sparkle sparkle--b" viewBox="0 0 24 24"><?= $sparkle ?></svg>
          </div>
        </div>
      </div>
    </section>

    <section id="projects" class="site-section" aria-labelledby="projects-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="projects-heading" class="nama section-title">Programs</h2>
          <p class="section-intro">Sites I've designed and built. Each one opens in a new tab.</p>
        </div>

        <?php if ($projects): ?>
        <div class="programs">
          <?php foreach ($projects as $project): ?>
          <article class="window program">
            <div class="window-bar">
              <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
              <span class="window-title"><?= e($project['file_name']) ?></span>
            </div>
            <?php if (!empty($project['image'])): ?>
            <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?> screenshot" class="program-shot" loading="lazy" decoding="async" width="800" height="240">
            <?php endif; ?>
            <div class="window-body program-body">
              <h3 class="program-title"><?= e($project['title']) ?></h3>
              <p class="program-desc"><?= e($project['description']) ?></p>
              <div class="program-foot">
                <ul class="chips" aria-label="Built with">
                  <?php foreach (array_filter(array_map('trim', explode(',', $project['tech_stack']))) as $tech): ?>
                  <li class="chip"><?= e($tech) ?></li>
                  <?php endforeach; ?>
                </ul>
                <a href="<?= e(safe_url($project['link'])) ?>" target="_blank" rel="noopener noreferrer" class="btn-gel btn-gel--small">Open site <span class="visually-hidden">(<?= e($project['title']) ?>, opens in new tab)</span></a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="window empty-state"><?= $pdo ? 'No programs installed yet.' : "The database is offline, so programs can't load right now." ?></p>
        <?php endif; ?>
      </div>
    </section>

    <section id="blog" class="site-section" aria-labelledby="blog-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="blog-heading" class="nama section-title">System logs</h2>
          <p class="section-intro">Notes and updates, newest first.</p>
        </div>

        <div class="window inbox">
          <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="window-title">Inbox</span>
            <span class="inbox-count"><?= count($blogs) ?> <?= count($blogs) === 1 ? 'message' : 'messages' ?></span>
          </div>
          <?php if ($blogs): ?>
          <div class="inbox-head" aria-hidden="true"><span>Received</span><span>Subject</span></div>
          <ul class="inbox-list">
            <?php foreach ($blogs as $blog): ?>
            <li>
              <button type="button" class="inbox-row"
                data-bs-toggle="modal"
                data-bs-target="#blogModal"
                data-title="<?= e($blog['title']) ?>"
                data-date="<?= e($blog['date']) ?>"
                data-image="<?= e($blog['image'] ?? '') ?>"
                data-content="<?= e($blog['content']) ?>">
                <time class="inbox-date" datetime="<?= e($blog['date']) ?>"><?= e($blog['date']) ?></time>
                <span>
                  <span class="inbox-subject"><?= e($blog['title']) ?></span>
                  <span class="inbox-preview"><?= e(excerpt($blog['content'], 110)) ?></span>
                </span>
              </button>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="empty-state"><?= $pdo ? 'No logs yet. Check back soon.' : "The database is offline, so logs can't load right now." ?></p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section id="contact" class="site-section" aria-labelledby="contact-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="contact-heading" class="nama section-title">Guestbook</h2>
          <p class="section-intro">Leave a message. It goes to my inbox, not a public wall.</p>
        </div>

        <div class="window">
          <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="window-title">Sign my guestbook</span>
          </div>
          <div class="window-body guestbook-grid">
            <div>
              <h3 class="guestbook-subhead">Find me elsewhere</h3>
              <ul class="desk-icons">
                <li>
                  <a class="desk-icon" href="https://www.instagram.com/kurniawansendhy/" target="_blank" rel="noopener noreferrer">
                    <span class="desk-icon-tile" aria-hidden="true"><i class="bi bi-instagram"></i></span>
                    <span class="desk-icon-label">Instagram</span>
                  </a>
                </li>
                <li>
                  <a class="desk-icon" href="https://github.com/SendhyKurniawan" target="_blank" rel="noopener noreferrer">
                    <span class="desk-icon-tile" aria-hidden="true"><i class="bi bi-github"></i></span>
                    <span class="desk-icon-label">GitHub</span>
                  </a>
                </li>
                <li>
                  <a class="desk-icon" href="mailto:sendhy27@gmail.com">
                    <span class="desk-icon-tile" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                    <span class="desk-icon-label">Email</span>
                  </a>
                </li>
              </ul>
            </div>

            <form method="post" action="index.php#contact" class="guestbook-form">
              <?php if ($flash): ?>
              <div class="guestbook-flash <?= $flash['type'] === 'success' ? 'lcd' : 'guestbook-flash--error' ?>"
                role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
                <?= e($flash['message']) ?>
              </div>
              <?php endif; ?>
              <?= csrf_field() ?>
              <!-- Honeypot: hidden from people, filled in by bots -->
              <div class="guestbook-hp" aria-hidden="true">
                <label for="website">Leave this field empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
              </div>
              <div class="field-row">
                <div>
                  <label for="gb-name" class="field-label">Name</label>
                  <input type="text" id="gb-name" name="nama" class="field" maxlength="100" required
                    autocomplete="name" value="<?= e($old['name'] ?? '') ?>">
                </div>
                <div>
                  <label for="gb-email" class="field-label">Email <span class="field-hint">(only I see it)</span></label>
                  <input type="email" id="gb-email" name="email" class="field" maxlength="100" required
                    autocomplete="email" value="<?= e($old['email'] ?? '') ?>">
                </div>
              </div>
              <div>
                <label for="gb-message" class="field-label">Message</label>
                <textarea id="gb-message" name="pesan" class="field" rows="5" maxlength="2000" required><?= e($old['message'] ?? '') ?></textarea>
              </div>
              <div class="form-foot">
                <p class="form-note">One message a minute, please.</p>
                <button type="submit" class="btn-gel">Sign guestbook</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Log viewer -->
  <div class="modal fade log-modal" id="blogModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="window-bar">
          <h2 class="log-modal-title window-title" id="modalTitle">Log</h2>
          <button type="button" class="gel-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <img id="modalImage" class="log-modal-image" alt="" hidden>
        <div class="log-modal-body">
          <time class="log-modal-date" id="modalDate"></time>
          <p class="log-modal-content" id="modalContent"></p>
        </div>
        <div class="log-modal-foot">
          <button type="button" class="btn-gel btn-gel--chrome btn-gel--small" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <div class="container">
      <p>Designed and built by <a href="https://www.instagram.com/kurniawansendhy/" target="_blank" rel="noopener noreferrer">Sendhy Kurniawan</a></p>
      <span class="hit-counter lcd"><span>Visitors</span> <span class="hit-counter-digits">031337</span></span>
      <p>Best viewed at 800 &times; 600 in Internet Explorer 5</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="<?= e(asset_url('script/blog.js')) ?>" defer></script>
</body>

</html>
