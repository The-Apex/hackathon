<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$city = isset($_GET['city']) ? $db->real_escape_string($_GET['city']) : '';
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';

$where = "role IN ('hospital', 'bank')";
if (!empty($city) && $city !== 'All') {
    $where .= " AND city = '$city'";
}
if (!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR address LIKE '%$search%')";
}

$query = "SELECT id, name, role, city, address, phone FROM users WHERE $where ORDER BY city ASC, name ASC";
$result = $db->query($query);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
}
echo json_encode($centers);
?>
