<?php
// Footer hit counter: one count per browser session; bots and the admin don't count

const VISITOR_COUNTER = 'visitors';
const VISITOR_DIGITS = 6;
const VISITOR_SESSION_KEY = 'visitor_counted';
// Crawlers, link previews, uptime monitors, scripts and requests with no user agent at all
const VISITOR_BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|headless|lighthouse|curl|wget|python|java\/|go-http|okhttp|^$/i';

function is_probable_bot(string $userAgent): bool
{
    return preg_match(VISITOR_BOT_PATTERN, trim($userAgent)) === 1;
}

function visitor_should_count(bool $alreadyCounted, string $userAgent, bool $isAdmin): bool
{
    return !$alreadyCounted && !$isAdmin && !is_probable_bot($userAgent);
}

/** Odometer-style digits for the LCD; dashes when the count can't be read. */
function visitor_counter_digits(?int $count, int $width = VISITOR_DIGITS): string
{
    if ($count === null) {
        return str_repeat('-', $width);
    }
    return str_pad((string) $count, $width, '0', STR_PAD_LEFT);
}

function counter_ensure_table(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS site_counters (
        name VARCHAR(50) NOT NULL PRIMARY KEY,
        value BIGINT NOT NULL DEFAULT 0
    )');
}

function counter_read(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare('SELECT value FROM site_counters WHERE name = ?');
    $stmt->execute([$name]);
    return (int) $stmt->fetchColumn();
}

/** Adds one and returns the new total. Plain UPDATE/INSERT so it runs on MySQL and SQLite alike. */
function counter_increment(PDO $pdo, string $name): int
{
    try {
        return counter_bump($pdo, $name);
    } catch (PDOException $e) {
        // Databases created before the counter existed don't have the table yet; a lost
        // race on the very first INSERT lands here too, and the retry's UPDATE then wins
        counter_ensure_table($pdo);
        return counter_bump($pdo, $name);
    }
}

function counter_bump(PDO $pdo, string $name): int
{
    $update = $pdo->prepare('UPDATE site_counters SET value = value + 1 WHERE name = ?');
    $update->execute([$name]);
    if ($update->rowCount() === 0) {
        $pdo->prepare('INSERT INTO site_counters (name, value) VALUES (?, 1)')->execute([$name]);
    }
    return counter_read($pdo, $name);
}

/**
 * Counts this visit if it's the first page view of a human, non-admin session,
 * and returns the total to display. Null means the counter is unavailable.
 */
function track_visitor(?PDO $pdo): ?int
{
    if (!$pdo) {
        return null;
    }
    $alreadyCounted = ($_SESSION[VISITOR_SESSION_KEY] ?? false) === true;
    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    try {
        if (!visitor_should_count($alreadyCounted, $userAgent, is_admin())) {
            return counter_read($pdo, VISITOR_COUNTER);
        }
        $total = counter_increment($pdo, VISITOR_COUNTER);
        $_SESSION[VISITOR_SESSION_KEY] = true;
        return $total;
    } catch (PDOException $e) {
        error_log('Visitor counter failed: ' . $e->getMessage());
        return null;
    }
}
