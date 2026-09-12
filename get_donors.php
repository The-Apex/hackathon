<?php
header('Content-Type: application/json');
require_once 'session_check.php';
$userId = $_SESSION['user_id'];
require_once 'db.php';

$result = $db->query("SELECT id, name, phone, blood_type, units, status, DATE_FORMAT(created_at, '%b %d %H:%i') as created_at FROM donations WHERE center_id = $userId ORDER BY created_at DESC");
$donors = [];
while ($row = $result->fetch_assoc()) {
    $donors[] = $row;
}
echo json_encode($donors);
?>
