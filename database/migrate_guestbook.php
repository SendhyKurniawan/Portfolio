<?php
// Adds the approval flag to guestbook tables created before the public wall existed.
// Existing messages stay hidden (default 0) until the admin approves them.
if ($pdo) {
    try {
        $pdo->exec('ALTER TABLE guestbook ADD COLUMN approved TINYINT(1) NOT NULL DEFAULT 0');
    } catch (PDOException $e) {
        error_log('Guestbook approval migration failed: ' . $e->getMessage());
    }
}
