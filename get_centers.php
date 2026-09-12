<?php
header('Content-Type: application/json');
require_once 'db.php';

$city = isset($_GET['city']) ? $db->real_escape_string($_GET['city']) : '';
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';

$where = "role IN ('hospital', 'bank')";
if (!empty($city) && $city !== 'All') {
    $where .= " AND city = '$city'";
}
if (!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR address LIKE '%$search%')";
}

$query = "SELECT u.id, u.name, u.role, u.city, u.address, u.phone,
                 (SELECT GROUP_CONCAT(blood_type SEPARATOR ', ') FROM inventory WHERE center_id = u.id AND units > 0) as available_groups
          FROM users u WHERE $where ORDER BY u.city ASC, u.name ASC";
$result = $db->query($query);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
}
echo json_encode($centers);
?>
