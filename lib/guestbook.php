<?php

const GUESTBOOK_COOLDOWN_SECONDS = 60;
const GUESTBOOK_MAX_PER_HOUR = 5;
const GUESTBOOK_RULES = [
    'name' => ['label' => 'Name', 'max' => 100],
    'email' => ['label' => 'Email', 'max' => 100],
    'message' => ['label' => 'Message', 'max' => 2000],
];

/**
 * @param array<string, string> $input raw name/email/message
 * @return array{data: array<string, string>, errors: array<string, string>}
 */
function validate_guestbook(array $input): array
{
    $data = [];
    foreach (array_keys(GUESTBOOK_RULES) as $field) {
        $data[$field] = trim($input[$field] ?? '');
    }
    $errors = validate_lengths($data, GUESTBOOK_RULES);
    if (!isset($errors['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Email address is not valid.';
    }
    return ['data' => $data, 'errors' => $errors];
}

function guestbook_cooldown_left(?int $lastSentAt, int $now): int
{
    if ($lastSentAt === null) {
        return 0;
    }
    return max(0, GUESTBOOK_COOLDOWN_SECONDS - ($now - $lastSentAt));
}

/**
 * Handle the contact form POST. Stores raw values; escaping happens on output.
 *
 * @return array{type: string, message: string, old: array<string, string>}
 */
function handle_guestbook_post(?PDO $pdo): array
{
    $input = [
        'name' => post_string('nama'),
        'email' => post_string('email'),
        'message' => post_string('pesan'),
    ];

    if (!csrf_valid(post_string('csrf_token'))) {
        return ['type' => 'error', 'message' => 'Session expired. Please send your message again.', 'old' => $input];
    }
    // Honeypot: humans never see this field, bots fill it. Pretend it worked.
    if (post_string('website') !== '') {
        return ['type' => 'success', 'message' => 'Signed. Thanks for your message!', 'old' => []];
    }
    $wait = guestbook_cooldown_left($_SESSION['guestbook_last_sent'] ?? null, time());
    if ($wait > 0) {
        return ['type' => 'error', 'message' => "Please wait $wait seconds before sending another message.", 'old' => $input];
    }
    // Session cooldown is easy to dodge by dropping cookies; this cap is per IP
    $wait = throttle_retry_after('guestbook', client_ip(), GUESTBOOK_MAX_PER_HOUR, 3600);
    if ($wait > 0) {
        return ['type' => 'error', 'message' => 'Too many messages from your network. Try again in ' . minutes_text($wait) . '.', 'old' => $input];
    }

    $result = validate_guestbook($input);
    if ($result['errors']) {
        return ['type' => 'error', 'message' => implode(' ', $result['errors']), 'old' => $input];
    }
    if ($pdo === null) {
        return ['type' => 'error', 'message' => 'Your message could not be saved because the database is offline. Please email me instead.', 'old' => $input];
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO guestbook (name, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$result['data']['name'], $result['data']['email'], $result['data']['message']]);
    } catch (PDOException $e) {
        error_log('Guestbook insert failed: ' . $e->getMessage());
        return ['type' => 'error', 'message' => 'Your message could not be saved. Please try again in a few minutes.', 'old' => $input];
    }

    $_SESSION['guestbook_last_sent'] = time();
    throttle_record('guestbook', client_ip(), 3600);
    return ['type' => 'success', 'message' => 'Signed. Thanks for your message!', 'old' => []];
}
