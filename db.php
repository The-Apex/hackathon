<?php
// Centralized Database Connection Helper for BloodSync
require_once __DIR__ . '/config.php';

$db_host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$db_user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_ENV['DB_PASS'] ?? '');
$db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'blood_bank');
$db_port = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306));

$db = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($db->connect_error) {
    if (defined('API_REQUEST') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Database connection failed',
            'details' => $db->connect_error,
            'hint' => 'Check your DB_HOST, DB_USER, DB_PASS, and DB_NAME in .env or db.php.'
        ]);
    } else {
        die("<h3>Database Connection Error</h3><p>Could not connect to MySQL database <strong>" . htmlspecialchars($db_name) . "</strong>.</p><p>Error: " . htmlspecialchars($db->connect_error) . "</p><p>Make sure MySQL is running in XAMPP and the database exists in phpMyAdmin.</p>");
    }
    exit;
}

$db->set_charset("utf8mb4");
?>
