<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$typeFilter = isset($_GET['type']) ? $db->real_escape_string($_GET['type']) : '';
$where = $typeFilter == 'hospital' ? "WHERE requester_type = 'hospital'" : "";

// Sort by: Urgent first -> Pending -> Completed/Received
$query = "SELECT id, requester_name, requester_type, blood_type, units, status, is_urgent, 
          DATE_FORMAT(created_at, '%b %d %H:%i') as created_at 
          FROM requests 
          $where
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
