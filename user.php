<?php
require_once 'session_check.php';
requireLogin(['user']);
$user = getCurrentUser();
require_once 'db.php';
require_once 'config.php';
$centers = [];
$cities = [];
$res = $db->query("SELECT id, name, city FROM users WHERE role IN ('hospital', 'bank') ORDER BY city ASC, name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $centers[] = $row;
        if (!in_array($row['city'], $cities)) {
            $cities[] = $row['city'];
        }
    }
}
sort($cities);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal — BloodSync</title>
    <link rel="stylesheet" href="style.css">
    <!-- jQuery and Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Basic Select2 styling to match theme */
        .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px;
            font-family: inherit;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-search__field {
            border-radius: 4px !important;
            border: 1px solid var(--border-color) !important;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">
            <div class="logo">🩸</div>
            BloodSync
        </a>
        <div class="navbar-actions">
            <a href="centers.php" class="nav-btn nav-btn-highlight">📍 Find Centers</a>
            <span class="nav-btn" style="color: var(--text-muted); cursor: default;">👤 <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="logout.php" class="nav-btn nav-btn-ghost">Logout</a>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header">
            <h1>👤 User Portal</h1>
            <p>Donate blood to save lives or submit a request for blood you need.</p>
        </div>

        <!-- LIVE APPROVAL & SMS NOTIFICATION BANNER -->
        <div id="liveApprovalBanner" style="display:none; margin-bottom: 24px;"></div>

        <div class="grid-2" style="align-items: start;">
            <!-- LEFT COLUMN -->
            <div style="display: flex; flex-direction: column; gap: 28px;">
                <!-- DONATE BLOOD -->
                <div class="card delay-1" style="height: fit-content;">
                <div class="card-header">
                    <h2><span class="card-header-icon">💉</span> Donate Blood</h2>
                </div>
                <div class="card-body" style="max-height: none; overflow: visible;">
                    <form method="POST" action="donate.php">
                        <input type="hidden" name="redirect" value="user.php">
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Blood Type</label>
                            <select name="blood_type" class="form-select" required>
                                <option value="">Select Blood Type</option>
                                <option value="O+">O+</option><option value="A+">A+</option>
                                <option value="B+">B+</option><option value="AB+">AB+</option>
                                <option value="O-">O-</option><option value="A-">A-</option>
                                <option value="B-">B-</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Units to Donate</label>
                            <input type="number" name="units" class="form-input" placeholder="Number of units" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Center</label>
                            <select name="center_id" class="form-select searchable-select" required>
                                <option value="">Choose a center...</option>
                                <?php foreach($centers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (' . $c['city'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">✅ Submit Donation</button>
                    </form>
                </div>
            </div>

            <!-- MY DONATIONS -->
            <div class="card delay-3">
                <div class="card-header"><h2><span class="card-header-icon">💉</span> My Donations</h2></div>
                <div class="card-body"><div id="my-donations" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>

            <!-- MY REQUESTS -->
            <div class="card delay-4">
                <div class="card-header"><h2><span class="card-header-icon">📋</span> My Requests</h2></div>
                <div class="card-body"><div id="my-requests" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>
            
        </div> <!-- End of Left Column -->

        <!-- RIGHT COLUMN -->
        <div style="display: flex; flex-direction: column; gap: 28px;">
            <!-- REQUEST BLOOD -->
            <div class="card delay-2">
                <div class="card-header">
                    <h2><span class="card-header-icon">📋</span> Request Blood</h2>
                </div>
                <div class="card-body" style="max-height: none; overflow: visible;">
                    <div id="availabilityForm">
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" id="req_name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" id="req_phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select City</label>
                            <select id="req_city" class="form-select searchable-select" required>
                                <option value="">Choose a city...</option>
                                <?php foreach($cities as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Blood Type Needed</label>
                            <select id="req_blood" class="form-select" required>
                                <option value="">Select Blood Type</option>
                                <option value="O+">O+</option><option value="A+">A+</option>
                                <option value="B+">B+</option><option value="AB+">AB+</option>
                                <option value="O-">O-</option><option value="A-">A-</option>
                                <option value="B-">B-</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Units Needed</label>
                            <input type="number" id="req_units" class="form-input" placeholder="Number of units" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="urgent-toggle">
                                <input type="checkbox" id="req_urgent" value="1">
                                <span>⚠ Emergency / Urgent Request</span>
                            </label>
                        </div>

                        <!-- REQUEST LOCATION (GOOGLE MAPS) -->
                        <div class="form-group" style="margin-top:16px; border-top:1px solid var(--border); padding-top:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px;">
                                <label class="form-label" style="margin-bottom:0;">📍 Emergency Location</label>
                                <button type="button" class="btn btn-sm btn-outline" id="btnUserCurrentLoc" onclick="getUserCurrentLocation()" style="width:auto; padding:5px 12px; font-size:11px; display:inline-flex; align-items:center; gap:4px; font-weight:600;">
                                    🎯 Use My Current Location
                                </button>
                            </div>
                            <div style="margin-bottom:8px;">
                                <input type="text" id="user_loc_search" class="form-input" placeholder="🔍 Search hospital, area, landmark, or address in Gujarat...">
                            </div>
                            <div id="userMapContainer" style="width:100%; height:250px; border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; position:relative; background:#f1f5f9;">
                                <div id="userMapLoader" style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text-muted); font-size:0.85rem; padding:12px; text-align:center;">
                                    Loading Live Google Map...
                                </div>
                            </div>
                            <div id="userLocNotice" style="display:none; font-size:11px; padding:8px 12px; border-radius:6px; margin-top:8px; line-height:1.4;"></div>
                            <div style="display:flex; gap:8px; margin-top:8px;">
                                <input type="text" id="req_loc_addr" class="form-input" placeholder="No location pinned yet. Click map or use button." style="font-size:12px; background:#f8fafc;" readonly>
                                <input type="hidden" id="req_lat" value="">
                                <input type="hidden" id="req_lng" value="">
                            </div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                💡 Click anywhere on map, drag the red pin, or click <strong>Use My Current Location</strong> to detect nearby blood banks.
                            </div>

                            <!-- NEARBY BLOOD BANKS CONTAINER -->
                            <div id="nearbyBanksSection" style="display:none; margin-top:12px; padding:12px; background:#f8fafc; border:1px solid var(--border); border-radius:var(--radius);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                    <span style="font-size:12px; font-weight:700; color:#0f172a; display:inline-flex; align-items:center; gap:4px;">
                                        🏥 Nearby Available Blood Banks
                                    </span>
                                    <span id="nearbyCountBadge" class="badge" style="background:#e0f2fe; color:#0369a1; font-size:10px; font-weight:600;">0 Centers</span>
                                </div>
                                <div id="nearbyBanksList" style="max-height:180px; overflow-y:auto; display:flex; flex-direction:column; gap:6px; padding-right:2px;">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline" onclick="searchAvailability()">🔍 Search Availability</button>
                    </div>

                    <div id="centersResult" style="display:none; margin-top:20px; padding-top:20px; border-top:1px solid var(--border);">
                        <h3 style="font-size:0.9rem; margin-bottom:12px; color:var(--text-secondary); text-transform:uppercase;">Available Centers</h3>
                        <div class="form-group">
                            <select id="req_center" class="form-select" style="width:100%;"></select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="submitRequestForm()">📋 Submit Request</button>
                    </div>
                </div>
            </div>

        </div> <!-- End of Right Column -->
        </div> <!-- End of grid-2 -->

    <!-- Receiver SMS & Email Details Modal -->
    <div id="userNotifModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:1001; align-items:center; justify-content:center;">
        <div class="card animate-in" style="width: 540px; max-width: 92%; background: var(--bg); margin:auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding: 16px 24px; border-bottom:1px solid var(--border);">
                <h2 style="margin:0; font-size:1.15rem; color:#15803d; display:flex; align-items:center; gap:8px;">
                    <span>📱</span> Official Approval Notice (SMS & Email)
                </h2>
                <button onclick="closeUserNotifModal()" style="background:none; border:none; cursor:pointer; font-size:1.5rem; color:var(--text-muted);">&times;</button>
            </div>
            <div class="card-body" style="padding: 20px 24px;">
                <div id="userNotifModalBody">
                    <!-- Populated dynamically via JS -->
                </div>
                <div style="margin-top: 20px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-outline" style="width:auto; padding:8px 18px;" onclick="closeUserNotifModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dismiss banner permanently function
        window.dismissBanner = function(reqId) {
            localStorage.setItem('banner_dismissed_req_' + reqId, 'true');
            let banner = document.getElementById('liveApprovalBanner');
            if (banner) banner.style.display = 'none';
        };

        $(document).ready(function() {
            $('.searchable-select').select2({
                placeholder: "Choose an option...",
                width: '100%'
            });
        });

        function searchAvailability() {
            let city = document.getElementById('req_city').value;
            let blood = document.getElementById('req_blood').value;
            let units = document.getElementById('req_units').value;
            let lat = document.getElementById('req_lat').value;
            let lng = document.getElementById('req_lng').value;
            
            if(!blood || !units) {
                alert("Please select Blood Type Needed and Units Needed.");
                return;
            }
            if(!city && (!lat || !lng)) {
                alert("Please either select a City or pin your emergency location on the Google Map.");
                return;
            }
            
            let queryParams = `blood_type=${encodeURIComponent(blood)}&units=${units}`;
            if(city) queryParams += `&city=${encodeURIComponent(city)}`;
            if(lat && lng) queryParams += `&lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;
            
            fetch(`check_availability.php?${queryParams}`)
            .then(r=>r.json())
            .then(data => {
                let centerSelect = $('#req_center');
                centerSelect.empty();
                
                if(data.length === 0) {
                    let locDesc = city ? city : (lat ? "your pinned location" : "the selected area");
                    alert("No centers near " + locDesc + " currently have " + units + " units of " + blood + " (or medically compatible groups).");
                    document.getElementById('centersResult').style.display = 'none';
                    return;
                }
                
                centerSelect.append(new Option("Choose a center...", ""));
                data.forEach(c => {
                    let typeTag = c.is_exact_match ? `[Exact Match: ${c.available_blood_type}]` : `[Compatible: ${c.available_blood_type}]`;
                    let distText = (c.distance_km !== null && c.distance_km !== undefined) ? ` · 📍 ${c.distance_km} km (${c.city})` : ` · (${c.city})`;
                    let optText = `${c.name} — ${c.units} units (${typeTag})${distText}`;
                    let opt = new Option(optText, c.id);
                    $(opt).attr('data-blood', c.available_blood_type);
                    centerSelect.append(opt);
                });
                
                centerSelect.select2({width: '100%'});
                document.getElementById('centersResult').style.display = 'block';
                if (lat && lng) {
                    fetchNearbyBloodBanks(parseFloat(lat), parseFloat(lng));
                }
            });
        }
        
        function submitRequestForm() {
            let centerId = document.getElementById('req_center').value;
            if(!centerId) { alert("Please choose a center from the available list."); return; }
            
            let selectedOption = $('#req_center').find(':selected');
            let matchedBlood = selectedOption.attr('data-blood') || document.getElementById('req_blood').value;
            
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'request.php';
            
            let fields = {
                'requester_type': 'user',
                'requester_name': document.getElementById('req_name').value,
                'phone': document.getElementById('req_phone').value,
                'center_id': centerId,
                'blood_type': matchedBlood,
                'units': document.getElementById('req_units').value,
            };
            
            let latVal = document.getElementById('req_lat').value;
            let lngVal = document.getElementById('req_lng').value;
            let addrVal = document.getElementById('req_loc_addr').value;
            if(latVal && lngVal) {
                fields['latitude'] = latVal;
                fields['longitude'] = lngVal;
                fields['location_address'] = addrVal;
            }
            
            for(let key in fields) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
            
            if(document.getElementById('req_urgent').checked) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'is_urgent';
                input.value = '1';
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        let hasNotifiedUser = false;

        function loadMyData() {
            fetch('get_my_donations.php').then(r=>r.json()).then(data => {
                let html = '';
                data.forEach(d => {
                    let statusBadge = d.status === 'pending' ? '<span class="badge badge-pending">Pending Visit</span>' : '<span class="badge badge-success">Donated</span>';
                    let certBtn = d.status === 'completed' ? `<a href="certificate.php?id=${d.id}" target="_blank" class="btn btn-outline btn-sm" style="margin-left:8px; padding:4px 8px; font-size:12px; background:white; color:#1f2937; border-color:#d1d5db; text-decoration:none;">🎖️ Certificate</a>` : '';
                    html += `<div class="list-item"><div class="list-item-left"><div class="blood-badge blood-badge-default">${d.blood_type}</div><div class="list-item-info"><h4>${d.blood_type} at ${d.center_name}</h4><p class="timestamp">${d.created_at}</p></div></div><div class="list-item-right"><span class="units-pill">${d.units} units</span>${statusBadge}${certBtn}</div></div>`;
                });
                document.getElementById('my-donations').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No donations yet. Be the first!</p>';
            });

            fetch('get_requests.php?scope=mine').then(r=>r.json()).then(data => {
                let html = '';
                let latestReq = data.length > 0 ? data[0] : null;

                // Show banner according to actual latest request status
                const banner = document.getElementById('liveApprovalBanner');
                if (latestReq && latestReq.status === 'pending') {
                    banner.innerHTML = `
                        <div style="background:#fffbeb; border:2px solid #fcd34d; border-radius:12px; padding:18px 22px; box-shadow:0 6px 12px -2px rgba(180, 83, 9, 0.08);">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px;">
                                <div>
                                    <div style="display:inline-flex; align-items:center; gap:6px; background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">
                                        <span>⏳</span> Awaiting Blood Bank Approval
                                    </div>
                                    <h3 style="margin:0 0 6px 0; color:#78350f; font-size:1.15rem;">
                                        Request #${latestReq.id} (${latestReq.units} Units of ${latestReq.blood_type}) is Pending Review
                                    </h3>
                                    <p style="margin:0 0 6px 0; font-size:13px; color:#451a03;">
                                        Destination Center: <strong>${latestReq.center_name || 'Assigned Blood Bank'}</strong> (${latestReq.center_city || ''})
                                    </p>
                                    <div style="font-size:12px; color:#92400e; line-height:1.4;">
                                        ℹ️ <strong>Approval Workflow:</strong> The blood bank operator must verify medical stock and manually click <strong>[Approve]</strong> in their dashboard before your request is approved and your official SMS/Email notification is dispatched.
                                    </div>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:8px; align-self:center;">
                                    <span class="badge badge-pending" style="font-size:12px; padding:6px 14px; font-weight:700;">STATUS: PENDING</span>
                                    <a href="request_details.php?id=${latestReq.id}" target="_blank" class="btn btn-outline btn-sm" style="width:auto; padding:6px 14px; font-size:11px; background:white;">
                                        🗺️ View Location Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    banner.style.display = 'block';
                } else if (latestReq && latestReq.status === 'completed') {
                    let dismissedKey = 'banner_dismissed_req_' + latestReq.id;
                    if (localStorage.getItem(dismissedKey)) {
                        banner.style.display = 'none';
                    } else {
                        banner.innerHTML = `
                            <div style="background:#f0fdf4; border:2px solid #86efac; border-radius:12px; padding:18px 22px; position:relative; box-shadow:0 6px 12px -2px rgba(22, 101, 52, 0.08);">
                                <button type="button" onclick="dismissBanner(${latestReq.id})" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:1.4rem; color:#166534; cursor:pointer;" title="Dismiss">&times;</button>
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px; padding-right:24px;">
                                    <div>
                                        <div style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#166534; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">
                                            <span>✅</span> Request Approved & Allocated · Ready for Pickup
                                        </div>
                                        <h3 style="margin:0 0 6px 0; color:#14532d; font-size:1.15rem;">
                                            🎉 Blood Request #${latestReq.id} is APPROVED (${latestReq.units} Units of ${latestReq.blood_type})
                                        </h3>
                                        <p style="margin:0 0 6px 0; font-size:13px; color:#334155;">
                                            Approved Blood Bank: <strong>${latestReq.center_name || 'Designated Regional Center'}</strong> (${latestReq.center_city || ''})
                                        </p>
                                        <div style="font-size:12px; color:#475569; display:flex; flex-wrap:wrap; gap:12px;">
                                            <span>📍 <strong>Pickup Counter:</strong> ${latestReq.center_address || latestReq.center_city}</span>
                                            <span>📞 <strong>Center Phone:</strong> <a href="tel:${latestReq.center_phone}" style="color:#2563eb; font-weight:700;">${latestReq.center_phone}</a></span>
                                        </div>
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:8px; align-self:center;">
                                        <a href="request_details.php?id=${latestReq.id}" target="_blank" class="btn btn-success" style="width:auto; padding:8px 16px; text-decoration:none; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                                            🗺️ View Pickup Map & Directions
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        banner.style.display = 'block';
                    }

                    // Web browser notification
                    let notifKey = 'notified_req_' + latestReq.id;
                    if (!localStorage.getItem(notifKey) && "Notification" in window && Notification.permission === "granted") {
                        new Notification("BloodSync: Blood Request Approved! 🩸", {
                            body: `Your request #${latestReq.id} for ${latestReq.units} units was approved by ${latestReq.center_name}. Ready for collection!`,
                            icon: 'banner.png'
                        });
                        localStorage.setItem(notifKey, 'true');
                    }
                } else if (banner) {
                    banner.style.display = 'none';
                }

                data.forEach(req => {
                    let isComp = (req.status === 'completed');
                    let isPending = (req.status === 'pending');
                    let badgeClass = isPending ? 'badge-pending' : (isComp ? 'badge-completed' : 'badge-received');
                    let statusLabel = isPending ? 'PENDING APPROVAL' : (isComp ? 'APPROVED · READY' : req.status);

                    let locBadge = (req.latitude && req.longitude) 
                        ? `<a href="request_details.php?id=${req.id}" target="_blank" class="badge" style="background:#e0f2fe; color:#0369a1; text-decoration:none; margin-left:6px; font-weight:600;" title="${req.location_address || 'View on Map'}">📍 Map</a>` 
                        : '';
                    
                    let notifBtn = isComp ? `<button type="button" class="btn btn-outline btn-sm" style="width:auto; padding:3px 8px; font-size:11px; margin-left:6px; background:white;" onclick="openUserNotifDetails(${req.id})">SMS Notice</button>` : '';

                    let centerInfo = '';
                    if (isComp && req.center_name) {
                        centerInfo = `<p class="timestamp" style="color:#15803d; font-weight:600; margin-top:2px;">🏥 Approved by: ${req.center_name} (📞 <a href="tel:${req.center_phone}" style="color:#2563eb;">${req.center_phone}</a>) · ${req.created_at}</p>`;
                    } else if (isPending && req.center_name) {
                        centerInfo = `<p class="timestamp" style="color:#92400e; font-weight:500; margin-top:2px;">⏳ Sent to: ${req.center_name} (${req.center_city || 'Regional'}) · Awaiting approval · ${req.created_at}</p>`;
                    } else {
                        centerInfo = `<p class="timestamp">${req.created_at}</p>`;
                    }

                    html += `
                        <div class="list-item ${req.is_urgent==1?'urgent-item':''}" style="${isComp?'border-left: 4px solid #22c55e;':''}">
                            <div class="list-item-left">
                                <div class="blood-badge blood-badge-default">${req.blood_type}</div>
                                <div class="list-item-info">
                                    <h4>${req.blood_type} ${req.is_urgent==1?'<span class="badge badge-urgent">URGENT</span>':''}</h4>
                                    ${centerInfo}
                                </div>
                            </div>
                            <div class="list-item-right">
                                <span class="units-pill">${req.units} units</span>
                                <span class="badge ${badgeClass}">${statusLabel}</span>
                                ${locBadge}
                                ${notifBtn}
                            </div>
                        </div>
                    `;
                });
                document.getElementById('my-requests').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No requests yet.</p>';
            });
        }
        loadMyData();
        setInterval(loadMyData, 4000);

        // Request browser notification permission once
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }

        function openUserNotifDetails(reqId) {
            fetch('get_my_notifications.php')
                .then(r => r.json())
                .then(list => {
                    let items = list.filter(n => parseInt(n.request_id) === parseInt(reqId));
                    let body = document.getElementById('userNotifModalBody');

                    if (!items || items.length === 0) {
                        body.innerHTML = `
                            <div style="padding:15px; text-align:center; color:#64748b;">
                                Notification record is being processed. Please check back in a moment.
                            </div>
                        `;
                    } else {
                        let html = `
                            <div style="margin-bottom:14px; font-size:13px; color:#334155; line-height:1.5;">
                                Below are the official automated emergency notices dispatched to your phone number and email upon blood bank approval:
                            </div>
                        `;

                        items.forEach(n => {
                            if (n.type === 'sms') {
                                html += `
                                    <div style="margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                            <span style="font-size:12px; font-weight:700; color:#0f172a; display:inline-flex; align-items:center; gap:4px;">
                                                📱 Mobile SMS Alert (Sent to: ${n.recipient_phone})
                                            </span>
                                            <span class="badge" style="background:#dcfce7; color:#166534; font-size:10px;">${n.status.toUpperCase()}</span>
                                        </div>
                                        <div style="background:white; border:1px solid #cbd5e1; border-radius:6px; padding:10px; font-size:12px; color:#1e293b; font-family:monospace; line-height:1.4;">
                                            "${n.message}"
                                        </div>
                                        <div style="font-size:10px; color:#64748b; margin-top:4px;">${n.status === 'sent' ? 'Dispatched on ' + n.formatted_date : 'Notice generated & recorded on ' + n.formatted_date}</div>
                                    </div>
                                `;
                            } else if (n.type === 'email') {
                                let badgeColor = (n.status === 'sent') ? 'background:#dcfce7; color:#166534;' : 'background:#f1f5f9; color:#475569;';
                                let badgeText = (n.status === 'sent') ? 'DISPATCHED' : 'LOGGED';
                                html += `
                                    <div style="margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                            <span style="font-size:12px; font-weight:700; color:#166534; display:inline-flex; align-items:center; gap:4px;">
                                                📧 Email Notice (To: ${n.recipient_email})
                                            </span>
                                            <span class="badge" style="${badgeColor} font-size:10px;">${badgeText}</span>
                                        </div>
                                        <div style="font-size:12px; color:#14532d; line-height:1.4;">
                                            <strong>Subject:</strong> ${n.subject}<br>
                                            <span style="color:#334155; display:inline-block; margin-top:4px;">${n.message}</span>
                                        </div>
                                        <div style="font-size:10px; color:#16a34a; margin-top:4px;">${n.status === 'sent' ? 'Dispatched to inbox on ' + n.formatted_date : 'Notice generated & recorded on ' + n.formatted_date}</div>
                                    </div>
                                `;
                            }
                        });

                        html += `
                            <div style="background:#eff6ff; border-left:4px solid #3b82f6; padding:10px 14px; font-size:12px; color:#1e40af; border-radius:0 6px 6px 0;">
                                <strong>Collection Reminder:</strong> Please present valid hospital ID or patient blood requisition slip when visiting the blood bank collection counter.
                            </div>
                        `;

                        body.innerHTML = html;
                    }

                    document.getElementById('userNotifModal').style.display = 'flex';
                })
                .catch(err => {
                    console.error("Error loading notification details:", err);
                    alert("Could not load notification details.");
                });
        }

        function closeUserNotifModal() {
            document.getElementById('userNotifModal').style.display = 'none';
        }

        // Google Maps Integration for User Portal
        let userMap = null;
        let userMarker = null;
        let bankMarkers = [];
        let activeInfoWindow = null;
        const GUJARAT_CENTER = { lat: 22.8, lng: 71.8 }; // Neutral state overview

        window.gm_authFailure = function() {
            console.warn("Google Maps Authentication Failed on User Portal.");
            showUserLocNotice("⚠️ Google Maps key issue. You can still submit requests with standard city selection.", "#fef2f2", "#991b1b");
            const loader = document.getElementById('userMapLoader');
            if (loader) loader.innerHTML = '<div style="padding:15px; color:#64748b;">Map preview unavailable.<br><small>Location selection via city remains active.</small></div>';
        };

        function showUserLocNotice(msg, bg, color) {
            const notice = document.getElementById('userLocNotice');
            if (notice) {
                notice.style.display = 'block';
                notice.style.background = bg;
                notice.style.color = color;
                notice.innerHTML = msg;
            }
        }

        function setLocationFields(lat, lng, address) {
            document.getElementById('req_lat').value = lat.toFixed(6);
            document.getElementById('req_lng').value = lng.toFixed(6);
            if (address) {
                document.getElementById('req_loc_addr').value = address;
            } else {
                document.getElementById('req_loc_addr').value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            }
        }

        function initUserMap() {
            try {
                if (!window.google || !window.google.maps) {
                    throw new Error("Google Maps JS library not loaded");
                }

                const mapDiv = document.getElementById('userMapContainer');
                userMap = new google.maps.Map(mapDiv, {
                    center: GUJARAT_CENTER,
                    zoom: 7, // Neutral zoom over Gujarat state
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false
                });

                // NOTE: DO NOT pre-fill or set fake default coordinates!
                // Inputs #req_lat and #req_lng remain empty until user interacts.

                // Map Click Event: Pinpoint emergency spot
                userMap.addListener('click', function(e) {
                    const pos = e.latLng;
                    applyRealLocation(pos.lat(), pos.lng(), "Map Pinned Spot");
                });

                // Places Autocomplete
                const searchInput = document.getElementById('user_loc_search');
                if (window.google.maps.places) {
                    const autocomplete = new google.maps.places.Autocomplete(searchInput);
                    autocomplete.bindTo('bounds', userMap);
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) {
                            showUserLocNotice("⚠️ No geometry details found for selected place. Please click directly on the map.", "#fff7ed", "#c2410c");
                            return;
                        }

                        const pLat = place.geometry.location.lat();
                        const pLng = place.geometry.location.lng();
                        applyRealLocation(pLat, pLng, place.formatted_address || place.name);
                        showUserLocNotice("📍 Selected location: <strong>" + (place.name || place.formatted_address) + "</strong>", "#f0fdf4", "#15803d");
                    });
                }

            } catch (e) {
                console.error("initUserMap error:", e);
                const loader = document.getElementById('userMapLoader');
                if (loader) loader.innerHTML = '<div style="padding:15px; color:#64748b;">Google Maps preview unavailable. Requests can still be submitted normally.</div>';
            }
        }

        function applyRealLocation(lat, lng, sourceLabel) {
            setLocationFields(lat, lng, sourceLabel);

            if (userMap) {
                if (!userMarker) {
                    userMarker = new google.maps.Marker({
                        position: { lat, lng },
                        map: userMap,
                        draggable: true,
                        title: "Your Emergency Location (Drag to adjust)",
                        animation: google.maps.Animation.DROP
                    });

                    userMarker.addListener('dragend', function(e) {
                        const pos = e.latLng;
                        applyRealLocation(pos.lat(), pos.lng(), "Adjusted Spot");
                    });
                } else {
                    userMarker.setPosition({ lat, lng });
                }

                userMap.panTo({ lat, lng });
                if (userMap.getZoom() < 13) {
                    userMap.setZoom(14);
                }
            }

            // Reverse geocode to get actual street address and detect city
            reverseGeocode(lat, lng);

            // Fetch and display nearby blood banks around this exact location
            fetchNearbyBloodBanks(lat, lng);
        }

        function reverseGeocode(lat, lng) {
            if (window.google && window.google.maps && window.google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === 'OK' && results && results[0]) {
                        const addr = results[0].formatted_address;
                        setLocationFields(lat, lng, addr);

                        // Try to auto-select matching city in dropdown
                        results[0].address_components.forEach(comp => {
                            let compName = comp.long_name.toLowerCase();
                            $('#req_city option').each(function() {
                                if ($(this).val() && compName.includes($(this).val().toLowerCase())) {
                                    $('#req_city').val($(this).val()).trigger('change');
                                }
                            });
                        });
                    }
                });
            }
        }

        function getUserCurrentLocation() {
            const btn = document.getElementById('btnUserCurrentLoc');
            btn.innerHTML = "⌛ Detecting Live Location...";
            btn.disabled = true;

            if (!navigator.geolocation) {
                fallbackToIpLocation(btn, "Browser Geolocation is not supported by your device.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    btn.innerHTML = "🎯 Use My Current Location";
                    btn.disabled = false;

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const acc = Math.round(position.coords.accuracy || 0);

                    applyRealLocation(lat, lng, `Device Location (~${acc}m accuracy)`);
                    showUserLocNotice(`🎯 <strong>Actual device location detected!</strong> Accuracy: ~${acc}m. Showing nearby blood banks below.`, "#f0fdf4", "#15803d");
                },
                function(err) {
                    console.warn("GPS failed or timed out. Falling back to IP geolocation...", err);
                    let reason = "GPS permission denied or unavailable.";
                    if (err.code === 1) reason = "Location permission was denied.";
                    else if (err.code === 3) reason = "Location detection timed out.";
                    fallbackToIpLocation(btn, reason);
                },
                { enableHighAccuracy: true, timeout: 9000, maximumAge: 0 }
            );
        }

        function fallbackToIpLocation(btn, reason) {
            btn.innerHTML = "⌛ Checking Network Location...";

            fetch('https://ipapi.co/json/')
                .then(r => r.json())
                .then(data => {
                    if (data && data.latitude && data.longitude) {
                        const lat = parseFloat(data.latitude);
                        const lng = parseFloat(data.longitude);
                        const city = data.city || '';
                        const region = data.region || 'Gujarat';

                        applyRealLocation(lat, lng, `Network Location (${city ? city + ', ' : ''}${region})`);
                        showUserLocNotice(`🌐 <strong>Detected live location via Network IP:</strong> ${city || region} (GPS note: ${reason}). Nearby blood banks loaded.`, "#f0fdf4", "#15803d");
                    } else {
                        throw new Error("No coordinates in IP response");
                    }
                })
                .catch(e => {
                    console.warn("IP Geolocation fallback failed:", e);
                    showUserLocNotice(`⚠️ ${reason} Please search your area or click on the map directly.`, "#fff7ed", "#c2410c");
                })
                .finally(() => {
                    btn.innerHTML = "🎯 Use My Current Location";
                    btn.disabled = false;
                });
        }

        function fetchNearbyBloodBanks(lat, lng) {
            let blood = document.getElementById('req_blood').value || '';
            let units = document.getElementById('req_units').value || 0;

            let url = `get_nearby_centers.php?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;
            if (blood) url += `&blood_type=${encodeURIComponent(blood)}`;
            if (units) url += `&units=${units}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data.centers || data.centers.length === 0) {
                        document.getElementById('nearbyBanksSection').style.display = 'none';
                        return;
                    }

                    // Clear old blood bank markers
                    bankMarkers.forEach(m => m.setMap(null));
                    bankMarkers = [];

                    let listHtml = '';
                    let count = data.centers.length;
                    document.getElementById('nearbyCountBadge').innerText = `${count} Centers Found`;

                    data.centers.forEach(c => {
                        // Place marker on map
                        if (userMap && window.google && window.google.maps) {
                            const bMarker = new google.maps.Marker({
                                position: { lat: c.latitude, lng: c.longitude },
                                map: userMap,
                                title: `${c.name} (${c.distance_km} km away)`,
                                icon: {
                                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                                }
                            });

                            const infoContent = `
                                <div style="font-size:12px; line-height:1.4; color:#0f172a; max-width:240px; padding:4px;">
                                    <strong style="color:#b91c1c; font-size:13px;">${c.name}</strong><br>
                                    <span style="color:#0284c7; font-weight:700;">📍 ${c.distance_km} km away</span> (${c.city})<br>
                                    <span style="color:#64748b; font-size:11px;">${c.address}</span><br>
                                    📞 <a href="tel:${c.phone}" style="color:#2563eb; text-decoration:none; font-weight:600;">${c.phone}</a><br>
                                    <span style="font-size:11px; color:#16a34a; font-weight:600;">🩸 Total Stock: ${c.total_units} units</span><br>
                                    <button type="button" onclick="selectNearbyCenter(${c.id}, '${escape(c.name)}', '${c.city}', '${c.matched_blood_type || ''}')" style="margin-top:6px; background:#dc2626; color:white; border:none; border-radius:4px; padding:4px 10px; cursor:pointer; font-size:11px; font-weight:600;">Select this Center</button>
                                </div>
                            `;

                            bMarker.addListener('click', function() {
                                if (activeInfoWindow) activeInfoWindow.close();
                                activeInfoWindow = new google.maps.InfoWindow({ content: infoContent });
                                activeInfoWindow.open(userMap, bMarker);
                            });

                            bankMarkers.push(bMarker);
                        }

                        // Build card in list
                        let stockBadge = c.has_stock 
                            ? `<span style="color:#16a34a; font-weight:600;">🩸 ${c.total_units} units available</span>`
                            : `<span style="color:#dc2626; font-weight:600;">Low stock</span>`;

                        listHtml += `
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; background:white; border:1px solid #e2e8f0; border-radius:6px; font-size:12px;">
                                <div>
                                    <div style="font-weight:600; color:#0f172a;">${c.name}</div>
                                    <div style="color:#64748b; font-size:11px;">
                                        📍 <strong style="color:#0284c7;">${c.distance_km} km away</strong> · ${c.city} · 📞 ${c.phone}
                                    </div>
                                    <div style="font-size:11px; margin-top:2px;">${stockBadge}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" style="width:auto; padding:4px 10px; font-size:11px; font-weight:600; white-space:nowrap;" onclick="selectNearbyCenter(${c.id}, '${escape(c.name)}', '${c.city}', '${c.matched_blood_type || ''}')">
                                    Select
                                </button>
                            </div>
                        `;
                    });

                    document.getElementById('nearbyBanksList').innerHTML = listHtml;
                    document.getElementById('nearbyBanksSection').style.display = 'block';
                })
                .catch(err => {
                    console.error("Error fetching nearby blood banks:", err);
                });
        }

        function selectNearbyCenter(centerId, centerName, city, matchedBlood) {
            let centerSelect = $('#req_center');
            let decodedName = unescape(centerName);

            // If options exist, find or append
            if ($('#req_center option[value="' + centerId + '"]').length === 0) {
                let opt = new Option(`${decodedName} (${city})`, centerId, true, true);
                if (matchedBlood) $(opt).attr('data-blood', matchedBlood);
                centerSelect.append(opt);
            } else {
                centerSelect.val(centerId);
            }

            centerSelect.select2({width: '100%'});
            document.getElementById('centersResult').style.display = 'block';

            // Auto-select city if dropdown was blank
            if (!$('#req_city').val() && city) {
                $('#req_city').val(city).trigger('change');
            }

            // Smooth scroll to centersResult
            document.getElementById('centersResult').scrollIntoView({ behavior: 'smooth' });
            showUserLocNotice(`✅ Selected: <strong>${decodedName}</strong>. Ready to submit blood request.`, "#f0fdf4", "#15803d");
        }

        // Fallback timeout
        setTimeout(() => {
            const loader = document.getElementById('userMapLoader');
            if (loader && (!window.google || !window.google.maps)) {
                loader.innerHTML = '<div style="padding:15px; color:#64748b;">Google Maps not loaded. You can still submit requests with standard city selection.</div>';
            }
        }, 3500);

        fetchMyRequests();

        fetchMyRequests();

    </script>
    <?php echo renderGoogleMapsScript('initUserMap'); ?>
</body>
</html>
