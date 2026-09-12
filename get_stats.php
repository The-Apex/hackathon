<?php
header('Content-Type: application/json');
require_once 'session_check.php';
$userId = $_SESSION['user_id'];
require_once 'db.php';

$stats = ['total_blood' => 0, 'total_requests' => 0, 'total_donations' => 0];

$res = $db->query("SELECT SUM(units) as total FROM inventory WHERE center_id = $userId");
if ($row = $res->fetch_assoc()) $stats['total_blood'] = $row['total'] ? $row['total'] : 0;

// Total Requests
$res = $db->query("SELECT COUNT(*) as total FROM requests WHERE center_id = $userId");
$stats['total_requests'] = $res->fetch_assoc()['total'];

$res = $db->query("SELECT COUNT(*) as total FROM donations WHERE center_id = $userId");
if ($row = $res->fetch_assoc()) $stats['total_donations'] = $row['total'];

echo json_encode($stats);
?>
