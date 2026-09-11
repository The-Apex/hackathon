<?php
session_start();
$db = new mysqli("localhost", "root", "", "blood_bank");

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$requester_name = $db->real_escape_string($_POST['requester_name']);
$requester_type = $db->real_escape_string($_POST['requester_type']);
$phone = $db->real_escape_string($_POST['phone']);
$blood_type = $_POST['blood_type'];
$units = (int)$_POST['units'];
$is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
$center_id = (int)$_POST['center_id'];

$stmt = $db->prepare("INSERT INTO requests (user_id, center_id, requester_name, requester_type, phone, blood_type, units, is_urgent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssssi", $user_id, $center_id, $requester_name, $requester_type, $phone, $blood_type, $units, $is_urgent);
$stmt->execute();

$redirect = $requester_type == 'hospital' ? 'hospital.php' : 'user.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted — BloodSync</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="success-page">
        <div class="success-box animate-in">
            <div class="success-icon">📋</div>
            <h2>Request Submitted!</h2>
            <p>Your request for <strong><?php echo $units; ?> units</strong> of <strong><?php echo htmlspecialchars($blood_type); ?></strong> blood has been submitted. <?php echo $is_urgent ? '<span class="badge badge-urgent">URGENT</span>' : ''; ?></p>
            <a href="<?php echo htmlspecialchars($redirect); ?>" class="btn btn-primary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
