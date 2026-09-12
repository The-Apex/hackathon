<?php
header('Content-Type: application/json');
require_once 'db.php';

$result = $db->query("SELECT name, blood_type, units, DATE_FORMAT(created_at, '%b %d %H:%i') as created_at FROM donations ORDER BY created_at DESC LIMIT 5");
$donations = [];
while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}
echo json_encode($donations);
?>
