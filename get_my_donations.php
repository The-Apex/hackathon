<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$uid = (int)$_SESSION['user_id'];

$result = $db->query("SELECT d.id, d.blood_type, d.units, d.status, u.name as center_name, DATE_FORMAT(d.created_at, '%b %d %H:%i') as created_at 
                       FROM donations d 
                       JOIN users u ON d.center_id = u.id 
                       WHERE d.user_id = $uid ORDER BY d.created_at DESC");
$donations = [];
while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}
echo json_encode($donations);
?>
