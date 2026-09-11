<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$name = $db->real_escape_string($_POST['name']);
$blood_type = $db->real_escape_string($_POST['blood_type']);
$units = (int)$_POST['units'];

$db->query("INSERT INTO donations (name, blood_type, units) VALUES ('$name', '$blood_type', $units)");
$db->query("UPDATE inventory SET units = units + $units WHERE blood_type = '$blood_type'");

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'user.html';

echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='style.css'></head><body style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f7f6;'>";
echo "<div class='panel' style='text-align:center;'>";
echo "<div class='msg-success'>✅ Thank you $name for donating $units units of $blood_type blood!</div>";
echo "<a href='$redirect'><button>← Back to Dashboard</button></a>";
echo "</div></body></html>";
?>
