<?php
header('Content-Type: application/json');
require_once 'session_check.php';
$userId = $_SESSION['user_id'];
require_once 'db.php';

$inventory = [
    'O+' => 0, 'A+' => 0, 'B+' => 0, 'AB+' => 0,
    'O-' => 0, 'A-' => 0, 'B-' => 0, 'AB-' => 0
];
$result = $db->query("SELECT blood_type, units FROM inventory WHERE center_id = $userId ORDER BY blood_type ASC");
while ($row = $result->fetch_assoc()) {
    $inventory[$row['blood_type']] = $row['units'];
}
echo json_encode($inventory);
?>
