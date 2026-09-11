<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Drop existing tables
$db->query("DROP TABLE IF EXISTS donations");
$db->query("DROP TABLE IF EXISTS requests");
$db->query("DROP TABLE IF EXISTS inventory");
$db->query("DROP TABLE IF EXISTS users");

// Users Table
$db->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'hospital', 'bank') NOT NULL,
    city VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Donations Table
$db->query("CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    center_id INT NOT NULL,
    name VARCHAR(100),
    phone VARCHAR(20),
    blood_type VARCHAR(10),
    units INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (center_id) REFERENCES users(id)
)");

// Requests Table
$db->query("CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    center_id INT NOT NULL,
    requester_name VARCHAR(100),
    requester_type ENUM('user', 'hospital') DEFAULT 'user',
    phone VARCHAR(20),
    blood_type VARCHAR(10),
    units INT,
    status ENUM('pending', 'completed', 'received') DEFAULT 'pending',
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (center_id) REFERENCES users(id)
)");

// Inventory Table (Per Center)
$db->query("CREATE TABLE inventory (
    center_id INT NOT NULL,
    blood_type VARCHAR(10),
    units INT DEFAULT 0,
    PRIMARY KEY (center_id, blood_type),
    FOREIGN KEY (center_id) REFERENCES users(id)
)");

// Create Demo Centers
$demoPass = password_hash('admin123', PASSWORD_DEFAULT);

$centers = [
    ['Central Blood Bank', 'bank@bloodsync.com', '+911234567890', 'bank', 'Mumbai', '123 Main St, Andheri West'],
    ['Ahmedabad Red Cross Blood Centre', 'redcross@bloodsync.com', '+917926578940', 'bank', 'Ahmedabad', 'Paldi, Ahmedabad'],
    ['Civil Hospital Regional Blood Center', 'civil@bloodsync.com', '+917922683721', 'hospital', 'Ahmedabad', 'Asarwa, Ahmedabad'],
    ['Prathama Blood Centre', 'prathama@bloodsync.com', '+917926862800', 'bank', 'Ahmedabad', 'Vasna, Ahmedabad'],
    ['Surat Raktdan Kendra & Research Centre', 'surat@bloodsync.com', '+912612325555', 'bank', 'Surat', 'Khatodara, Surat'],
    ['Baroda Citizen Blood Bank', 'baroda@bloodsync.com', '+912652361234', 'bank', 'Vadodara', 'Sayajigunj, Vadodara'],
    ['Rajkot Voluntary Blood Bank', 'rajkot@bloodsync.com', '+912812234567', 'bank', 'Rajkot', 'Dhebar Road, Rajkot']
];

foreach ($centers as $c) {
    $db->query("INSERT INTO users (name, email, password, phone, role, city, address) VALUES ('{$c[0]}', '{$c[1]}', '$demoPass', '{$c[2]}', '{$c[3]}', '{$c[4]}', '{$c[5]}')");
    $centerId = $db->insert_id;
    
    // Seed Inventory for each center with random units
    $types = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];
    foreach ($types as $type) {
        $units = rand(0, 15);
        $db->query("INSERT INTO inventory (center_id, blood_type, units) VALUES ($centerId, '$type', $units)");
    }
}
echo "✅ Database initialized with authentication schema!<br>";
echo "Demo Blood Bank Login: bank@bloodsync.com / admin123";
?>
