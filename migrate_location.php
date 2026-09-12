<?php
// Safe, Non-Destructive Database Migration for BloodSync Google Maps Integration

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "blood_bank";

$serverConn = new mysqli($db_host, $db_user, $db_pass);
if ($serverConn->connect_error) {
    die("Server connection failed: " . $serverConn->connect_error);
}

// Ensure blood_bank database exists
$serverConn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$serverConn->close();

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

echo "<h3>BloodSync Database Migration — Location Fields</h3>\n";

// Check if requests table exists
$checkTable = $db->query("SHOW TABLES LIKE 'requests'");
if ($checkTable->num_rows === 0) {
    echo "<p style='color:orange;'>Table 'requests' not found. Please run init.php first to seed the initial database.</p>\n";
    exit;
}

// Helper to check if column exists
function columnExists($db, $table, $column) {
    $res = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && $res->num_rows > 0;
}

$columnsToAdd = [
    'latitude' => "ALTER TABLE `requests` ADD COLUMN `latitude` DECIMAL(10,8) NULL AFTER `is_urgent`",
    'longitude' => "ALTER TABLE `requests` ADD COLUMN `longitude` DECIMAL(11,8) NULL AFTER `latitude`",
    'location_address' => "ALTER TABLE `requests` ADD COLUMN `location_address` VARCHAR(255) NULL AFTER `longitude`"
];

foreach ($columnsToAdd as $col => $sql) {
    if (!columnExists($db, 'requests', $col)) {
        if ($db->query($sql)) {
            echo "<p style='color:green;'>✅ Added column <code>$col</code> to requests successfully.</p>\n";
        } else {
            echo "<p style='color:red;'>❌ Failed to add column <code>$col</code>: " . $db->error . "</p>\n";
        }
    } else {
        echo "<p style='color:gray;'>ℹ️ Column <code>$col</code> already exists on requests.</p>\n";
    }
}

// Also ensure users table has latitude and longitude for blood banks / hospitals
$userCols = [
    'latitude' => "ALTER TABLE `users` ADD COLUMN `latitude` DECIMAL(10,8) NULL AFTER `address`",
    'longitude' => "ALTER TABLE `users` ADD COLUMN `longitude` DECIMAL(11,8) NULL AFTER `latitude`"
];

foreach ($userCols as $col => $sql) {
    if (!columnExists($db, 'users', $col)) {
        if ($db->query($sql)) {
            echo "<p style='color:green;'>✅ Added column <code>$col</code> to users successfully.</p>\n";
        } else {
            echo "<p style='color:red;'>❌ Failed to add column <code>$col</code> to users: " . $db->error . "</p>\n";
        }
    } else {
        echo "<p style='color:gray;'>ℹ️ Column <code>$col</code> already exists on users.</p>\n";
    }
}

echo "<p style='color:green; font-weight:bold;'>Migration completed successfully!</p>\n";
?>
