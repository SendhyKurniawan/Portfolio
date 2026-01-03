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
  <link rel="stylesheet" href="style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>" />
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
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Navbar -->
  <div style="background: black; border-bottom: 2px solid #fff; color: lime; font-family: var(--main-font); overflow: hidden; white-space: nowrap; padding: 5px;">
    <marquee scrollamount="10" scrolldelay="50">
      +++ WELCOME TO THE CYBER ZONE +++ EST. 2026 +++ LAST UPDATED: TODAY +++ DON'T FORGET TO SIGN THE GUESTBOOK +++ CONSTRUCTING DIGITAL REALITIES +++
    </marquee>
  </div>

  <!-- Home -->
  <section class="p-5" id="home">
    <div class="container hero-container text-center d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
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

  <script>
    // Subtitle Glitch Logic
    const heroSubtitle = document.querySelector('.hero-subtitle');
    const subtitleTexts = [
      "[ ARCHITECTING DIGITAL EXPERIENCES ]", 
      "[ CREATIVE DEVELOPER ]", 
      "[ SYSTEM ARCHITECT ]", 
      "[ FULL STACK ENGINEER ]"
    ];
    let subtitleIndex = 0;

    setInterval(() => {
      heroSubtitle.style.animation = 'none';
      void heroSubtitle.offsetWidth; 
      
      subtitleIndex = (subtitleIndex + 1) % subtitleTexts.length;
      const newSubtitle = subtitleTexts[subtitleIndex];
      
      heroSubtitle.setAttribute('data-text', newSubtitle);
      heroSubtitle.textContent = newSubtitle;
      
      heroSubtitle.style.animation = 'glitch-anim-1 0.5s infinite';
      setTimeout(() => {
         heroSubtitle.style.animation = 'glitch-anim-1 3s infinite linear alternate-reverse';
      }, 500);
    }, 4000);

    // Title Swapping Logic (KURNIAWAN SENDHY <-> KURSE.CO)
    const heroTitle = document.querySelector('.hero-title');
    const titleTexts = ["KURNIAWAN SENDHY", "KURSE.CO"];
    let titleIndex = 0;

    setInterval(() => {
      heroTitle.style.animation = 'none';
      void heroTitle.offsetWidth; 

      titleIndex = (titleIndex + 1) % titleTexts.length;
      const newTitle = titleTexts[titleIndex];

      heroTitle.setAttribute('data-text', newTitle);
      heroTitle.textContent = newTitle;

      // Sync glitch with text change
      heroTitle.style.animation = 'glitch-anim-1 0.5s infinite';
      setTimeout(() => {
         heroTitle.style.animation = 'glitch-anim-1 3s infinite linear alternate-reverse';
      }, 500);
    }, 5000); // Swap every 5 seconds
  </script>

  <!-- Skills -->
  <section id="skills" class="p-5">
    <div class="container">
      <h2 class="nama text-center mb-5" style="font-size: 3rem;">SYSTEM MODULES</h2>
      <div class="d-flex justify-content-center flex-wrap gap-4">
        <div class="skill-badge">HTML5</div>
        <div class="skill-badge">CSS3</div>
        <div class="skill-badge">JAVASCRIPT</div>
        <div class="skill-badge">BOOTSTRAP 5</div>
        <div class="skill-badge">NODE.JS</div>
        <div class="skill-badge">REACT</div>
        <div class="skill-badge">GIT</div>
        <div class="skill-badge">UI/UX</div>
      </div>
    </div>
  </section>
  <!-- Skills -->
  <!-- Home -->

  <!-- projects -->
  <section id="projects" class="p-5">
    <div class="content container row align-items-center">
      <div class="col-lg-6">
        <div class="projdiv my-5">
          <div class="proj projhover" id="cuffour">CUFFOUR VOL2</div>
          <div class="proj" id="lynx">LYNX STREZZO NFT</div>
          <div class="proj" id="lapizzeria">LA PIZZERIA</div>
          <div class="proj" id="mrs">MRS FURNITURE</div>
          <div class="proj" id="imedia">IMEDIA 2023</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="expdiv text-start my-5" id="expdiv">
          <div class="cuffour exp" id="cuffourexp">
            <h3 class="text-white mb-3">CUFFOUR VOL2</h3>
            <p>
              CUFFOUR (Culture Festival of Fifty Four) is a foreign language extracurricular program at SMAN 54 Jakarta.
              <br /><br />
              A dynamic platform showcasing diverse cultures including Japanese, Korean, and German.
              <br /><br />
              <span class="text-info">Stack: HTML, CSS, JS, Bootstrap 5</span>
            </p>
            <img src="https://placehold.co/600x300/1a1a2e/00f3ff?text=CUFFOUR+VOL2+PREVIEW" alt="Cuffour Preview"
              class="project-preview-img mb-3">
            <br>
            <a href="https://cuffourvol2.github.io/cuffourvol2/index.html" target="_blank">
              <button class="btn btn-outline-light mt-3">VISIT SITE</button>
            </a>
          </div>
          <div class="lynx exp d-none" id="lynxexp">
            <h3 class="text-white mb-3">LYNX STREZZO NFT</h3>
            <p>
              A digital oasis of boundless creativity. A veritable gallery of the future where infinite possibilities
              converge.
              <br /><br />
              <span class="text-info">Stack: HTML, CSS, JS, AOS, Bootstrap 5</span>
            </p>
            <img src="https://placehold.co/600x300/1a1a2e/bd00ff?text=LYNX+NFT+PREVIEW" alt="Lynx Preview"
              class="project-preview-img mb-3">
            <br>
            <a href="https://sendhykurniawan.github.io/Lynx-Strezzo-NFT-V2/" target="_blank">
              <button class="btn btn-outline-light mt-3">VISIT SITE</button>
            </a>
          </div>
          <div class="lapizzeria exp d-none" id="lapizzeriaexp">
            <h3 class="text-white mb-3">LA PIZZERIA</h3>
            <p>
              An educational project replicating and reinventing a web presence, honing skills in web development and
              UX.
              <br /><br />
              <span class="text-info">Stack: HTML, CSS, JS, Bootstrap 5</span>
            </p>
            <img src="https://placehold.co/600x300/1a1a2e/00f3ff?text=LA+PIZZERIA+PREVIEW" alt="Pizzeria Preview"
              class="project-preview-img mb-3">
            <br>
            <a href="https://sendhykurniawan.github.io/lapizzeria/" target="_blank">
              <button class="btn btn-outline-light mt-3">VISIT SITE</button>
            </a>
          </div>
          <div class="mrs exp d-none" id="mrsexp">
            <h3 class="text-white mb-3">MRS FURNITURE</h3>
            <p>
              Crafting an innovative digital platform for a visionary business concept, redefining the furniture retail
              experience.
              <br /><br />
              <span class="text-info">Stack: HTML, CSS, JS, AOS, Bootstrap 5</span>
            </p>
            <img src="https://placehold.co/600x300/1a1a2e/bd00ff?text=MRS+FURNITURE+PREVIEW" alt="MRS Furniture Preview"
              class="project-preview-img mb-3">
            <br>
            <a href="https://sendhykurniawan.github.io/mrsfurniture/" target="_blank">
              <button class="btn btn-outline-light mt-3">VISIT SITE</button>
            </a>
          </div>
          <div class="imedia exp d-none" id="imediaexp">
            <h3 class="text-white mb-3">IMEDIA 2023</h3>
            <p>
              A comprehensive orientation hub for incoming college students. Resources, guidance, and insights for a
              seamless transition.
              <br /><br />
              <span class="text-info">Stack: HTML, CSS, JS, AOS, Bootstrap 5</span>
            </p>
            <img src="https://placehold.co/600x300/1a1a2e/00f3ff?text=IMEDIA+2023+PREVIEW" alt="Imedia Preview"
              class="project-preview-img mb-3">
            <br>
            <a href="https://sendhykurniawan.github.io/iMEDIA2023/" target="_blank">
              <button class="btn btn-outline-light mt-3">VISIT SITE</button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- projects -->
  
  <!-- System Logs (Blog) -->
  <section id="blog" class="p-5">
    <div class="container">
      <h2 class="nama text-center mb-5" style="font-size: 3rem;">SYSTEM LOGS</h2>
      <div class="row g-4">
        <?php
        $file = 'data/blogs.json';
        if (file_exists($file)) {
            $blogs = json_decode(file_get_contents($file), true);
            if ($blogs) {
                foreach ($blogs as $blog) {
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="expdiv h-100 d-flex flex-column">
                            <img src="<?php echo $blog['image']; ?>" class="project-preview-img mb-3" alt="Log Visual" style="height: 200px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <small class="text-info" style="font-family: var(--main-font);"><i class="bi bi-calendar-event"></i> <?php echo $blog['date']; ?></small>
                                <h4 class="mt-2 mb-3 text-white" style="font-family: var(--header-font); font-size: 1.2rem; line-height: 1.5;">
                                    <?php echo $blog['title']; ?>
                                </h4>
                                <p class="exp"><?php echo substr($blog['content'], 0, 100) . '...'; ?></p>
                            </div>
                            <button class="btn btn-outline-light w-100 mt-3" 
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
                    <?php
                }
            } else {
                echo "<div class='console-text text-center text-muted'>> NO LOGS FOUND IN DATABASE_</div>";
            }
        } else {
            echo "<div class='console-text text-center text-danger'>> CRITICAL ERROR: DATABASE DISCONNECTED_</div>";
        }
        ?>
      </div>
    </div>
  </section>
  <!-- System Logs -->

  <!-- contact -->
  <section class="contact p-5" id="contact">
    <div class="container">
      <h2 class="nama text-center mb-5" style="font-size: 3rem;">ESTABLISH CONNECTION</h2>
      <div class="y2k-window">
        <div class="y2k-window-header">
          <div class="y2k-window-title">MESSAGE_UPLINK.EXE</div>
          <div class="window-controls">
            <div class="win-btn">_</div>
            <div class="win-btn">□</div>
            <div class="win-btn">X</div>
          </div>
        </div>
        <div class="terminal-body row p-0 m-0">
          <div class="col-lg-5 border-end border-secondary p-4 d-flex flex-column justify-content-between" style="background: rgba(0,0,0,0.3);">
            <div>
              <p class="text-info mb-4" style="font-family: var(--header-font);">> INITIALIZING PROTOCOLS...</p>
              <ul class="list-unstyled">
                <li class="mb-4">
                  <a href="https://www.instagram.com/kurniawansendhy/" target="_blank"
                    class="text-decoration-none text-light hover-effect d-flex align-items-center gap-3">
                    <i class="bi bi-instagram text-primary fs-4"></i> 
                    <span style="border-bottom: 1px dotted #fff;">INSTAGRAM_FEED</span>
                  </a>
                </li>
                <li class="mb-4">
                  <a href="mailto:sendhy27@gmail.com" class="text-decoration-none text-light hover-effect d-flex align-items-center gap-3">
                    <i class="bi bi-envelope text-primary fs-4"></i>
                    <span style="border-bottom: 1px dotted #fff;">SEND_EMAIL</span>
                  </a>
                </li>
                <li class="mb-4">
                  <a href="https://github.com/SendhyKurniawan" target="_blank"
                    class="text-decoration-none text-light hover-effect d-flex align-items-center gap-3">
                    <i class="bi bi-github text-primary fs-4"></i>
                     <span style="border-bottom: 1px dotted #fff;">GITHUB_REPO</span>
                  </a>
                </li>
              </ul>
            </div>
            <div class="mt-4 p-2 border border-info text-center">
               <small class="text-muted">SECURE CONNECTION ESTABLISHED</small>
            </div>
          </div>
          <div class="col-lg-7 p-4">
            <form name="contact-form" id="form">
              <div class="mb-3">
                <label for="name" class="form-label text-white">> USER_IDENTITY</label>
                <input type="text" class="form-control y2k-input" id="name" name="nama" placeholder="[ ENTER NAME ]">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label text-white">> RETURN_ADDRESS</label>
                <input type="email" class="form-control y2k-input" id="email" name="email" placeholder="[ ENTER EMAIL ]">
              </div>
              <div class="mb-3">
                <label for="pesan" class="form-label text-white">> DATA_PAYLOAD</label>
                <textarea class="form-control y2k-input" id="pesan:" rows="4" name="pesan"
                  placeholder="[ ENTER MESSAGE ]"></textarea>
              </div>
              <button type="submit" class="y2k-submit-btn w-100 mt-2" id="btnKirim">TRANSMIT DATA</button>
              <button class="y2k-submit-btn w-100 mt-2 d-none" type="button" disabled
                id="btnLoading">
                TRANSMITTING...
              </button>
            </form>
            <div class="alert alert-success alert-dismissible fade show d-none my-alert mt-3" role="alert" 
                 style="background: black; border: 2px solid lime; color: lime; font-family: var(--main-font);">
              <strong>> SUCCESS:</strong> DATA UPLOADED TO SERVER.
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- contact -->

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
            <p class="text-light" id="modalContent" style="white-space: pre-wrap; line-height: 1.6;">Log content...</p>
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
    <div class="text-center mt-3" style="font-family: var(--main-font); font-size: 0.8rem; color: #fff;">
       [ 800x600 ] [ IE 5.0 ] [ NOTEPAD ] <br>
       <span style="color: red; font-weight: bold;">[ VISITOR COUNT: 031337 ]</span>
    </div>
  </footer>
  <!-- Footer -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <!-- My JS -->
  <script src="script/scirpt.js"></script>
  <script src="script/background.js"></script>

  <script>
    // Blog Modal Listener
    const blogModal = document.getElementById('blogModal');
    if (blogModal) {
      blogModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const title = button.getAttribute('data-title');
        const date = button.getAttribute('data-date');
        const image = button.getAttribute('data-image');
        const content = button.getAttribute('data-content');

        blogModal.querySelector('#modalTitle').textContent = title;
        blogModal.querySelector('#modalDate').textContent = '> TIMESTAMP: ' + date;
        blogModal.querySelector('#modalImage').src = image;
        blogModal.querySelector('#modalContent').textContent = content;
      });
    }
  </script>
</body>

</html>