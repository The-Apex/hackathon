<?php
header('Content-Type: application/json');
require_once 'session_check.php';
if ($_SESSION['role'] !== 'bank') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && is_array($data)) {
    foreach ($data as $type => $units) {
        $type = $db->real_escape_string($type);
        $units = (int)$units;
        // Insert or update inventory row for the blood bank
        $db->query("INSERT INTO inventory (center_id, blood_type, units) VALUES ($userId, '$type', $units) ON DUPLICATE KEY UPDATE units = $units");
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
