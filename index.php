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
$visitors = track_visitor($pdo);

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

    // Guestbook wall needs the approval flag; older databases don't have it yet
    try {
        $pdo->query('SELECT approved FROM guestbook LIMIT 1');
    } catch (Exception $e) {
        include 'database/migrate_guestbook.php';
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
// The 1999 iMac colours; script/flavour-boot.js and script/flavour.js use the same keys
$flavours = ['bondi' => 'Bondi Blue', 'tangerine' => 'Tangerine', 'grape' => 'Grape', 'lime' => 'Lime', 'strawberry' => 'Strawberry'];
$emoticons = [':)' => ['🙂', 'Smile'], ':D' => ['😄', 'Big grin'], ';)' => ['😉', 'Wink'], ':P' => ['😛', 'Tongue out'], '<3' => ['❤️', 'Heart']];
$wall = fetch_public_guestbook($pdo);
$now = time();
$skills = collect_skills($projects);
// Scanned from img/certif/, read off the certificates themselves
$certificates = [
  ['file' => 'img/certif/sl_html.png', 'course' => 'HTML', 'issuer' => 'SoloLearn', 'issued' => '2019-06-09', 'serial' => 'CT-1A3FPKBF'],
  ['file' => 'img/certif/sl_css.png', 'course' => 'CSS', 'issuer' => 'SoloLearn', 'issued' => '2021-06-13', 'serial' => 'CT-KIFU8ZCT'],
];
$albums = array_map(
  static fn(array $album) => $album + ['images' => gallery_images(__DIR__ . '/' . $album['path'], $album['path'])],
  [
    ['name' => 'Anime edits', 'path' => 'img/projects/animeedit'],
    ['name' => 'Photo manipulations', 'path' => 'img/projects/manipulation'],
  ]
);
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
  <meta property="og:url" content="<?= e(absolute_url('index.php')) ?>" />
  <?php if (is_file(__DIR__ . '/img/og.jpg')): ?>
  <meta property="og:image" content="<?= e(absolute_url(asset_url('img/og.jpg'))) ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="KURSE CO. Millennium Edition — the portfolio of Sendhy Kurniawan" />
  <meta name="twitter:card" content="summary_large_image" />
  <?php endif; ?>
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
  <link rel="stylesheet" href="<?= e(asset_url('css/system.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/screensaver.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('css/mode-1999.css')) ?>">
  <!-- Applies the saved iMac flavour before first paint, so the page never flashes blue -->
  <script src="<?= e(asset_url('script/flavour-boot.js')) ?>"></script>
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
          <li class="nav-item"><a class="nav-link" href="#system">System</a></li>
          <li class="nav-item"><a class="nav-link" href="#pictures">Pictures</a></li>
          <li class="nav-item"><a class="nav-link" href="#blog">Logs</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Guestbook</a></li>
        </ul>
        <div class="nav-tools">
          <fieldset class="flavours">
            <legend class="visually-hidden">Window colour</legend>
            <?php foreach ($flavours as $value => $label): ?>
            <label class="flavour" title="<?= e($label) ?>">
              <input type="radio" name="flavour" value="<?= e($value) ?>" class="visually-hidden"<?= $value === 'bondi' ? ' checked' : '' ?>>
              <span class="flavour-dot flavour-dot--<?= e($value) ?>" aria-hidden="true"></span>
              <span class="visually-hidden"><?= e($label) ?></span>
            </label>
            <?php endforeach; ?>
          </fieldset>
          <button type="button" class="sound-toggle" aria-pressed="false" aria-label="Interface sounds" title="Interface sounds">
            <i class="bi bi-volume-mute" aria-hidden="true"></i>
          </button>
        </div>
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
            <?php if (is_recent($project['created_at'] ?? null, $now)): ?>
            <span class="sticker-new"><span>NEW!</span></span>
            <?php endif; ?>
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

    <section id="system" class="site-section" aria-labelledby="system-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="system-heading" class="nama section-title">System properties</h2>
          <p class="section-intro">What's installed on this machine, and the paperwork that came with it.</p>
        </div>

        <div class="window sysprops">
          <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="window-title">System Properties</span>
          </div>
          <div class="nav tab-strip" role="tablist" aria-label="System properties">
            <button type="button" class="tab active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general"
              role="tab" aria-controls="pane-general" aria-selected="true">General</button>
            <button type="button" class="tab" id="tab-skills" data-bs-toggle="tab" data-bs-target="#pane-skills"
              role="tab" aria-controls="pane-skills" aria-selected="false">Skills</button>
            <button type="button" class="tab" id="tab-certificates" data-bs-toggle="tab" data-bs-target="#pane-certificates"
              role="tab" aria-controls="pane-certificates" aria-selected="false">Certificates</button>
          </div>
          <div class="tab-content window-body sys-body">
            <div class="tab-pane fade show active" id="pane-general" role="tabpanel" aria-labelledby="tab-general" tabindex="0">
              <div class="sys-general">
                <div class="sys-emblem" aria-hidden="true"><span></span></div>
                <dl class="sys-spec">
                  <dt>System</dt>
                  <dd>KURSE CO. Millennium Edition<br>Hand-written PHP, no framework</dd>
                  <dt>Registered to</dt>
                  <dd>Sendhy Kurniawan<br><a href="mailto:sendhy27@gmail.com">sendhy27@gmail.com</a></dd>
                  <dt>Computer</dt>
                  <dd>
                    <?= count($projects) ?> <?= count($projects) === 1 ? 'program' : 'programs' ?> installed<br>
                    <?= count($skills) ?> skills detected<br>
                    <?= count($certificates) ?> certificates on file
                  </dd>
                </dl>
              </div>
            </div>

            <div class="tab-pane fade" id="pane-skills" role="tabpanel" aria-labelledby="tab-skills" tabindex="0">
              <p class="sys-note">Everything the programs above are built with, plus what runs this site.</p>
              <ul class="skill-list">
                <?php foreach ($skills as $skill): ?>
                <li class="skill"><span class="skill-mark" aria-hidden="true"></span><?= e($skill) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="tab-pane fade" id="pane-certificates" role="tabpanel" aria-labelledby="tab-certificates" tabindex="0">
              <ul class="certs">
                <?php foreach ($certificates as $cert): ?>
                <li class="cert">
                  <button type="button" class="cert-shot" data-viewer="certificates"
                    data-src="<?= e($cert['file']) ?>"
                    data-caption="<?= e($cert['course'] . ' course certificate, ' . $cert['issuer']) ?>">
                    <img src="<?= e($cert['file']) ?>" alt="<?= e($cert['course']) ?> certificate from <?= e($cert['issuer']) ?>"
                      loading="lazy" decoding="async" width="360" height="254">
                    <span class="cert-zoom" aria-hidden="true"><i class="bi bi-arrows-fullscreen"></i></span>
                  </button>
                  <div class="cert-meta">
                    <h3 class="cert-name"><?= e($cert['course']) ?> course</h3>
                    <p class="cert-issuer"><?= e($cert['issuer']) ?></p>
                    <p class="cert-date">Issued <time datetime="<?= e($cert['issued']) ?>"><?= e(date('j F Y', strtotime($cert['issued']))) ?></time></p>
                    <p class="cert-serial">Certificate <?= e($cert['serial']) ?></p>
                  </div>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="pictures" class="site-section" aria-labelledby="pictures-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="pictures-heading" class="nama section-title">My Pictures</h2>
          <p class="section-intro">Edits and photo manipulations I make away from client work. Open one to see it full size.</p>
        </div>

        <div class="window gallery-window">
          <div class="window-bar">
            <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="window-title">My Pictures</span>
          </div>
          <div class="window-body gallery-body">
            <?php foreach ($albums as $album): ?>
            <?php if ($album['images']): ?>
            <section class="album" aria-labelledby="album-<?= e(md5($album['path'])) ?>">
              <h3 class="album-name" id="album-<?= e(md5($album['path'])) ?>">
                <span class="folder" aria-hidden="true"></span><?= e($album['name']) ?>
                <span class="album-count"><?= count($album['images']) ?> items</span>
              </h3>
              <ul class="thumbs">
                <?php foreach ($album['images'] as $picture): ?>
                <?php $label = $album['name'] . ' ' . $picture['number']; ?>
                <li>
                  <button type="button" class="thumb" data-viewer="pictures"
                    data-src="<?= e($picture['src']) ?>" data-caption="<?= e($label) ?>">
                    <img src="<?= e($picture['src']) ?>" alt="<?= e($label) ?>" loading="lazy" decoding="async"
                      width="240" height="240">
                  </button>
                </li>
                <?php endforeach; ?>
              </ul>
            </section>
            <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
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
                  <span class="inbox-subject"><?= e($blog['title']) ?><?php if (is_recent($blog['date'], $now)): ?><span class="tag-new">NEW!</span><?php endif; ?></span>
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
          <p class="section-intro">Say hello. I read every message, and I post some of them here once I've approved them.</p>
        </div>

        <div class="messenger">
          <aside class="window buddy-card" aria-label="About Sendhy">
            <div class="window-bar">
              <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
              <span class="window-title">Buddy info</span>
            </div>
            <div class="window-body">
              <div class="buddy">
                <img src="img/avatar.png" alt="" class="buddy-avatar" width="64" height="64">
                <div>
                  <p class="buddy-name">Sendhy Kurniawan</p>
                  <p class="buddy-status"><span class="status-dot" aria-hidden="true"></span>Available for projects</p>
                </div>
              </div>
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
          </aside>

          <div class="window chat-window" id="chatWindow">
            <div class="window-bar">
              <span class="gel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
              <span class="window-title">Chat with Sendhy</span>
            </div>
            <div class="chat-log" role="log" aria-label="Conversation">
              <p class="chat-line"><span class="chat-name">Sendhy:</span> Hi! Thanks for stopping by. Leave a message below and I'll reply by email.</p>
              <?php foreach ($wall as $entry): ?>
              <p class="chat-line">
                <span class="chat-name"><?= e($entry['name']) ?>:</span>
                <?= e($entry['message']) ?>
                <time class="chat-time" datetime="<?= e(substr((string) $entry['created_at'], 0, 10)) ?>"><?= e(substr((string) $entry['created_at'], 0, 10)) ?></time>
              </p>
              <?php endforeach; ?>
              <?php if ($flash && $flash['type'] === 'success'): ?>
              <p class="chat-line chat-line--system" role="status"><?= e($flash['message']) ?></p>
              <?php endif; ?>
            </div>

            <form method="post" action="index.php#contact" class="chat-compose">
              <?php if ($flash && $flash['type'] !== 'success'): ?>
              <div class="guestbook-flash--error" role="alert"><?= e($flash['message']) ?></div>
              <?php endif; ?>
              <?= csrf_field() ?>
              <!-- Honeypot: hidden from people, filled in by bots -->
              <div class="guestbook-hp" aria-hidden="true">
                <label for="website">Leave this field empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
              </div>
              <div class="field-row">
                <div>
                  <label for="gb-name" class="field-label">Your name</label>
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
                <div class="chat-toolbar">
                  <?php foreach ($emoticons as $code => [$glyph, $label]): ?>
                  <button type="button" class="emoticon" data-emoticon="<?= e($code) ?>" aria-label="Insert <?= e(strtolower($label)) ?>" title="<?= e($label . ' ' . $code) ?>"><?= $glyph ?></button>
                  <?php endforeach; ?>
                  <button type="button" class="buzz-btn">BUZZ!!!</button>
                </div>
                <label for="gb-message" class="field-label">Message</label>
                <textarea id="gb-message" name="pesan" class="field" rows="4" maxlength="2000" required><?= e($old['message'] ?? '') ?></textarea>
              </div>
              <div class="form-foot">
                <p class="form-note">Ctrl + Enter sends. Your message stays private until I approve it.</p>
                <button type="submit" class="btn-gel">Send</button>
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

  <!-- Picture viewer, shared by the gallery and the certificates -->
  <div class="modal fade viewer-modal" id="viewerModal" tabindex="-1" aria-labelledby="viewerTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="window-bar">
          <h2 class="window-title viewer-title" id="viewerTitle">Preview</h2>
          <button type="button" class="gel-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="viewer-stage">
          <img id="viewerImage" class="viewer-image" alt="">
        </div>
        <div class="viewer-foot">
          <button type="button" class="btn-gel btn-gel--chrome btn-gel--small" data-viewer-step="-1">
            <i class="bi bi-chevron-left" aria-hidden="true"></i> Previous
          </button>
          <p class="viewer-count" id="viewerCount" aria-live="polite"></p>
          <button type="button" class="btn-gel btn-gel--chrome btn-gel--small" data-viewer-step="1">
            Next <i class="bi bi-chevron-right" aria-hidden="true"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <div class="container">
      <p>Designed and built by <a href="https://www.instagram.com/kurniawansendhy/" target="_blank" rel="noopener noreferrer">Sendhy Kurniawan</a></p>
      <span class="hit-counter lcd">
        <span>Visitors</span>
        <span class="hit-counter-digits" aria-hidden="true"><?= e(visitor_counter_digits($visitors)) ?></span>
        <span class="visually-hidden"><?= $visitors === null ? 'unavailable' : (int) $visitors ?></span>
      </span>
      <ul class="web-badges" aria-label="Site badges">
        <li><span class="web-badge web-badge--lcd"><b>Y2K</b><span>Compliant</span></span></li>
        <li><span class="web-badge web-badge--ink"><b>PHP</b><span>Powered</span></span></li>
        <li><a class="web-badge web-badge--tangerine" href="#contact"><b aria-hidden="true">✍</b><span>Guest&shy;book</span></a></li>
        <li><span class="web-badge"><b aria-hidden="true">🖥</b><span>800&times;600 best viewed</span></span></li>
      </ul>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="<?= e(asset_url('script/sound.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/flavour.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/blog.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/messenger.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/gallery.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/screensaver.js')) ?>" defer></script>
  <script src="<?= e(asset_url('script/konami.js')) ?>" defer></script>
</body>

</html>
