<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Drop existing tables to apply new schema
$db->query("DROP TABLE IF EXISTS donations");
$db->query("DROP TABLE IF EXISTS requests");
$db->query("DROP TABLE IF EXISTS inventory");

// Create Donations Table
$db->query("CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    blood_type VARCHAR(10),
    units INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create Requests Table
$db->query("CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_name VARCHAR(100),
    requester_type ENUM('user', 'hospital') DEFAULT 'user',
    blood_type VARCHAR(10),
    units INT,
    status ENUM('pending', 'completed', 'received') DEFAULT 'pending',
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create Inventory Table
$db->query("CREATE TABLE IF NOT EXISTS inventory (
    blood_type VARCHAR(10) PRIMARY KEY,
    units INT
)");

// Seed Inventory
$db->query("INSERT INTO inventory VALUES ('O+', 10)");
$db->query("INSERT INTO inventory VALUES ('A+', 8)");
$db->query("INSERT INTO inventory VALUES ('B+', 12)");
$db->query("INSERT INTO inventory VALUES ('AB+', 5)");
$db->query("INSERT INTO inventory VALUES ('O-', 4)");
$db->query("INSERT INTO inventory VALUES ('A-', 2)");
$db->query("INSERT INTO inventory VALUES ('B-', 3)");
$db->query("INSERT INTO inventory VALUES ('AB-', 1)");

echo "✅ Database initialized with new Hackathon schema!";
?>
