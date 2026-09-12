<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once 'db.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$scope = isset($_GET['scope']) ? $_GET['scope'] : '';
$typeFilter = isset($_GET['type']) ? $db->real_escape_string($_GET['type']) : '';

$where = "1=1";

// If scope=mine, show only current user's requests
if ($scope === 'mine' && $userId > 0) {
    $where .= " AND r.user_id = $userId";
} else {
    // Show requests destined for this center
    $where .= " AND r.center_id = $userId";
}

// If type=hospital, filter by requester_type
if ($typeFilter === 'hospital') {
    $where .= " AND r.requester_type = 'hospital'";
}

$orderBy = ($scope === 'mine')
    ? "r.created_at DESC"
    : "r.is_urgent DESC, CASE WHEN r.status = 'pending' THEN 1 WHEN r.status = 'completed' THEN 2 ELSE 3 END, r.created_at DESC";

$query = "SELECT r.id, r.user_id, r.center_id, r.requester_name, r.requester_type, r.phone, r.blood_type, r.units, r.status, r.is_urgent, 
          r.latitude, r.longitude, r.location_address,
          c.name as center_name, c.phone as center_phone, c.address as center_address, c.city as center_city,
          DATE_FORMAT(r.created_at, '%b %d %H:%i') as created_at 
          FROM requests r
          LEFT JOIN users c ON r.center_id = c.id
          WHERE $where
          ORDER BY $orderBy";

$result = $db->query($query);
$requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
echo json_encode($requests);
?>
