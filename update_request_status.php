<?php
session_start();
$db = new mysqli("localhost", "root", "", "blood_bank");

$id = (int)$_POST['id'];
$status = $db->real_escape_string($_POST['status']);

if (isset($_POST['deduct_blood']) && isset($_POST['deduct_units'])) {
    $blood_type = $db->real_escape_string(trim($_POST['deduct_blood']));
    $units = (int)$_POST['deduct_units'];

    // Check inventory first
    $res = $db->query("SELECT units FROM inventory WHERE blood_type = '$blood_type'");
    $row = $res ? $res->fetch_assoc() : null;
    
    if (!$row || $row['units'] < $units) {
        echo "Error: Not enough blood in inventory!";
        exit;
    }

    // Deduct
    $db->query("UPDATE inventory SET units = units - $units WHERE blood_type = '$blood_type'");
}

$db->query("UPDATE requests SET status = '$status' WHERE id = $id");
echo "Success";
?>
