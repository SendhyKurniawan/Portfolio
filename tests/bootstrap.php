<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/app.php';

// Throttle state for tests lives in its own temp dir, never the real one
putenv('THROTTLE_DIR=' . sys_get_temp_dir() . '/portfolio-throttle-test-' . getmypid());

// Keep expected error_log() calls out of the test output
ini_set('error_log', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null');
