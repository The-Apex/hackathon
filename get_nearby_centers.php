<?php
header('Content-Type: application/json');
require_once 'session_check.php';
require_once 'db.php';

$lat = isset($_GET['lat']) && $_GET['lat'] !== '' && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
$lng = isset($_GET['lng']) && $_GET['lng'] !== '' && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : null;
$bloodType = isset($_GET['blood_type']) ? trim($_GET['blood_type']) : '';
$units = isset($_GET['units']) ? (int)$_GET['units'] : 0;
$radiusKm = isset($_GET['radius']) && is_numeric($_GET['radius']) ? (float)$_GET['radius'] : 150.0;

if ($lat === null || $lng === null) {
    echo json_encode(['error' => 'Missing coordinates (lat, lng required).', 'centers' => []]);
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

$compatibleTypes = !empty($bloodType) && isset($compatibility[$bloodType]) ? $compatibility[$bloodType] : ($bloodType ? [$bloodType] : []);

// Haversine formula calculation in kilometers
$distFormula = "ROUND((6371 * acos(least(1.0, cos(radians($lat)) * cos(radians(COALESCE(u.latitude, $lat))) * cos(radians(COALESCE(u.longitude, $lng)) - radians($lng)) + sin(radians($lat)) * sin(radians(COALESCE(u.latitude, $lat)))))), 1)";

$query = "SELECT u.id, u.name, u.role, u.city, u.address, u.phone, u.latitude, u.longitude,
                 $distFormula as distance_km
          FROM users u
          WHERE u.role IN ('bank', 'hospital')
            AND u.latitude IS NOT NULL 
            AND u.longitude IS NOT NULL
          HAVING distance_km <= $radiusKm
          ORDER BY distance_km ASC
          LIMIT 25";

$result = $db->query($query);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $centerId = (int)$row['id'];
        $row['distance_km'] = (float)$row['distance_km'];
        $row['latitude'] = (float)$row['latitude'];
        $row['longitude'] = (float)$row['longitude'];

        // Get full inventory for this center
        $invRes = $db->query("SELECT blood_type, units FROM inventory WHERE center_id = $centerId");
        $inventory = [];
        $totalUnits = 0;
        $matchedUnits = 0;
        $exactUnits = 0;
        $matchedType = '';

        if ($invRes) {
            while ($invRow = $invRes->fetch_assoc()) {
                $bt = $invRow['blood_type'];
                $u = (int)$invRow['units'];
                $inventory[$bt] = $u;
                $totalUnits += $u;

                if (!empty($bloodType)) {
                    if ($bt === $bloodType && $u >= $units) {
                        $exactUnits = $u;
                    }
                    if (in_array($bt, $compatibleTypes) && $u >= $units && empty($matchedType)) {
                        $matchedUnits = $u;
                        $matchedType = $bt;
                    }
                }
            }
        }

        $row['inventory'] = $inventory;
        $row['total_units'] = $totalUnits;
        $row['is_exact_match'] = ($exactUnits > 0);
        $row['has_stock'] = (!empty($bloodType) ? ($matchedUnits > 0) : ($totalUnits > 0));
        $row['matched_blood_type'] = $exactUnits > 0 ? $bloodType : $matchedType;
        $row['matched_units'] = $exactUnits > 0 ? $exactUnits : $matchedUnits;

        $centers[] = $row;
    }
}

echo json_encode(['status' => 'success', 'count' => count($centers), 'centers' => $centers]);
exit;
