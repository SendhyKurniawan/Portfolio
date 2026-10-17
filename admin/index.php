<?php
// /admin/ has no page of its own: without this the directory listing is denied
// and the short address dead-ends on a 403. Send visitors where they were going —
// the dashboard if they are already signed in, the login form if not, matching
// what login.php does when an admin lands on it with a live session.
require_once '../lib/app.php';
start_session();

header('Location: ' . (is_admin() ? 'dashboard.php' : 'login.php'));
exit;
