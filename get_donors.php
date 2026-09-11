<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$result = $db->query("SELECT name, phone, blood_type, units, DATE_FORMAT(created_at, '%b %d %H:%i') as created_at FROM donations ORDER BY created_at DESC");
$donors = [];
while ($row = $result->fetch_assoc()) {
    $donors[] = $row;
}
echo json_encode($donors);
?>
