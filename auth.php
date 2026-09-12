<?php
session_start();
require_once 'db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? '');

// Find user securely
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.html?role=$role&error=" . urlencode("No account found with this email and role."));
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    header("Location: login.html?role=$role&error=" . urlencode("Incorrect password. Please try again."));
    exit;
}

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_phone'] = $user['phone'];
$_SESSION['role'] = $user['role'];

// Redirect based on role
switch ($user['role']) {
    case 'user': header('Location: user.php'); break;
    case 'hospital': header('Location: hospital.php'); break;
    case 'bank': header('Location: bank.php'); break;
    default: header('Location: index.html');
}
exit;
?>
