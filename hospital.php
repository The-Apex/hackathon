<?php
require_once 'session_check.php';
requireLogin(['hospital']);
$user = getCurrentUser();
require_once 'db.php';
require_once 'config.php';
$banks = [];
$res = $db->query("SELECT id, name, city FROM users WHERE role = 'bank' ORDER BY city ASC, name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $banks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Portal — BloodSync</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">
            <div class="logo">🩸</div>
            BloodSync
        </a>
        <div class="navbar-actions">
            <a href="centers.php" class="nav-btn nav-btn-highlight">📍 Find Centers</a>
            <span class="nav-btn" style="color: var(--text-muted); cursor: default;">🚑 <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="logout.php" class="nav-btn nav-btn-ghost">Logout</a>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header">
            <h1>🚑 Hospital Portal</h1>
            <p>Submit blood supply requests for <?php echo htmlspecialchars($user['name']); ?> and track their status.</p>
        </div>

        <div class="grid-2">
            <div class="card delay-1">
                <div class="card-header">
                    <h2><span class="card-header-icon">🚑</span> Request Blood Supply</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="request.php">
                        <input type="hidden" name="requester_type" value="hospital">
                        <div class="form-group">
                            <label class="form-label">Hospital Name</label>
                            <input type="text" name="requester_name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Blood Type Needed</label>
                            <select name="blood_type" class="form-select" required>
                                <option value="">Select Blood Type</option>
                                <option value="O+">O+</option><option value="A+">A+</option>
                                <option value="B+">B+</option><option value="AB+">AB+</option>
                                <option value="O-">O-</option><option value="A-">A-</option>
                                <option value="B-">B-</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Units Needed</label>
                            <input type="number" name="units" class="form-input" placeholder="Number of units" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Target Blood Bank</label>
                            <select name="center_id" class="form-select" required>
                                <option value="">Choose a blood bank...</option>
                                <?php foreach($banks as $b): ?>
                                    <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name'] . ' (' . $b['city'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="location_address" id="hosp_loc_addr" value="">
                        <input type="hidden" name="latitude" id="hosp_lat" value="">
                        <input type="hidden" name="longitude" id="hosp_lng" value="">

                        <div class="form-group">
                            <label class="urgent-toggle">
                                <input type="checkbox" name="is_urgent" value="1">
                                <span>⚠ Emergency / Urgent Request</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">🚑 Submit Hospital Request</button>
                    </form>
                </div>
            </div>

            <div class="card delay-2">
                <div class="card-header">
                    <h2><span class="card-header-icon">⏳</span> Active Requests</h2>
                </div>
                <div class="card-body">
                    <div id="requests-active" class="list-container">
                        <p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p>
                    </div>
                </div>
            </div>

            <div class="card delay-3">
                <div class="card-header">
                    <h2><span class="card-header-icon">✅</span> Past Requests</h2>
                </div>
                <div class="card-body">
                    <div id="requests-completed" class="list-container">
                        <p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadHospitalRequests() {
            fetch('get_requests.php?scope=mine')
                .then(r => r.json())
                .then(data => {
                    let activeHtml = '';
                    let completedHtml = '';
                    data.forEach(req => {
                        let badgeClass = req.status==='pending'?'badge-pending':(req.status==='completed'?'badge-completed':'badge-received');
                        let urgentClass = req.is_urgent == 1 ? 'urgent-item' : '';
                        let markReceivedBtn = req.status === 'completed' ? `<button class="btn btn-success btn-sm" style="margin-left:8px; width:auto; padding:4px 10px; font-size:12px;" onclick="markReceived(${req.id})">✓ Mark Received</button>` : '';
                        let locBadge = (req.latitude && req.longitude) 
                            ? `<a href="request_details.php?id=${req.id}" target="_blank" class="badge" style="background:#e0f2fe; color:#0369a1; text-decoration:none; margin-left:6px; font-weight:600;" title="${req.location_address || 'View on Map'}">📍 Map</a>` 
                            : '';

                        let itemHtml = `
                            <div class="list-item ${urgentClass}">
                                <div class="list-item-left">
                                    <div class="blood-badge blood-badge-default">${req.blood_type}</div>
                                    <div class="list-item-info">
                                        <h4>${req.requester_name} ${req.is_urgent==1?'<span class="badge badge-urgent">URGENT</span>':''}</h4>
                                        <p class="timestamp">Requested: ${req.created_at}</p>
                                    </div>
                                </div>
                                <div class="list-item-right">
                                    <span class="units-pill">${req.units} units</span>
                                    <span class="badge ${badgeClass}">${req.status}</span>
                                    ${locBadge}
                                    ${markReceivedBtn}
                                </div>
                            </div>`;
                            
                        if (req.status === 'pending') {
                            activeHtml += itemHtml;
                        } else {
                            completedHtml += itemHtml;
                        }
                    });
                    document.getElementById('requests-active').innerHTML = activeHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No active requests.</p>';
                    document.getElementById('requests-completed').innerHTML = completedHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No past requests.</p>';
                });
        }

        function markReceived(id) {
            if (confirm('Confirm receipt of these blood units at your hospital?')) {
                fetch('update_request_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&status=received`
                }).then(r => r.text()).then(res => {
                    if (res.includes('Error')) alert(res);
                    else loadHospitalRequests();
                }).catch(err => alert('Network error: ' + err));
            }
        }

        loadHospitalRequests();
        setInterval(loadHospitalRequests, 3000);

        // Google Maps Integration for Hospital Portal
        let hospMap = null;
        let hospMarker = null;
        const DEFAULT_HOSP_POS = { lat: 23.0531, lng: 72.5855 }; // Default: Ahmedabad Civil Hospital region

        window.gm_authFailure = function() {
            console.warn("Google Maps Authentication Failed on Hospital Portal.");
            showHospLocNotice("⚠️ Google Maps API Key issue. Address can still be entered manually.", "#fef2f2", "#991b1b");
            const loader = document.getElementById('hospMapLoader');
            if (loader) loader.innerHTML = '<div style="padding:15px; color:#64748b;">Map preview unavailable.</div>';
        };

        function showHospLocNotice(msg, bg, color) {
            const notice = document.getElementById('hospLocNotice');
            if (notice) {
                notice.style.display = 'block';
                notice.style.background = bg;
                notice.style.color = color;
                notice.innerHTML = msg;
            }
        }

        function setHospLocationFields(lat, lng, address) {
            document.getElementById('hosp_lat').value = lat.toFixed(6);
            document.getElementById('hosp_lng').value = lng.toFixed(6);
            if (address) {
                document.getElementById('hosp_loc_addr').value = address;
            } else {
                document.getElementById('hosp_loc_addr').value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            }
        }

        function initHospMap() {
            try {
                if (!window.google || !window.google.maps) {
                    throw new Error("Google Maps JS library not loaded");
                }

                const mapDiv = document.getElementById('hospMapContainer');
                hospMap = new google.maps.Map(mapDiv, {
                    center: DEFAULT_HOSP_POS,
                    zoom: 13,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false
                });

                hospMarker = new google.maps.Marker({
                    position: DEFAULT_HOSP_POS,
                    map: hospMap,
                    draggable: true,
                    title: "Hospital Delivery Spot",
                    animation: google.maps.Animation.DROP
                });

                setHospLocationFields(DEFAULT_HOSP_POS.lat, DEFAULT_HOSP_POS.lng, "Hospital Default Pin (Drag or search)");

                // Marker Drag Event
                hospMarker.addListener('dragend', function(e) {
                    const pos = e.latLng;
                    reverseGeocodeHosp(pos.lat(), pos.lng());
                });

                // Map Click Event
                hospMap.addListener('click', function(e) {
                    const pos = e.latLng;
                    hospMarker.setPosition(pos);
                    hospMap.panTo(pos);
                    reverseGeocodeHosp(pos.lat(), pos.lng());
                });

                // Places Autocomplete
                const searchInput = document.getElementById('hosp_loc_search');
                if (window.google.maps.places) {
                    const autocomplete = new google.maps.places.Autocomplete(searchInput);
                    autocomplete.bindTo('bounds', hospMap);
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) {
                            showHospLocNotice("No location details found for this place.", "#fff7ed", "#c2410c");
                            return;
                        }

                        if (place.geometry.viewport) {
                            hospMap.fitBounds(place.geometry.viewport);
                        } else {
                            hospMap.setCenter(place.geometry.location);
                            hospMap.setZoom(15);
                        }

                        hospMarker.setPosition(place.geometry.location);
                        setHospLocationFields(place.geometry.location.lat(), place.geometry.location.lng(), place.formatted_address || place.name);
                        showHospLocNotice("📍 Hospital location selected: " + (place.name || place.formatted_address), "#f0fdf4", "#15803d");
                    });
                }

            } catch (e) {
                console.error("initHospMap error:", e);
                const loader = document.getElementById('hospMapLoader');
                if (loader) loader.innerHTML = '<div style="padding:15px; color:#64748b;">Google Maps preview unavailable.</div>';
            }
        }

        function reverseGeocodeHosp(lat, lng) {
            setHospLocationFields(lat, lng, `Pinned: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
            if (window.google && window.google.maps && window.google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        setHospLocationFields(lat, lng, results[0].formatted_address);
                        showHospLocNotice("📍 Pinned: " + results[0].formatted_address, "#f0fdf4", "#15803d");
                    }
                });
            }
        }

        function getHospCurrentLocation() {
            const btn = document.getElementById('btnHospCurrentLoc');
            if (!navigator.geolocation) {
                showHospLocNotice("❌ Browser Geolocation is not supported by this device.", "#fef2f2", "#991b1b");
                return;
            }

            btn.innerText = "⌛ Detecting...";
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    btn.innerText = "🎯 Use Current Location";
                    btn.disabled = false;
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    if (hospMap && hospMarker) {
                        hospMap.setCenter(pos);
                        hospMap.setZoom(16);
                        hospMarker.setPosition(pos);
                    }
                    reverseGeocodeHosp(pos.lat, pos.lng);
                    showHospLocNotice("✅ Detected device location.", "#f0fdf4", "#15803d");
                },
                function(err) {
                    btn.innerText = "🎯 Use Current Location";
                    btn.disabled = false;
                    let msg = "Could not obtain location.";
                    if (err.code === 1) msg = "Location permission denied.";
                    else if (err.code === 2) msg = "Position unavailable.";
                    else if (err.code === 3) msg = "Detection timed out.";
                    showHospLocNotice("⚠️ " + msg, "#fff7ed", "#c2410c");
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
            );
        }

        // Safety fallback timer
        setTimeout(() => {
            const loader = document.getElementById('hospMapLoader');
            if (loader && (!window.google || !window.google.maps)) {
                loader.innerHTML = '<div style="padding:15px; color:#64748b;">Google Maps not loaded. Form can still be submitted directly.</div>';
            }
        }, 3500);
    </script>
    <?php echo renderGoogleMapsScript('initHospMap'); ?>
</body>
</html>
