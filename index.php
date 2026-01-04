<?php
require_once 'config/db.php';

// Handle Guestbook Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['email'], $_POST['pesan'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $pesan = htmlspecialchars($_POST['pesan']);

    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO guestbook (name, email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$nama, $email, $pesan])) {
            echo "<script>alert('Message transmitted successfully!');</script>";
        } else {
            echo "<script>alert('Transmission failed.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sendhy Kurniawan | Creative Dev</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
  <!-- Bootstrap Icon -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
  <!-- My CSS -->
  <!-- My CSS -->
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
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark shadow-lg" id="main-navbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="https://win98icons.alexmeub.com/icons/png/computer_explorer-4.png" width="20" height="20" alt="icon">
        KURSE CO.
        <div class="window-controls ms-2">
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
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar -->
  <div class="marquee-container">
    <marquee scrollamount="10" scrolldelay="50">
      +++ WELCOME TO THE CYBER ZONE +++ EST. 2026 +++ LAST UPDATED: TODAY +++ DON'T FORGET TO SIGN THE GUESTBOOK +++ CONSTRUCTING DIGITAL REALITIES +++
    </marquee>
  </div>

  <!-- Home -->
  <section class="p-5" id="home">
    <div class="container hero-container text-center d-flex flex-column justify-content-center align-items-center">
      <h1 class="hero-title" data-text="KURNIAWAN SENDHY">KURNIAWAN SENDHY</h1>
      <div class="hero-subtitle" data-text="[ ARCHITECTING DIGITAL EXPERIENCES ]">[ ARCHITECTING DIGITAL EXPERIENCES ]</div>
      
      <div class="mt-5 d-flex gap-3 justify-content-center flex-wrap">
        <a href="mailto:sendhy27@gmail.com">
          <button class="btn btn-outline-light">HIRE ME</button>
        </a>
        <a href="#contact">
          <button class="btn btn-outline-light">CONTACT ME</button>
        </a>
        <a href="#">
          <button class="btn btn-outline-light"><i class="bi bi-download"></i> RESUME</button>
        </a>
      </div>
    </div>
  </section>


  <!-- Home -->

  <!-- projects -->
  <section id="projects" class="p-5">
    <div class="container">
      <h2 class="nama text-center mb-5" style="font-size: 3rem;">PROJECTS_</h2>
      
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
                // Handle error
            }
        }

        if (!empty($projects)) {
            foreach ($projects as $project) {
                ?>
                <div class="y2k-project-card">
                  <div class="y2k-window-header">
                    <span><?php echo htmlspecialchars($project['file_name']); ?></span>
                    <div class="y2k-window-controls">
                      <div class="y2k-control-btn">_</div>
                      <div class="y2k-control-btn">X</div>
                    </div>
                  </div>
                  <div class="y2k-card-body">
                    <?php if (!empty($project['image'])): ?>
                    <div class="y2k-image-container">
                      <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="y2k-project-img">
                    </div>
                    <?php endif; ?>
                    <div class="y2k-project-info">
                      <h3 class="y2k-project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                      <p class="y2k-project-desc">
                        <?php echo htmlspecialchars($project['description']); ?>
                      </p>
                      <div class="y2k-tech-stack">
                        <?php 
                        $stack = explode(',', $project['tech_stack']);
                        foreach ($stack as $tech) {
                            echo '<span class="y2k-badge">' . htmlspecialchars(trim($tech)) . '</span>';
                        }
                        ?>
                      </div>
                      <a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" class="y2k-action-btn">VISIT SITE</a>
                    </div>
                  </div>
                </div>
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
  <section id="blog" class="p-5">
    <div class="container">
      <h2 class="nama text-center mb-5" style="font-size: 3rem;">SYSTEM_LOGS</h2>
      <div class="projects-grid">
        <?php
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM blogs ORDER BY date DESC");
            $blogs = $stmt->fetchAll();

            if ($blogs) {
                foreach ($blogs as $blog) {
                    ?>
                    <div class="y2k-project-card">
                        <div class="y2k-window-header">
                            <span>LOG_FILE_<?php echo str_replace('-', '', $blog['date']); ?>.TXT</span>
                            <div class="y2k-window-controls">
                                <div class="y2k-control-btn">_</div>
                                <div class="y2k-control-btn">X</div>
                            </div>
                        </div>
                        <div class="y2k-card-body">
                            <?php if (!empty($blog['image'])): ?>
                            <div class="y2k-image-container">
                                <img src="<?php echo $blog['image']; ?>" class="y2k-project-img" alt="Log Visual">
                            </div>
                            <?php endif; ?>
                            <div class="y2k-project-info">
                                <span class="y2k-badge mb-2" style="width: fit-content;"><?php echo $blog['date']; ?></span>
                                <h3 class="y2k-project-title"><?php echo $blog['title']; ?></h3>
                                <p class="y2k-project-desc">
                                    <?php echo substr($blog['content'], 0, 100) . '...'; ?>
                                </p>
                                <button class="y2k-action-btn w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#blogModal" 
                                    data-title="<?php echo htmlspecialchars($blog['title']); ?>"
                                    data-date="<?php echo $blog['date']; ?>" 
                                    data-image="<?php echo $blog['image']; ?>"
                                    data-content="<?php echo htmlspecialchars($blog['content']); ?>">
                                    [ ACCESS DATA ]
                                </button>
                            </div>
                        </div>
                    </div>
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

  <!-- Footer -->
  
  <!-- Blog View Modal -->
  <div class="modal fade" id="blogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content y2k-modal-content">
        <div class="modal-header y2k-modal-header">
          <h5 class="modal-title y2k-modal-title" id="modalTitle">LOG_VIEWER.EXE</h5>
          <button type="button" class="y2k-btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
        </div>
        <div class="modal-body y2k-modal-body">
          <img src="" id="modalImage" class="w-100" style="height: 300px; object-fit: cover; border-bottom: 2px solid var(--primary-color);">
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
    <p class="text-white text-center mb-0">DESIGNED BY <a href="http://instagram.com/kurniawansendhy">SENDHY
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
  <script src="script/main.js"></script>
  <script src="script/hero.js"></script>
  <script src="script/blog.js"></script>
  <script src="script/background.js"></script>


</body>

</html>