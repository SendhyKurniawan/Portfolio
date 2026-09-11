<?php
// Shared helpers for every page. Pages that need the DB also require config/db.php.
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/throttle.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/guestbook.php';
require_once __DIR__ . '/assets.php';
require_once __DIR__ . '/visitors.php';
require_once __DIR__ . '/badges.php';
require_once __DIR__ . '/skills.php';
require_once __DIR__ . '/gallery.php';

const UPLOAD_DIR = __DIR__ . '/../uploads';
