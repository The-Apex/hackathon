<?php
session_start();
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$uid = (int)$_SESSION['user_id'];

$result = $db->query("SELECT blood_type, units, DATE_FORMAT(created_at, '%b %d %H:%i') as created_at 
                       FROM donations WHERE user_id = $uid ORDER BY created_at DESC");
$donations = [];
while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}
echo json_encode($donations);
?>
