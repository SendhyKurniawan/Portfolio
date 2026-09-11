<?php
require_once __DIR__ . '/lib/app.php';
http_response_code(404);
// Apache hands the original path over; it is the visitor's text, so it only ever leaves here escaped
$wanted = (string) ($_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?? '/');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>404 — page not found | KURSE CO.</title>
  <meta name="robots" content="noindex" />
  <meta name="theme-color" content="#0000aa" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💿</text></svg>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=VT323&display=swap" />
  <link rel="stylesheet" href="<?= e(asset_url('css/bsod.css', '/')) ?>">
</head>

<body>
  <main class="bsod">
    <h1 class="bsod-chip">KURSE CO.</h1>

    <p>A fatal exception <b>0E</b> has occurred at 0028:C0011E36. The page you asked for is not
      installed on this system:</p>

    <p class="bsod-path"><?= e($wanted) ?></p>

    <ul class="bsod-choices">
      <li>Press <b>Home</b> to go back to the front page.</li>
      <li>Press <b>Guestbook</b> to tell me what you were looking for.</li>
      <li>Press <b>CTRL+ALT+DEL</b> to restart your computer. You will lose any unsaved information
        in all applications.</li>
    </ul>

    <p class="bsod-actions">
      <a class="bsod-btn" href="/index.php">Home</a>
      <a class="bsod-btn" href="/index.php#contact">Guestbook</a>
    </p>

    <p class="bsod-prompt">Press any key to continue <span class="bsod-caret" aria-hidden="true">_</span></p>
  </main>

  <script src="<?= e(asset_url('script/bsod.js', '/')) ?>" defer></script>
</body>

</html>
