<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bank') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

$donation_id = isset($_POST['donation_id']) ? (int)$_POST['donation_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($donation_id === 0 || $action !== 'confirm') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Ensure the donation belongs to this bank and is pending
$center_id = $_SESSION['user_id'];
$res = $db->query("SELECT * FROM donations WHERE id = $donation_id AND center_id = $center_id AND status = 'pending'");

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Donation not found or already processed']);
    exit;
}

$donation = $res->fetch_assoc();
$units = $donation['units'];
$blood_type = $db->real_escape_string($donation['blood_type']);

$db->begin_transaction();
try {
    $db->query("UPDATE donations SET status = 'completed' WHERE id = $donation_id");
    $db->query("UPDATE inventory SET units = units + $units WHERE blood_type = '$blood_type' AND center_id = $center_id");
    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
