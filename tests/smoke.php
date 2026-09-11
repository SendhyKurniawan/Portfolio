<?php
/**
 * HTTP smoke test against the running docker compose stack.
 *
 *   SMOKE_ADMIN_PASSWORD=... php tests/smoke.php [base-url]
 *
 * Reads DB and admin settings from .env, talks to MySQL on 127.0.0.1:3388 to
 * reset the guestbook cooldown and verify rows, and cleans up what it creates.
 */

$base = rtrim($argv[1] ?? 'http://localhost:8095', '/');
$env = parse_ini_file(__DIR__ . '/../.env', false, INI_SCANNER_RAW) ?: [];
$adminUser = $env['ADMIN_USERNAME'] ?? 'admin';
$adminPass = getenv('SMOKE_ADMIN_PASSWORD') ?: '';
if ($adminPass === '') {
    fwrite(STDERR, "Set SMOKE_ADMIN_PASSWORD to the admin password.\n");
    exit(2);
}
$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3388;dbname=' . $env['MYSQL_DATABASE'] . ';charset=utf8mb4',
    $env['MYSQL_USER'],
    $env['MYSQL_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Login/guestbook throttles are per IP; start from a clean slate so reruns don't lock us out
function reset_throttles(): void
{
    exec('docker compose exec -T portfolio rm -rf /tmp/portfolio-throttle 2>&1', $out, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Could not reset throttle state: " . implode("\n", $out) . "\n");
        exit(2);
    }
}
reset_throttles();

$failures = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    global $failures;
    echo ($ok ? "  ok   " : "  FAIL ") . $name . ($ok || $detail === '' ? '' : "  [$detail]") . "\n";
    if (!$ok) {
        $failures++;
    }
}

/** @return array{status: int, body: string, headers: string, location: string} */
function http(string $method, string $url, array $fields = [], ?string $jar = null, bool $multipart = false, string $userAgent = ''): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        // No user agent by default, so smoke requests count as bots and leave the visitor counter alone
        CURLOPT_USERAGENT => $userAgent,
    ]);
    if ($jar !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart ? $fields : http_build_query($fields));
    }
    $raw = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $result = [
        'status' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
        'headers' => substr($raw, 0, $headerSize),
        'body' => substr($raw, $headerSize),
        'location' => (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL),
    ];
    curl_close($ch);
    return $result;
}

function csrf_from(string $html): string
{
    return preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $html, $m) ? $m[1] : '';
}

function new_jar(): string
{
    return tempnam(sys_get_temp_dir(), 'smoke');
}

echo "Public pages\n";
foreach (['/', '/index.php', '/admin/login.php'] as $path) {
    check("GET $path is 200", http('GET', $base . $path)['status'] === 200);
}
$home = http('GET', $base . '/');
check('security headers present', stripos($home['headers'], 'X-Content-Type-Options: nosniff') !== false
    && stripos($home['headers'], 'X-Frame-Options: DENY') !== false);
check('no PHP version leak', stripos($home['headers'], 'X-Powered-By: PHP') === false);

echo "Blocked paths\n";
$blocked = ['/.env', '/.env.example', '/.git/HEAD', '/config/db.php', '/lib/app.php', '/database/init.sql',
    '/docker-compose.yml', '/Dockerfile', '/README.md', '/docker/apache/portfolio.conf', '/tests/smoke.php',
    '/composer.json', '/vendor/autoload.php', '/uploads/', '/img/',
    // Bind mounts from Windows/macOS are case-insensitive; rules must be too
    '/Config/db.php', '/LIB/app.php', '/VENDOR/autoload.php', '/README.MD', '/DOCKERFILE', '/docker-compose.YML'];
foreach ($blocked as $path) {
    $status = http('GET', $base . $path)['status'];
    check("GET $path is blocked", in_array($status, [403, 404], true), "got $status");
}
foreach (['/admin.php', '/blog.php'] as $path) {
    check("legacy $path removed", http('GET', $base . $path)['status'] === 404);
}

echo "Uploads never execute PHP\n";
$probe = __DIR__ . '/../uploads/smoke-probe.php';
@mkdir(dirname($probe), 0755, true);
file_put_contents($probe, '<?php echo "EXECUTED";');
foreach (['/uploads/smoke-probe.php', '/Uploads/smoke-probe.php', '/UPLOADS/SMOKE-PROBE.PHP'] as $path) {
    $res = http('GET', $base . $path);
    check("$path is not executed", $res['status'] === 403 && strpos($res['body'], 'EXECUTED') === false, "got {$res['status']}");
}
unlink($probe);

