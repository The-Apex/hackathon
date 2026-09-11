<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="BloodBank_Report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Write Inventory
fputcsv($output, ['--- BLOOD INVENTORY ---']);
fputcsv($output, ['Blood Type', 'Units Available']);
$res = $db->query("SELECT blood_type, units FROM inventory");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}

fputcsv($output, []);

// Write Donors
fputcsv($output, ['--- DONOR RECORDS ---']);
fputcsv($output, ['Name', 'Phone', 'Blood Type', 'Units', 'Date']);
$res = $db->query("SELECT name, phone, blood_type, units, created_at FROM donations ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
?>
