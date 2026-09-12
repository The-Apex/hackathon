<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$blood_type = trim($_POST['blood_type'] ?? '');
$units = (int)($_POST['units'] ?? 0);
$center_id = (int)($_POST['center_id'] ?? 0);

if ($user_id <= 0 || $center_id <= 0 || $units <= 0 || empty($name) || empty($blood_type)) {
    die("Error: Invalid donation parameters.");
}

$stmt = $db->prepare("INSERT INTO donations (user_id, center_id, name, phone, blood_type, units) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssi", $user_id, $center_id, $name, $phone, $blood_type, $units);
$stmt->execute();

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'user.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Pledge Successful — BloodSync</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="success-page">
        <div class="success-box animate-in">
            <div class="success-icon" style="background: var(--warning-bg); color: var(--warning);">⏳</div>
            <h2>Thank You, <?php echo htmlspecialchars($name); ?>!</h2>
            <p>Your donation pledge of <strong><?php echo $units; ?> units</strong> of <strong><?php echo htmlspecialchars($blood_type); ?></strong> blood has been sent to the center.</p>
            <p style="margin-top: 10px; font-weight: 600; color: var(--warning);">Status: Pending Confirmation</p>
            <a href="<?php echo htmlspecialchars($redirect); ?>" class="btn btn-primary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
