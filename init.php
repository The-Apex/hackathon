<?php
$serverConn = new mysqli("localhost", "root", "");
if ($serverConn->connect_error) {
    die("Connection failed: " . $serverConn->connect_error);
}
$serverConn->query("CREATE DATABASE IF NOT EXISTS blood_bank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$serverConn->close();

$db = new mysqli("localhost", "root", "", "blood_bank");
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
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
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
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
    status ENUM('pending', 'completed') DEFAULT 'pending',
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
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    location_address VARCHAR(255) NULL,
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

// Notifications Table (Logs SMS and Email alerts)
$db->query("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    recipient_name VARCHAR(100),
    recipient_phone VARCHAR(20),
    recipient_email VARCHAR(100),
    type VARCHAR(30) DEFAULT 'email',
    subject VARCHAR(255),
    message TEXT,
    status ENUM('sent', 'logged', 'failed') DEFAULT 'sent',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_req (request_id)
)");

// Create Demo Centers
$demoPass = password_hash('admin123', PASSWORD_DEFAULT);

$users = [
    // Standard Demo Logins
    ['Ahmedabad Red Cross Blood Centre', 'bank@bloodsync.com', '+917926578940', 'bank', 'Ahmedabad', 'Paldi, Ahmedabad', 23.012000, 72.562500],
    ['Civil Hospital Regional Blood Center', 'hospital@bloodsync.com', '+917922683721', 'hospital', 'Ahmedabad', 'Asarwa, Ahmedabad', 23.053120, 72.585540],
    ['Demo User', 'user@bloodsync.com', '+919876543210', 'user', 'Ahmedabad', '456 User Lane, Ahmedabad', 23.022500, 72.571400],

    // Additional Ahmedabad
    ['Prathama Blood Centre', 'prathama@bloodsync.com', '+917926862800', 'bank', 'Ahmedabad', 'Vasna, Ahmedabad', 23.003300, 72.548900],
    ['Apollo Hospitals Blood Bank', 'apollo.ahd@bloodsync.com', '+917966701800', 'hospital', 'Ahmedabad', 'Bhat, Gandhinagar Rd, Ahmedabad', 23.111200, 72.616700],

    // Gandhinagar
    ['Gandhinagar Civil Hospital Blood Bank', 'gandhinagar@bloodsync.com', '+917923222384', 'hospital', 'Gandhinagar', 'Sector 12, Gandhinagar', 23.215600, 72.636900],

    // Surat
    ['Surat Rakt Daan Kendra & Research Centre', 'surat@bloodsync.com', '+912612325555', 'bank', 'Surat', 'Khatodara, Surat', 21.170200, 72.831100],
    ['New Civil Hospital Blood Bank Surat', 'surathosp@bloodsync.com', '+912612244456', 'hospital', 'Surat', 'Majura Gate, Surat', 21.161000, 72.812000],

    // Vadodara
    ['Baroda Citizen Blood Bank', 'baroda@bloodsync.com', '+912652361234', 'bank', 'Vadodara', 'Sayajigunj, Vadodara', 22.307200, 73.181200],
    ['SSG Hospital Blood Bank Vadodara', 'ssghosp@bloodsync.com', '+912652424000', 'hospital', 'Vadodara', 'Jail Road, Vadodara', 22.302500, 73.192000],

    // Rajkot
    ['Rajkot Voluntary Blood Bank', 'rajkot@bloodsync.com', '+912812234567', 'bank', 'Rajkot', 'Dhebar Road, Rajkot', 22.303900, 70.802200],
    ['PDU Civil Hospital Blood Bank Rajkot', 'rajkothosp@bloodsync.com', '+912812450505', 'hospital', 'Rajkot', 'Hospital Chowk, Rajkot', 22.298000, 70.796000],

    // Bhavnagar
    ['Sir T Hospital Regional Blood Bank', 'bhavnagar@bloodsync.com', '+912782424000', 'hospital', 'Bhavnagar', 'Kalanala, Bhavnagar', 21.764500, 72.151900],
    ['Red Cross Blood Centre Bhavnagar', 'redcross.bhav@bloodsync.com', '+912782427800', 'bank', 'Bhavnagar', 'Waghawadi Road, Bhavnagar', 21.770000, 72.145000],

    // Jamnagar
    ['GG Hospital Blood Bank Jamnagar', 'jamnagar@bloodsync.com', '+912882550204', 'hospital', 'Jamnagar', 'Indira Marg, Jamnagar', 22.470700, 70.057700],

    // Junagadh
    ['GMERS Civil Hospital Blood Bank Junagadh', 'junagadh@bloodsync.com', '+912852631500', 'hospital', 'Junagadh', 'Majevadi Gate, Junagadh', 21.522200, 70.457900],

    // Anand & Nadiad
    ['Shree Krishna Hospital Blood Bank', 'anand@bloodsync.com', '+912692228400', 'bank', 'Anand', 'Gokalnagar, Karamsad, Anand', 22.545800, 72.898900],
    ['Muljibhai Patel Hospital Blood Centre', 'nadiad@bloodsync.com', '+912682520323', 'hospital', 'Nadiad', 'Dr. V.V. Desai Marg, Nadiad', 22.691600, 72.863400],

    // Bharuch & Ankleshwar
    ['Civil Hospital Blood Bank Bharuch', 'bharuch@bloodsync.com', '+912642240500', 'hospital', 'Bharuch', 'Station Road, Bharuch', 21.705100, 72.995900],
    ['Rotary Blood Bank Ankleshwar', 'ankleshwar@bloodsync.com', '+912646246000', 'bank', 'Ankleshwar', 'GIDC, Ankleshwar', 21.626400, 73.002800],

    // Navsari, Valsad & Vapi
    ['Rotary Eye & Blood Bank Navsari', 'navsari@bloodsync.com', '+912637257000', 'bank', 'Navsari', 'Lunsikui, Navsari', 20.946700, 72.952000],
    ['Kasturba Hospital Blood Bank Valsad', 'valsad@bloodsync.com', '+912632254000', 'hospital', 'Valsad', 'Kalyan Baug, Valsad', 20.610000, 72.925000],
    ['Haria L.G. Rotary Blood Bank Vapi', 'vapi@bloodsync.com', '+912602431000', 'bank', 'Vapi', 'Gunjan, Vapi', 20.371800, 72.904300],

    // Bhuj & Gandhidham (Kutch)
    ['GK General Hospital Blood Bank Bhuj', 'bhuj@bloodsync.com', '+912832250101', 'hospital', 'Bhuj', 'Lotus Colony, Bhuj (Kutch)', 23.242000, 69.666900],
    ['Rotary Kutch Blood Centre Bhuj', 'rotarybhuj@bloodsync.com', '+912832222000', 'bank', 'Bhuj', 'Station Road, Bhuj (Kutch)', 23.250000, 69.670000],
    ['Rotary Blood Bank Gandhidham', 'gandhidham@bloodsync.com', '+912836231000', 'bank', 'Gandhidham', 'Sector 1, Gandhidham (Kutch)', 23.075300, 70.133700],

    // Mehsana & Patan
    ['Lions Blood Bank Mehsana', 'mehsana@bloodsync.com', '+912762251000', 'bank', 'Mehsana', 'Radhanpur Road, Mehsana', 23.588000, 72.369300],
    ['Dharpur General Hospital Blood Bank', 'patan@bloodsync.com', '+912766255000', 'hospital', 'Patan', 'Dharpur, Patan', 23.834000, 72.128000],

    // Palanpur (Banaskantha) & Himatnagar (Sabarkantha)
    ['Banas Voluntary Blood Bank Palanpur', 'palanpur@bloodsync.com', '+912742252000', 'bank', 'Palanpur', 'Simla Gate, Palanpur', 24.172400, 72.434600],
    ['GMERS Civil Hospital Blood Bank Himatnagar', 'himatnagar@bloodsync.com', '+912772240000', 'hospital', 'Himatnagar', 'Idar Highway, Himatnagar', 23.597700, 72.969800],

    // Morbi, Porbandar, Surendranagar
    ['Morbi Voluntary Blood Bank', 'morbi@bloodsync.com', '+912822230000', 'bank', 'Morbi', 'Sanala Road, Morbi', 22.812000, 70.838000],
    ['Bhavsinhji Hospital Blood Bank Porbandar', 'porbandar@bloodsync.com', '+912862242000', 'hospital', 'Porbandar', 'MG Road, Porbandar', 21.641700, 69.629300],
    ['CU Shah Medical College Blood Bank', 'surendranagar@bloodsync.com', '+912752287000', 'hospital', 'Surendranagar', 'Dudhrej Road, Surendranagar', 22.727500, 71.637000],

    // Amreli, Veraval, Godhra, Dahod, Botad
    ['Civil Hospital Blood Bank Amreli', 'amreli@bloodsync.com', '+912792223000', 'hospital', 'Amreli', 'Chakkargadh Road, Amreli', 21.603200, 71.222300],
    ['Aditya Birla Memorial Blood Bank Veraval', 'veraval@bloodsync.com', '+912876245000', 'bank', 'Veraval', 'Somnath Highway, Veraval', 20.907700, 70.367900],
    ['Civil Hospital Blood Bank Godhra', 'godhra@bloodsync.com', '+912672241000', 'hospital', 'Godhra', 'Vavdi Road, Godhra', 22.776600, 73.614900],
    ['Zydus Medical College Blood Bank Dahod', 'dahod@bloodsync.com', '+912673238000', 'hospital', 'Dahod', 'Nimnaliya, Dahod', 22.834500, 74.254600],
    ['Sonwala General Hospital Blood Bank Botad', 'botad@bloodsync.com', '+912849222000', 'hospital', 'Botad', 'Station Road, Botad', 22.170600, 71.666500]
];

$insUserStmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, city, address, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($users as $u) {
    $lat = isset($u[6]) ? $u[6] : null;
    $lng = isset($u[7]) ? $u[7] : null;
    $insUserStmt->bind_param("sssssssdd", $u[0], $u[1], $demoPass, $u[2], $u[3], $u[4], $u[5], $lat, $lng);
    $insUserStmt->execute();
    $userId = $db->insert_id;
    
    // Seed healthy inventory for each center
    if ($u[3] === 'bank' || $u[3] === 'hospital') {
        $types = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];
        foreach ($types as $type) {
            $units = rand(4, 25);
            $db->query("INSERT INTO inventory (center_id, blood_type, units) VALUES ($userId, '$type', $units)");
        }
    }
}
echo "✅ Database initialized with complete Gujarat cities & blood centers!<br><br>";
echo "<strong>Demo Logins (Password for all: admin123):</strong><br>";
echo "User: user@bloodsync.com<br>";
echo "Hospital: hospital@bloodsync.com<br>";
echo "Blood Bank: bank@bloodsync.com<br>";
?>
