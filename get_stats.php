<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$stats = ['total_blood' => 0, 'total_requests' => 0, 'total_donations' => 0];

// Total Blood
$res = $db->query("SELECT SUM(units) as total FROM inventory");
if ($row = $res->fetch_assoc()) $stats['total_blood'] = $row['total'] ? $row['total'] : 0;

// Total Requests
$res = $db->query("SELECT COUNT(*) as total FROM requests");
if ($row = $res->fetch_assoc()) $stats['total_requests'] = $row['total'];

// Total Donations
$res = $db->query("SELECT COUNT(*) as total FROM donations");
if ($row = $res->fetch_assoc()) $stats['total_donations'] = $row['total'];

echo json_encode($stats);
?>
