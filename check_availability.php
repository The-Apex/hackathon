<?php
header('Content-Type: application/json');
require_once 'session_check.php';
require_once 'db.php';

$city = isset($_GET['city']) ? $db->real_escape_string(trim($_GET['city'])) : '';
$bloodType = isset($_GET['blood_type']) ? $db->real_escape_string(trim($_GET['blood_type'])) : '';
$units = isset($_GET['units']) ? (int)$_GET['units'] : 0;

$hasCoords = (isset($_GET['lat']) && $_GET['lat'] !== '' && is_numeric($_GET['lat']) && 
              isset($_GET['lng']) && $_GET['lng'] !== '' && is_numeric($_GET['lng']));
$lat = $hasCoords ? (float)$_GET['lat'] : null;
$lng = $hasCoords ? (float)$_GET['lng'] : null;

// Require blood type and units, and at least city OR coordinates
if (!$bloodType || $units <= 0 || (!$city && !$hasCoords)) {
    echo json_encode([]);
    exit;
}

// Medical ABO & Rh Blood Compatibility Mapping
$compatibility = [
    'O-'  => ['O-'],
    'O+'  => ['O+', 'O-'],
    'A-'  => ['A-', 'O-'],
    'A+'  => ['A+', 'A-', 'O+', 'O-'],
    'B-'  => ['B-', 'O-'],
    'B+'  => ['B+', 'B-', 'O+', 'O-'],
    'AB-' => ['AB-', 'A-', 'B-', 'O-'],
    'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-']
];

$compatibleTypes = isset($compatibility[$bloodType]) ? $compatibility[$bloodType] : [$bloodType];
$escapedTypes = array_map(function($t) use ($db) { return "'" . $db->real_escape_string($t) . "'"; }, $compatibleTypes);
$typesList = implode(',', $escapedTypes);

if ($hasCoords) {
    // Haversine formula calculation in kilometers
    $distFormula = "ROUND((6371 * acos(least(1.0, cos(radians($lat)) * cos(radians(COALESCE(u.latitude, $lat))) * cos(radians(COALESCE(u.longitude, $lng)) - radians($lng)) + sin(radians($lat)) * sin(radians(COALESCE(u.latitude, $lat)))))), 1)";
    
    $whereLocation = "1=1";
    if (!empty($city)) {
        // If city is also provided, prioritize city or nearby radius (within 60 km)
        $whereLocation = "(u.city = '$city' OR $distFormula <= 60.0)";
    }

    $query = "SELECT u.id, u.name, u.city, u.address, u.phone, 
                     i.blood_type as available_blood_type, i.units,
                     $distFormula as distance_km
              FROM users u 
              JOIN inventory i ON u.id = i.center_id 
              WHERE $whereLocation
                AND i.blood_type IN ($typesList) 
                AND i.units >= $units 
                AND u.role IN ('hospital', 'bank')
              ORDER BY (CASE WHEN i.blood_type = '$bloodType' THEN 1 ELSE 2 END), 
                       distance_km ASC, 
                       i.units DESC";
} else {
    $query = "SELECT u.id, u.name, u.city, u.address, u.phone,
                     i.blood_type as available_blood_type, i.units,
                     NULL as distance_km
              FROM users u 
              JOIN inventory i ON u.id = i.center_id 
              WHERE u.city = '$city' 
                AND i.blood_type IN ($typesList) 
                AND i.units >= $units 
                AND u.role IN ('hospital', 'bank')
              ORDER BY (CASE WHEN i.blood_type = '$bloodType' THEN 1 ELSE 2 END), 
                       i.units DESC";
}

$result = $db->query($query);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['is_exact_match'] = ($row['available_blood_type'] === $bloodType);
        $row['requested_blood_type'] = $bloodType;
        $row['distance_km'] = $row['distance_km'] !== null ? (float)$row['distance_km'] : null;
        $centers[] = $row;
    }
}

echo json_encode($centers);
exit;
?>