echo "Visitor counter\n";
const BROWSER_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36';
function visitor_total(PDO $pdo): int
{
    try {
        return (int) $pdo->query("SELECT value FROM site_counters WHERE name = 'visitors'")->fetchColumn();
    } catch (PDOException $e) {
        return 0; // table not created yet
    }
}
$visitorsBefore = visitor_total($pdo);
$visitor = new_jar();
$page = http('GET', $base . '/', [], $visitor, false, BROWSER_UA)['body'];
check('first page view counts a visitor', visitor_total($pdo) === $visitorsBefore + 1);
check('footer shows the new total', strpos($page, str_pad((string) ($visitorsBefore + 1), 6, '0', STR_PAD_LEFT) . '</span>') !== false);
http('GET', $base . '/', [], $visitor, false, BROWSER_UA);
check('reload in the same session does not count again', visitor_total($pdo) === $visitorsBefore + 1);
http('GET', $base . '/', [], null, false, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
http('GET', $base . '/');
check('bots and requests without a user agent are not counted', visitor_total($pdo) === $visitorsBefore + 1);
// Put the real count back so smoke runs don't inflate it
$pdo->prepare("UPDATE site_counters SET value = ? WHERE name = 'visitors'")->execute([$visitorsBefore]);

echo "Guestbook\n";
$pdo->exec("DELETE FROM guestbook WHERE email LIKE '%@smoke.test'");
$jar = new_jar();
$token = csrf_from(http('GET', $base . '/', [], $jar)['body']);
check('contact form has CSRF token', $token !== '');

$res = http('POST', $base . '/', ['nama' => 'x', 'email' => 'a@smoke.test', 'pesan' => 'no token'], $jar);
check('POST without token redirects (303)', $res['status'] === 303);
check('...and shows expired-session error', strpos(http('GET', $base . '/', [], $jar)['body'], 'Session expired') !== false);

$token = csrf_from(http('GET', $base . '/', [], $jar)['body']);
http('POST', $base . '/', ['csrf_token' => $token, 'nama' => 'Bot', 'email' => 'bot@smoke.test', 'pesan' => 'spam', 'website' => 'http://spam'], $jar);
check('honeypot submission is not stored', (int) $pdo->query("SELECT COUNT(*) FROM guestbook WHERE email='bot@smoke.test'")->fetchColumn() === 0);

http('POST', $base . '/', ['csrf_token' => $token, 'nama' => 'A', 'email' => 'not-an-email', 'pesan' => 'hi'], $jar);
$page = http('GET', $base . '/', [], $jar)['body'];
check('invalid email rejected', strpos($page, 'Email address is not valid.') !== false);
check('old input is kept after error', strpos($page, 'value="not-an-email"') !== false);

$xssName = '<script>alert(1)</script>Tester';
http('POST', $base . '/', ['csrf_token' => $token, 'nama' => $xssName, 'email' => 'guest@smoke.test', 'pesan' => "Hello & <b>bye</b>"], $jar);
check('valid message shows success', strpos(http('GET', $base . '/', [], $jar)['body'], 'Sent! Thanks for signing my guestbook.') !== false);
$row = $pdo->query("SELECT name, message FROM guestbook WHERE email='guest@smoke.test'")->fetch(PDO::FETCH_ASSOC);
check('message stored raw (no double-encoding)', $row && $row['name'] === $xssName && $row['message'] === 'Hello & <b>bye</b>');

http('POST', $base . '/', ['csrf_token' => $token, 'nama' => 'Again', 'email' => 'again@smoke.test', 'pesan' => 'second'], $jar);
check('second message within a minute is rate limited', strpos(http('GET', $base . '/', [], $jar)['body'], 'Please wait') !== false);

echo "Admin auth\n";
$admin = new_jar();
check('dashboard requires login', http('GET', $base . '/admin/dashboard.php', [], $admin)['status'] === 302);
$token = csrf_from(http('GET', $base . '/admin/login.php', [], $admin)['body']);
$res = http('POST', $base . '/admin/login.php', ['username' => $adminUser, 'password' => $adminPass], $admin);
check('login without CSRF token refused', $res['status'] === 200 && strpos($res['body'], 'Session expired') !== false);
$res = http('POST', $base . '/admin/login.php', ['csrf_token' => $token, 'username' => $adminUser, 'password' => 'admin123'], $admin);
check('old admin123 password refused', strpos($res['body'], 'Invalid credentials') !== false);
$res = http('POST', $base . '/admin/login.php', ['csrf_token' => $token, 'username' => $adminUser, 'password' => $adminPass], $admin);
check('correct login redirects to dashboard', $res['status'] === 302 && str_ends_with($res['location'], 'dashboard.php'));

$dash = http('GET', $base . '/admin/dashboard.php', [], $admin)['body'];
check('inbox lists guestbook message escaped', strpos($dash, '&lt;script&gt;alert(1)&lt;/script&gt;Tester') !== false
    && strpos($dash, '<script>alert(1)</script>') === false);
$token = csrf_from($dash);

echo "Admin writes\n";
$guestId = (int) $pdo->query("SELECT id FROM guestbook WHERE email='guest@smoke.test'")->fetchColumn();
check('delete via GET is refused (405)', http('GET', $base . "/admin/delete.php?type=guestbook&id=$guestId", [], $admin)['status'] === 405);
check('delete without CSRF is refused (400)', http('POST', $base . '/admin/delete.php', ['type' => 'guestbook', 'id' => $guestId], $admin)['status'] === 400);

$fakeImage = tempnam(sys_get_temp_dir(), 'smk');
file_put_contents($fakeImage, '<?php system($_GET["c"]); ?>');
$res = http('POST', $base . '/admin/blog_form.php', [
    'csrf_token' => $token, 'title' => 'Smoke upload', 'date' => '2026-01-01', 'content' => 'x',
    'image_file' => new CURLFile($fakeImage, 'image/jpeg', 'shell.php.jpg'),
], $admin, true);
check('PHP disguised as image is rejected', strpos($res['body'], 'Only JPG, PNG, GIF or WebP images are allowed.') !== false);
unlink($fakeImage);

$png = tempnam(sys_get_temp_dir(), 'smk');
$img = imagecreatetruecolor(4, 4);
imagepng($img, $png);
$xssTitle = '<img src=x onerror=alert(1)>Smoke';
$res = http('POST', $base . '/admin/blog_form.php', [
    'csrf_token' => $token, 'title' => $xssTitle, 'date' => date('Y-m-d'), 'content' => 'Smoke body',
    'image_file' => new CURLFile($png, 'image/png', 'my photo.png'),
], $admin, true);
unlink($png);
check('blog with real PNG saves', $res['status'] === 302);
$blog = $pdo->query("SELECT id, image FROM blogs WHERE content='Smoke body'")->fetch(PDO::FETCH_ASSOC);
check('upload renamed to random .png', $blog && preg_match('#^uploads/[a-f0-9]{32}\.png$#', $blog['image']) === 1, $blog['image'] ?? 'no row');
if ($blog) {
    check('uploaded image is served', http('GET', $base . '/' . $blog['image'])['status'] === 200);
    $home = http('GET', $base . '/')['body'];
    check('blog title escaped on home page', strpos($home, '&lt;img src=x onerror=alert(1)&gt;Smoke') !== false
        && strpos($home, '<img src=x onerror') === false);
    check('post dated today gets a NEW! sticker', strpos($home, 'onerror=alert(1)&gt;Smoke<span class="tag-new">NEW!</span>') !== false);

    $res = http('POST', $base . '/admin/delete.php', ['csrf_token' => $token, 'type' => 'blog', 'id' => $blog['id']], $admin);
    check('blog delete works', $res['status'] === 302 && !$pdo->query("SELECT 1 FROM blogs WHERE id={$blog['id']}")->fetchColumn());
    check('uploaded file removed with its post', http('GET', $base . '/' . $blog['image'])['status'] === 404);
}

$res = http('POST', $base . '/admin/project_form.php', [
    'csrf_token' => $token, 'title' => 'Smoke', 'file_name' => 'S.EXE', 'description' => 'd',
    'tech_stack' => 'PHP', 'link' => 'javascript:alert(1)',
], $admin, true);
check('javascript: project link rejected', strpos($res['body'], 'Project link must start with http') !== false);

http('POST', $base . '/admin/delete.php', ['csrf_token' => $token, 'type' => 'guestbook', 'id' => $guestId], $admin);
check('guestbook delete works', !$pdo->query("SELECT 1 FROM guestbook WHERE id=$guestId")->fetchColumn());

echo "Logout\n";
http('GET', $base . '/admin/logout.php', [], $admin);
check('GET logout does not end the session', http('GET', $base . '/admin/dashboard.php', [], $admin)['status'] === 200);
check('logout without CSRF is refused (400)', http('POST', $base . '/admin/logout.php', [], $admin)['status'] === 400);
http('POST', $base . '/admin/logout.php', ['csrf_token' => $token], $admin);
check('POST logout ends the session', http('GET', $base . '/admin/dashboard.php', [], $admin)['status'] === 302);

echo "Login lockout\n";
$attacker = new_jar();
$token = csrf_from(http('GET', $base . '/admin/login.php', [], $attacker)['body']);
for ($i = 0; $i < 5; $i++) {
    http('POST', $base . '/admin/login.php', ['csrf_token' => $token, 'username' => $adminUser, 'password' => "wrong-$i"], $attacker);
}
$fresh = new_jar();
$token = csrf_from(http('GET', $base . '/admin/login.php', [], $fresh)['body']);
$res = http('POST', $base . '/admin/login.php', ['csrf_token' => $token, 'username' => $adminUser, 'password' => $adminPass], $fresh);
check('6th attempt is locked out even with new cookies and the right password',
    $res['status'] === 200 && strpos($res['body'], 'Too many failed attempts') !== false);
reset_throttles();

$pdo->exec("DELETE FROM guestbook WHERE email LIKE '%@smoke.test'");
foreach ([$visitor, $jar, $admin, $attacker, $fresh] as $cookieJar) {
    @unlink($cookieJar);
}

echo $failures === 0 ? "\nALL SMOKE CHECKS PASSED\n" : "\n$failures SMOKE CHECK(S) FAILED\n";
exit($failures === 0 ? 0 : 1);
