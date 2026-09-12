<?php
require_once 'session_check.php';
requireLogin(['user', 'hospital', 'bank']);
$currentUser = getCurrentUser();
require_once 'db.php';
require_once 'config.php';

$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($requestId <= 0) {
    die("Invalid request ID.");
}

$stmt = $db->prepare("
    SELECT r.*, 
           u.name as requester_user_name, u.email as requester_email,
           c.name as center_name, c.city as center_city, c.address as center_address, c.phone as center_phone
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN users c ON r.center_id = c.id
    WHERE r.id = ?
");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Blood request not found.");
}

$req = $res->fetch_assoc();

// Determine return link
$backUrl = 'index.html';
if ($currentUser['role'] === 'user') $backUrl = 'user.php';
elseif ($currentUser['role'] === 'hospital') $backUrl = 'hospital.php';
elseif ($currentUser['role'] === 'bank') $backUrl = 'bank.php';

$hasCoordinates = ($req['latitude'] !== null && $req['longitude'] !== null && is_numeric($req['latitude']) && is_numeric($req['longitude']));
$lat = $hasCoordinates ? (float)$req['latitude'] : null;
$lng = $hasCoordinates ? (float)$req['longitude'] : null;
$directionsUrl = $hasCoordinates ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}" : "#";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Request #<?php echo $req['id']; ?> Location — BloodSync</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 24px;
            margin-top: 24px;
        }
        @media (max-width: 900px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
        .map-container {
            width: 100%;
            height: 380px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            background: #f1f5f9;
            position: relative;
        }
        .map-error-banner {
            display: none;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table tr td {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }
        .meta-table tr:last-child td {
            border-bottom: none;
        }
        .meta-table td:first-child {
            color: var(--text-secondary);
            font-weight: 500;
            width: 38%;
        }
        .meta-table td:last-child {
            font-weight: 600;
            color: var(--text);
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">
            <div class="logo">🩸</div>
            BloodSync
        </a>
        <div class="navbar-actions">
            <a href="<?php echo htmlspecialchars($backUrl); ?>" class="nav-btn nav-btn-ghost">← Back to Dashboard</a>
            <a href="logout.php" class="nav-btn nav-btn-ghost">Logout</a>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                    <h1 style="margin:0;">Request #<?php echo $req['id']; ?></h1>
                    <?php if ($req['is_urgent']): ?>
                        <span class="badge badge-urgent">EMERGENCY / URGENT</span>
                    <?php endif; ?>
                    <?php 
                        $statusClass = $req['status'] === 'pending' ? 'badge-pending' : ($req['status'] === 'completed' ? 'badge-completed' : 'badge-received');
                    ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo strtoupper($req['status']); ?></span>
                </div>
                <p style="margin:0; color:var(--text-muted);">Submitted by <?php echo htmlspecialchars($req['requester_name']); ?> (<?php echo strtoupper($req['requester_type']); ?>) on <?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($backUrl); ?>" class="btn btn-outline" style="width:auto;">← Return to Dashboard</a>
        </div>

        <div class="details-grid">
            <!-- LEFT: REQUEST SPECIFICATIONS -->
            <div class="card delay-1">
                <div class="card-header" style="display:flex; align-items:center; justify-content:space-between;">
                    <h2><span class="card-header-icon">📋</span> Request Details</h2>
                    <div class="blood-badge blood-badge-default" style="font-size:1.1rem; width:44px; height:44px;"><?php echo htmlspecialchars($req['blood_type']); ?></div>
                </div>
                <div class="card-body">
                    <table class="meta-table">
                        <tr>
                            <td>Requester</td>
                            <td><?php echo htmlspecialchars($req['requester_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Category</td>
                            <td><span class="badge" style="background:#f1f5f9; color:#475569;"><?php echo strtoupper($req['requester_type']); ?></span></td>
                        </tr>
                        <tr>
                            <td>Blood Group</td>
                            <td><strong style="color:var(--primary); font-size:1.1rem;"><?php echo htmlspecialchars($req['blood_type']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Units Needed</td>
                            <td><span class="units-pill"><?php echo (int)$req['units']; ?> Units</span></td>
                        </tr>
                        <tr>
                            <td>Phone Contact</td>
                            <td>
                                <?php if (!empty($req['phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($req['phone']); ?>" style="color:var(--text); text-decoration:none;">📞 <?php echo htmlspecialchars($req['phone']); ?></a>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $req['phone']); ?>" target="_blank" style="margin-left:6px; font-size:12px; color:#16a34a; font-weight:bold;">[WhatsApp]</a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Assigned Center</td>
                            <td>
                                <strong><?php echo htmlspecialchars($req['center_name'] ?: 'Not Assigned'); ?></strong><br>
                                <span style="font-size:0.85rem; color:var(--text-secondary);"><?php echo htmlspecialchars($req['center_address'] . ' (' . $req['center_city'] . ')'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>Target Location</td>
                            <td>
                                <?php echo htmlspecialchars($req['location_address'] ?: 'Standard Center Fulfillment'); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- RIGHT: GOOGLE MAPS LOCATION & DIRECTIONS -->
            <div class="card delay-2">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <h2><span class="card-header-icon">📍</span> Request Location</h2>
                    <?php if ($hasCoordinates): ?>
                        <a href="<?php echo htmlspecialchars($directionsUrl); ?>" target="_blank" class="btn btn-primary" style="width:auto; padding:8px 16px; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px;">
                            🗺️ Get Directions
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div id="mapErrorBanner" class="map-error-banner">
                        ⚠️ <strong>Google Maps Notice:</strong> Live map rendering is unavailable (API key not configured or offline). You can still navigate directly using the <strong>Get Directions</strong> button below.
                    </div>

                    <?php if ($hasCoordinates): ?>
                        <div id="requestMap" class="map-container">
                            <div id="mapLoader" style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text-muted);">
                                Loading Google Map...
                            </div>
                        </div>
                        <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <div style="font-size:0.85rem; color:var(--text-secondary);">
                                <strong>Coordinates:</strong> <code><?php echo number_format($lat, 6); ?>, <?php echo number_format($lng, 6); ?></code><br>
                                <strong>Place:</strong> <?php echo htmlspecialchars($req['location_address'] ?: 'Point on Map'); ?>
                            </div>
                            <a href="<?php echo htmlspecialchars($directionsUrl); ?>" target="_blank" class="btn btn-outline" style="width:auto; padding:8px 14px; font-size:0.85rem;">
                                ↗ Open in Google Maps
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:50px 20px; background:#f8fafc; border-radius:var(--radius-lg); border:1px dashed var(--border);">
                            <div style="font-size:2.5rem; margin-bottom:12px;">📍</div>
                            <h3 style="font-size:1.1rem; margin-bottom:6px;">No Exact Coordinates Attached</h3>
                            <p style="color:var(--text-muted); font-size:0.9rem; max-width:400px; margin:0 auto 16px;">This request was submitted without GPS/map pin. Fulfilled at the designated center: <strong><?php echo htmlspecialchars($req['center_name']); ?></strong> (<?php echo htmlspecialchars($req['center_city']); ?>).</p>
                            <?php if (!empty($req['center_address'])): ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($req['center_name'] . ' ' . $req['center_address'] . ' ' . $req['center_city']); ?>" target="_blank" class="btn btn-outline" style="width:auto;">
                                    🗺️ Search Center Location
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($hasCoordinates): ?>
    <script>
        // Global auth failure handler called automatically by Google Maps JS API if key fails
        window.gm_authFailure = function() {
            console.warn("Google Maps authentication failed. Showing fallback notice.");
            const banner = document.getElementById('mapErrorBanner');
            if (banner) banner.style.display = 'block';
            const loader = document.getElementById('mapLoader');
            if (loader) loader.innerHTML = '<div style="padding:20px; text-align:center; color:#64748b;">Interactive map preview unavailable.<br><small>Direct navigation link remains fully functional.</small></div>';
        };

        function initRequestMap() {
            try {
                if (!window.google || !window.google.maps) {
                    throw new Error("Google Maps API library not available");
                }

                const reqPos = { lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?> };
                const mapElement = document.getElementById('requestMap');

                const map = new google.maps.Map(mapElement, {
                    center: reqPos,
                    zoom: 15,
                    mapTypeControl: true,
                    streetViewControl: false,
                    fullscreenControl: true,
                    styles: [
                        { featureType: "poi.medical", elementType: "geometry", stylers: [{ color: "#fee2e2" }] }
                    ]
                });

                const marker = new google.maps.Marker({
                    position: reqPos,
                    map: map,
                    title: "Blood Requirement: <?php echo addslashes($req['requester_name']); ?>",
                    animation: google.maps.Animation.DROP
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding:6px; font-family:sans-serif;">
                            <strong style="color:#dc2626;"><?php echo addslashes($req['blood_type']); ?> Blood Needed</strong><br>
                            <span style="font-size:12px; color:#334155;"><?php echo addslashes($req['requester_name']); ?> · <?php echo (int)$req['units']; ?> Units</span><br>
                            <span style="font-size:11px; color:#64748b;"><?php echo addslashes($req['location_address'] ?: 'Emergency Location'); ?></span>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                // Open info window by default
                infoWindow.open(map, marker);

            } catch (err) {
                console.error("Map initialization error:", err);
                const banner = document.getElementById('mapErrorBanner');
                if (banner) banner.style.display = 'block';
            }
        }

        // Safety fallback timer if API script hangs or is blocked
        setTimeout(() => {
            const loader = document.getElementById('mapLoader');
            if (loader && (!window.google || !window.google.maps)) {
                loader.innerHTML = '<div style="padding:20px; text-align:center; color:#64748b;">Google Maps could not be loaded.<br><small>Click "Get Directions" above to navigate directly.</small></div>';
            }
        }, 3500);
    </script>
    <?php echo renderGoogleMapsScript('initRequestMap'); ?>
    <?php endif; ?>
</body>
</html>
