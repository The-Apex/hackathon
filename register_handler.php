<?php
session_start();
require_once 'db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? '');
$city = trim($_POST['city'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validate
if (strlen($password) < 6) {
    header("Location: register.html?role=$role&error=" . urlencode("Password must be at least 6 characters."));
    exit;
}

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$check = $stmt->get_result();
if ($check->num_rows > 0) {
    header("Location: register.html?role=$role&error=" . urlencode("An account with this email already exists."));
    exit;
}

// Hash password and insert
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$insStmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, city, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insStmt->bind_param("sssssss", $name, $email, $hashedPassword, $phone, $role, $city, $address);
$insStmt->execute();

$userId = $db->insert_id;

// Auto-login after registration
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_phone'] = $phone;
$_SESSION['role'] = $role;

// Redirect based on role
switch ($role) {
    case 'user': header('Location: user.php'); break;
    case 'hospital': header('Location: hospital.php'); break;
    case 'bank': header('Location: bank.php'); break;
    default: header('Location: index.html');
}
exit;
?>
