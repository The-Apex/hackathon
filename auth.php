<?php
session_start();
$db = new mysqli("localhost", "root", "", "blood_bank");

$email = $db->real_escape_string(trim($_POST['email']));
$password = $_POST['password'];
$role = $db->real_escape_string($_POST['role']);

// Find user
$result = $db->query("SELECT * FROM users WHERE email = '$email' AND role = '$role'");

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
