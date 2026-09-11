<?php
session_start();
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$scope = isset($_GET['scope']) ? $_GET['scope'] : '';
$typeFilter = isset($_GET['type']) ? $db->real_escape_string($_GET['type']) : '';

$where = "1=1";

// If scope=mine, show only current user's requests
if ($scope === 'mine' && isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $where .= " AND user_id = $uid";
}

// If type=hospital, filter by requester_type
if ($typeFilter === 'hospital') {
    $where .= " AND requester_type = 'hospital'";
}

$query = "SELECT id, requester_name, requester_type, phone, blood_type, units, status, is_urgent, 
          DATE_FORMAT(created_at, '%b %d %H:%i') as created_at 
          FROM requests 
          WHERE $where
          ORDER BY is_urgent DESC, 
          CASE WHEN status = 'pending' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END, 
          created_at DESC";

$result = $db->query($query);
$requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
echo json_encode($requests);
?>
