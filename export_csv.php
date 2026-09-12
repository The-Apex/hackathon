<?php
require_once 'session_check.php';
requireLogin(['bank']);
$center_id = (int)$_SESSION['user_id'];
$center_name = preg_replace('/[^a-zA-Z0-9]/', '_', $_SESSION['user_name']);

require_once 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="BloodSync_' . $center_name . '_Report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Write Inventory
fputcsv($output, ['--- BLOOD INVENTORY ---']);
fputcsv($output, ['Blood Type', 'Units Available']);
$stmt = $db->prepare("SELECT blood_type, units FROM inventory WHERE center_id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    fputcsv($output, [$row['blood_type'], $row['units']]);
}

fputcsv($output, []);

// Write Donors
fputcsv($output, ['--- DONOR RECORDS ---']);
fputcsv($output, ['Donor Name', 'Phone Number', 'Blood Type', 'Units', 'Status', 'Date']);
$stmt = $db->prepare("SELECT name, phone, blood_type, units, status, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as date FROM donations WHERE center_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    // Format as formula to force Excel to treat them as plain text
    $phone_formatted = '="' . $row['phone'] . '"';
    $date_formatted = '="' . $row['date'] . '"';
    fputcsv($output, [$row['name'], $phone_formatted, $row['blood_type'], $row['units'], ucfirst($row['status']), $date_formatted]);
}

fclose($output);
?>
