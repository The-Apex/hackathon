<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$center_id = (int)($_POST['center_id'] ?? 0);
$requester_name = trim($_POST['requester_name'] ?? '');
$requester_type = trim($_POST['requester_type'] ?? 'user');
$phone = trim($_POST['phone'] ?? '');
$blood_type = trim($_POST['blood_type'] ?? '');
$units = (int)($_POST['units'] ?? 0);
$is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
$latitude = (isset($_POST['latitude']) && $_POST['latitude'] !== '' && is_numeric($_POST['latitude'])) ? (float)$_POST['latitude'] : null;
$longitude = (isset($_POST['longitude']) && $_POST['longitude'] !== '' && is_numeric($_POST['longitude'])) ? (float)$_POST['longitude'] : null;
$location_address = trim($_POST['location_address'] ?? '');

// Strict Server-Side Validation: Ensure coordinates are valid geographic ranges
if ($latitude !== null && ($latitude < -90.0 || $latitude > 90.0)) {
    $latitude = null;
}
if ($longitude !== null && ($longitude < -180.0 || $longitude > 180.0)) {
    $longitude = null;
}
if (empty($location_address) && $latitude !== null && $longitude !== null) {
    $location_address = round($latitude, 5) . ', ' . round($longitude, 5);
}

$stmt = $db->prepare("INSERT INTO requests (user_id, center_id, requester_name, requester_type, phone, blood_type, units, is_urgent, latitude, longitude, location_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssssidds", $user_id, $center_id, $requester_name, $requester_type, $phone, $blood_type, $units, $is_urgent, $latitude, $longitude, $location_address);
$stmt->execute();

$requestId = $db->insert_id;
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
            <div style="margin: 14px 0; padding: 10px 16px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; font-size: 13px; color: #92400e; line-height: 1.4;">
                ⏳ <strong>Status: AWAITING BLOOD BANK APPROVAL</strong><br>
                Your request is strictly pending. The destination blood bank must manually verify inventory and approve it in their dashboard before collection is authorized and SMS/Email is dispatched.
            </div>
            <?php if ($latitude !== null && $longitude !== null): ?>
                <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:16px;">
                    📍 <strong>Location Pinpointed:</strong> <?php echo htmlspecialchars($location_address); ?>
                </p>
                <div style="display:flex; gap:10px; justify-content:center; margin-bottom:12px;">
                    <a href="request_details.php?id=<?php echo $requestId; ?>" class="btn btn-outline" style="width:auto; padding:8px 16px;">🗺️ View Location & Map</a>
                    <a href="<?php echo htmlspecialchars($redirect); ?>" class="btn btn-primary" style="width:auto; padding:8px 16px;">← Back to Dashboard</a>
                </div>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($redirect); ?>" class="btn btn-primary">← Back to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
