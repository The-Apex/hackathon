<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$result = $db->query("SELECT blood_type, units FROM inventory");
$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[$row['blood_type']] = $row['units'];
}
echo json_encode($inventory);
?>
