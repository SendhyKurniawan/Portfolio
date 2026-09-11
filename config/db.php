<?php
// Connection settings come from the environment (see .env.example)
$host = getenv('DB_HOST') ?: 'db'; // Service name in docker-compose
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Pages check for a null $pdo and render without DB content
$pdo = null;

if ($db === false || $user === false || $pass === false) {
    error_log('Database disabled: DB_NAME, DB_USER and DB_PASSWORD must be set');
} else {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
    }
}
?>
