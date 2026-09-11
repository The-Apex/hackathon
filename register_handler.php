<?php
session_start();
$db = new mysqli("localhost", "root", "", "blood_bank");

$name = $db->real_escape_string(trim($_POST['name']));
$email = $db->real_escape_string(trim($_POST['email']));
$phone = $db->real_escape_string(trim($_POST['phone']));
$password = $_POST['password'];
$role = $db->real_escape_string($_POST['role']);
$city = isset($_POST['city']) ? $db->real_escape_string(trim($_POST['city'])) : '';
$address = isset($_POST['address']) ? $db->real_escape_string(trim($_POST['address'])) : '';

// Validate
if (strlen($password) < 6) {
    header("Location: register.html?role=$role&error=" . urlencode("Password must be at least 6 characters."));
    exit;
}

// Check if email already exists for this role
$check = $db->query("SELECT id FROM users WHERE email = '$email' AND role = '$role'");
if ($check->num_rows > 0) {
    header("Location: register.html?role=$role&error=" . urlencode("An account with this email already exists."));
    exit;
}

// Hash password and insert
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$db->query("INSERT INTO users (name, email, password, phone, role, city, address) VALUES ('$name', '$email', '$hashedPassword', '$phone', '$role', '$city', '$address')");

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
