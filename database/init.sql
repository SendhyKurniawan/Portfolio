CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    message TEXT NOT NULL,
    -- Messages show on the public wall only after the admin approves them
    approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Footer hit counter (lib/visitors.php also creates it on databases made before it existed)
CREATE TABLE IF NOT EXISTS site_counters (
    name VARCHAR(50) NOT NULL PRIMARY KEY,
    value BIGINT NOT NULL DEFAULT 0
);

-- Seed Blogs
INSERT INTO blogs (title, content, image, date) VALUES
('Rebuilding as a 1999 desktop', 'KURSE CO. is now the Millennium Edition: a portfolio dressed as a turn-of-the-century desktop, with frosted plastic windows, chrome type, an LCD hit counter and a guestbook that works like an old instant messenger.\n\nUnder the costume it''s hand-written PHP 8.2 and MySQL, no framework, running in Docker. Pages are rendered on the server, so the content arrives before any script runs, and the layout holds down to 390px wide. Animations switch off if your system asks for reduced motion.\n\nThe guestbook took the most care. Messages stay private until I approve them, email addresses never reach the page, and the form is guarded by CSRF tokens, a honeypot and per-network rate limits. A PHPUnit suite and an HTTP smoke test check all of it, including that an unapproved message never shows up in public.\n\nA few things are hidden for you to find: five iMac colors in the top bar, a screensaver after a minute idle, a blue screen on missing pages, and a Konami code that sends the whole site back to 1999.\n\nGot a project in mind, or a role I''d fit? Hit "Hire me" at the top, or sign the guestbook.', NULL, '2026-09-12');
