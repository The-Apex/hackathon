<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$requester_name = $db->real_escape_string($_POST['requester_name']);
$requester_type = $db->real_escape_string($_POST['requester_type']); // 'user' or 'hospital'
$blood_type = $db->real_escape_string($_POST['blood_type']);
$units = (int)$_POST['units'];
$is_urgent = isset($_POST['is_urgent']) && $_POST['is_urgent'] == '1' ? 1 : 0;

$db->query("INSERT INTO requests (requester_name, requester_type, blood_type, units, is_urgent) 
            VALUES ('$requester_name', '$requester_type', '$blood_type', $units, $is_urgent)");

$redirect = $requester_type == 'hospital' ? 'hospital.html' : 'user.html';

echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='style.css'></head><body style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f7f6;'>";
echo "<div class='panel' style='text-align:center;'>";
echo "<div class='msg-success'>✅ Blood request for $units units of $blood_type submitted successfully!</div>";
echo "<a href='$redirect'><button>← Back to Dashboard</button></a>";
echo "</div></body></html>";
?>
