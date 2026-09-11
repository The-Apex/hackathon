<?php
session_start();

// Check if user is logged in
function requireLogin($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit;
    }
    
    // Check role if specified
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: index.html');
        exit;
    }
}

// Get current user info
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? ''
    ];
}
?>
