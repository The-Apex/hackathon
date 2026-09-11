<?php
session_start();
$db = new mysqli("localhost", "root", "", "blood_bank");

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $db->real_escape_string($_POST['name']);
$phone = $db->real_escape_string($_POST['phone']);
$blood_type = $db->real_escape_string($_POST['blood_type']);
$units = (int)$_POST['units'];
$center_id = (int)$_POST['center_id'];

$db->query("INSERT INTO donations (user_id, center_id, name, phone, blood_type, units) VALUES ($user_id, $center_id, '$name', '$phone', '$blood_type', $units)");
$db->query("UPDATE inventory SET units = units + $units WHERE blood_type = '$blood_type' AND center_id = $center_id");

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'user.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Successful — BloodSync</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="success-page">
        <div class="success-box animate-in">
            <div class="success-icon">✅</div>
            <h2>Thank You, <?php echo htmlspecialchars($name); ?>!</h2>
            <p>You have successfully donated <strong><?php echo $units; ?> units</strong> of <strong><?php echo htmlspecialchars($blood_type); ?></strong> blood.</p>
            <a href="<?php echo htmlspecialchars($redirect); ?>" class="btn btn-primary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
