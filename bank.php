<?php
require_once 'session_check.php';
requireLogin(['bank']);
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Dashboard — BloodSync</title>
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
            <span class="nav-btn" style="color: var(--text-muted); cursor: default;">🏥 <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="export_csv.php" class="nav-btn nav-btn-success">📥 Export CSV</a>
            <a href="logout.php" class="nav-btn nav-btn-ghost">Logout</a>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header">
            <h1>🏥 Blood Bank Dashboard</h1>
            <p>Monitor inventory, manage requests, and view donor records.</p>
        </div>

        <!-- STATS -->
        <div class="stats-row delay-1">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blood">🩸</div>
                <div class="stat-info"><h4>Total Blood</h4><div class="stat-value" id="stat-blood">0</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-requests">📋</div>
                <div class="stat-info"><h4>Requests</h4><div class="stat-value" id="stat-requests">0</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-donations">💉</div>
                <div class="stat-info"><h4>Donations</h4><div class="stat-value" id="stat-donations">0</div></div>
            </div>
        </div>

        <div class="grid-2">
            <!-- INVENTORY -->
            <div class="card delay-2">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h2 style="margin:0;"><span class="card-header-icon">📦</span> Inventory</h2>
                    <button class="btn btn-sm btn-outline" onclick="openInventoryModal()">✏️ Update</button>
                </div>
                <div class="card-body"><div id="inventory" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>
            
            <!-- DONOR REQUESTS -->
            <div class="card delay-2">
                <div class="card-header"><h2><span class="card-header-icon">⏳</span> Donor Requests</h2></div>
                <div class="card-body">
                    <div class="controls-bar">
                        <input type="text" id="donorSearchBox" class="form-input" placeholder="Search donors..." onkeyup="filterDonors()">
                        <select id="donorFilterType" class="form-select" onchange="filterDonors()">
                            <option value="all">All</option>
                            <option value="O+">O+</option><option value="A+">A+</option>
                            <option value="B+">B+</option><option value="AB+">AB+</option>
                            <option value="O-">O-</option><option value="A-">A-</option>
                            <option value="B-">B-</option><option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div id="donor-requests" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div>
                </div>
            </div>

            <!-- DONORS -->
            <div class="card delay-3">
                <div class="card-header"><h2><span class="card-header-icon">💉</span> Donor Records</h2></div>
                <div class="card-body"><div id="donors" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>
            <!-- REQUESTS -->
            <div class="card delay-4">
                <div class="card-header"><h2><span class="card-header-icon">📋</span> Active Blood Requests</h2></div>
                <div class="card-body">
                    <div class="controls-bar">
                        <input type="text" id="searchBox" class="form-input" placeholder="Search..." onkeyup="filterRequests()">
                        <select id="filterType" class="form-select" onchange="filterRequests()">
                            <option value="all">All</option>
                            <option value="O+">O+</option><option value="A+">A+</option>
                            <option value="B+">B+</option><option value="AB+">AB+</option>
                            <option value="O-">O-</option><option value="A-">A-</option>
                            <option value="B-">B-</option><option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div id="requests-pending" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div>
                </div>
            </div>

            <!-- COMPLETED REQUESTS -->
            <div class="card delay-4">
                <div class="card-header"><h2><span class="card-header-icon">✅</span> Completed Requests</h2></div>
                <div class="card-body">
                    <div id="requests-completed" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Modal -->
    <div id="inventoryModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="card animate-in" style="width: 500px; max-width: 90%; background: var(--bg); margin:auto;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 24px;">
                <h2 style="margin:0;"><span class="card-header-icon" style="display:inline-flex;">✏️</span> Update Inventory</h2>
                <button onclick="closeInventoryModal()" style="background:none; border:none; cursor:pointer; font-size:1.5rem; color:var(--text-muted);">&times;</button>
            </div>
            <div class="card-body">
                <form id="inventoryForm" onsubmit="submitInventory(event)">
                    <div id="inventoryInputs" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <!-- Inputs populated via JS -->
                    </div>
                    <div style="margin-top: 24px; text-align: right;">
                        <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 24px;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Request Approval & Notification Modal -->
    <div id="approvalModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:1001; align-items:center; justify-content:center;">
        <div class="card animate-in" style="width: 520px; max-width: 92%; background: var(--bg); margin:auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding: 16px 24px; border-bottom:1px solid var(--border);">
                <h2 style="margin:0; font-size:1.15rem; color:#15803d; display:flex; align-items:center; gap:8px;">
                    <span>🎉</span> Request Approved Successfully!
                </h2>
                <button onclick="closeApprovalModal()" style="background:none; border:none; cursor:pointer; font-size:1.5rem; color:var(--text-muted);">&times;</button>
            </div>
            <div class="card-body" style="padding: 20px 24px;">
                <div id="approvalModalBody">
                    <!-- Dynamic details populated in JS -->
                </div>
                <div style="margin-top: 20px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-outline" style="width:auto; padding:8px 18px;" onclick="closeApprovalModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allRequests = [];
        let allDonors = [];
        let currentInventory = {};

        function loadData() {
            fetch('get_stats.php').then(r=>r.json()).then(d => {
                document.getElementById('stat-blood').innerText = d.total_blood + ' Units';
                document.getElementById('stat-requests').innerText = d.total_requests;
                document.getElementById('stat-donations').innerText = d.total_donations;
            });

            fetch('get_inventory.php').then(r=>r.json()).then(data => {
                currentInventory = data;
                let html = '';
                for (let type in data) {
                    let units = parseInt(data[type]);
                    let critical = units < 5;
                    html += `<div class="list-item ${critical?'critical-item':''}"><div class="list-item-left"><div class="blood-badge blood-badge-default">${type}</div><div class="list-item-info"><h4>${type} ${critical?'<span class="badge badge-critical">CRITICAL</span>':''}</h4><p>${units<5?'Low stock':'Healthy stock'}</p></div></div><span class="units-pill">${units} units</span></div>`;
                }
                document.getElementById('inventory').innerHTML = html;
            });

            fetch('get_donors.php').then(r=>r.json()).then(data => {
                allDonors = data;
                filterDonors();
            });

            fetch('get_requests.php').then(r=>r.json()).then(data => { allRequests = data; filterRequests(); });
        }

        function filterDonors() {
            let search = document.getElementById('donorSearchBox').value.toLowerCase();
            let bType = document.getElementById('donorFilterType').value;
            let filtered = allDonors.filter(d => d.name.toLowerCase().includes(search) && (bType==='all'||d.blood_type===bType));
            
            let pendingHtml = '';
            let completedHtml = '';
            
            let bankName = "<?php echo addslashes($user['name']); ?>";
            
            filtered.forEach(d => {
                let cleanPhone = d.phone ? d.phone.replace(/[^0-9]/g,'') : '';
                
                let waLogo = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width:12px; height:12px; fill:currentColor; margin-right:4px; vertical-align:middle; position:relative; top:-1px;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>`;

                if (d.status === 'pending') {
                    let msg = encodeURIComponent(`Hi ${d.name}, we received your pledge to donate blood at ${bankName}. Please visit us soon! 🩸`);
                    let waBtn = d.phone ? `<a href="https://wa.me/${cleanPhone}?text=${msg}" target="_blank" style="display:inline-flex; align-items:center; background-color:#25D366; color:white; border-radius:12px; padding:2px 8px; text-decoration:none; font-size:10px; font-weight:bold;">${waLogo}WhatsApp</a>` : '';
                    let phoneText = d.phone ? `<span style="white-space:nowrap; color:var(--text-secondary);">📱 ${d.phone}</span>` : '';
                    let statusHtml = `<div class="list-item-right"><span class="units-pill">${d.units} units</span><button class="btn btn-success btn-sm" style="width:auto; margin-top: 4px;" onclick="confirmDonation(${d.id})">Confirm</button></div>`;
                    let metaInfo = `<div style="display:flex; flex-wrap:wrap; align-items:center; gap:4px 8px; font-size:13px; margin-top:4px;">${phoneText} ${waBtn} <span style="white-space:nowrap; color:var(--text-muted);">· ${d.created_at}</span></div>`;
                    pendingHtml += `<div class="list-item"><div class="list-item-left"><div class="blood-badge blood-badge-default">${d.blood_type}</div><div class="list-item-info"><h4 style="margin-bottom:0;">${d.name}</h4>${metaInfo}</div></div>${statusHtml}</div>`;
                } else {
                    let msg = encodeURIComponent(`Dear ${d.name}, thank you for donating blood at ${bankName}! Your contribution saves lives. 🩸 You can now download your Official Certificate of Appreciation from your BloodSync dashboard!`);
                    let waBtn = d.phone ? `<a href="https://wa.me/${cleanPhone}?text=${msg}" target="_blank" style="display:inline-flex; align-items:center; background-color:#25D366; color:white; border-radius:12px; padding:2px 8px; text-decoration:none; font-size:10px; font-weight:bold;">${waLogo}WhatsApp</a>` : '';
                    let phoneText = d.phone ? `<span style="white-space:nowrap; color:var(--text-secondary);">📱 ${d.phone}</span>` : '';
                    let statusHtml = `<div class="list-item-right"><span class="badge badge-success">+${d.units} units</span></div>`;
                    let metaInfo = `<div style="display:flex; flex-wrap:wrap; align-items:center; gap:4px 8px; font-size:13px; margin-top:4px;">${phoneText} ${waBtn} <span style="white-space:nowrap; color:var(--text-muted);">· ${d.created_at}</span></div>`;
                    completedHtml += `<div class="list-item"><div class="list-item-left"><div class="blood-badge blood-badge-default">${d.blood_type}</div><div class="list-item-info"><h4 style="margin-bottom:0;">${d.name}</h4>${metaInfo}</div></div>${statusHtml}</div>`;
                }
            });
            
            document.getElementById('donor-requests').innerHTML = pendingHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No pending requests</p>';
            document.getElementById('donors').innerHTML = completedHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No completed donors</p>';
        }

        function filterRequests() {
            let search = document.getElementById('searchBox').value.toLowerCase();
            let bType = document.getElementById('filterType').value;
            let filtered = allRequests.filter(r => r.requester_name.toLowerCase().includes(search) && (bType==='all'||r.blood_type===bType));
            
            let pendingHtml = '';
            let completedHtml = '';

            filtered.forEach(req => {
                let badgeClass = req.status==='pending'?'badge-pending':(req.status==='completed'?'badge-completed':'badge-received');
                let waLink = req.phone ? `<a href="https://wa.me/${req.phone.replace(/[^0-9]/g,'')}" target="_blank" class="wa-link">📱${req.phone}</a>` : '';
                let locBadge = (req.latitude && req.longitude) 
                    ? `<a href="request_details.php?id=${req.id}" target="_blank" class="badge" style="background:#e0f2fe; color:#0369a1; text-decoration:none; margin-left:4px; font-weight:600;" title="${req.location_address || 'View on Map'}">📍 Map</a>` 
                    : '';
                let locText = req.location_address ? ` · 📍 <span style="color:var(--text-secondary);">${req.location_address}</span>` : '';
                
                let itemHtml = `<div class="list-item ${req.is_urgent==1?'urgent-item':''}"><div class="list-item-left"><div class="blood-badge blood-badge-default">${req.blood_type}</div><div class="list-item-info"><h4>${req.requester_name} ${req.is_urgent==1?'<span class="badge badge-urgent">URGENT</span>':''}</h4><p>${req.requester_type.toUpperCase()} · ${waLink} · ${req.created_at}${locText}</p></div></div><div class="list-item-right"><span class="units-pill">${req.units} units</span><span class="badge ${badgeClass}">${req.status}</span>${locBadge}${req.status==='pending'?`<button class="btn btn-success btn-sm" style="width:auto;" onclick="markCompleted(${req.id},'${req.blood_type}',${req.units})">Approve</button>`:''}</div></div>`;
                
                if (req.status === 'pending') {
                    pendingHtml += itemHtml;
                } else {
                    completedHtml += itemHtml;
                }
            });

            document.getElementById('requests-pending').innerHTML = pendingHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No active requests</p>';
            document.getElementById('requests-completed').innerHTML = completedHtml || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No completed requests</p>';
        }

        function markCompleted(id, bloodType, units) {
            if(!confirm(`Approve emergency blood request #${id}? This will deduct ${units} units of ${bloodType} from inventory.`)) {
                return;
            }

            fetch('update_request_status.php', { 
                method: 'POST', 
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
                body: `id=${id}&status=completed&deduct_blood=${encodeURIComponent(bloodType)}&deduct_units=${units}` 
            })
            .then(r => r.json())
            .then(res => {
                if(res.status === 'error' || (res.message && res.message.includes('Error'))) {
                    alert(res.message || 'Error processing approval.');
                } else {
                    loadData();
                    showApprovalModal(res, bloodType, units);
                }
            })
            .catch(err => {
                console.error("markCompleted parse error:", err);
                loadData();
            });
        }

        function showApprovalModal(data, bloodType, units) {
            let emailStatus = '';
            if (notif.recipient_email) {
                if (notif.email_status === 'sent') {
                    emailStatus = `<div style="display:flex; align-items:center; gap:8px; padding:10px 12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; margin-top:10px;">
                      <span style="font-size:18px;">📧</span>
                      <div style="font-size:12px; color:#166534;">
                        <strong>Email Notification Dispatched:</strong><br>
                        <span style="color:#14532d; word-break:break-all;">${notif.recipient_email}</span>
                        <span style="font-size:10px; color:#15803d; display:block;">(Accepted by ${notif.email_gateway ? notif.email_gateway.toUpperCase() : 'SMTP'} gateway for delivery)</span>
                      </div>
                    </div>`;
                } else {
                    emailStatus = `<div style="display:flex; align-items:center; gap:8px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; margin-top:10px;">
                      <span style="font-size:18px;">📧</span>
                      <div style="font-size:12px; color:#334155;">
                        <strong>Email Notification Logged:</strong><br>
                        <span style="color:#1e293b; word-break:break-all;">${notif.recipient_email}</span>
                        <span style="font-size:10px; color:#64748b; display:block;">(Local simulation mode — Configure SMTP in .env for live internet delivery.)</span>
                      </div>
                    </div>`;
                }
            } else {
                emailStatus = `<div style="font-size:12px; color:#64748b; margin-top:6px;">No email on record.</div>`;
            }

            let smsStatus = notif.recipient_phone
                ? `<div style="display:flex; align-items:flex-start; gap:8px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; margin-top:8px;">
                     <span style="font-size:18px;">📱</span>
                     <div style="font-size:12px; color:#334155;">
                       <strong>SMS Notification Logged:</strong> ${notif.recipient_phone}<br>
                       <span style="color:#64748b; font-size:11px; display:inline-block; margin-top:2px;">"${notif.sms_message || ''}"</span>
                     </div>
                   </div>`
                : '';

            let waLogo = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width:14px; height:14px; fill:currentColor; margin-right:6px;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>`;
            let waAction = notif.whatsapp_url 
                ? `<div style="margin-top:16px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; text-align:center;">
                     <div style="font-size:12px; color:#166534; margin-bottom:8px; font-weight:600;">Optional: Send direct WhatsApp message to requester</div>
                     <a href="${notif.whatsapp_url}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none; background:#25D366; color:white; font-weight:700; padding:9px 18px; border-radius:8px; font-size:13px;">
                       ${waLogo} Open WhatsApp Alert
                     </a>
                   </div>`
                : '';

            let html = `
                <div style="font-size:14px; margin-bottom:12px; line-height:1.5;">
                    Successfully allocated & deducted <strong>${units} units</strong> of <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:700;">${bloodType}</span> from center inventory.
                </div>
                <div style="font-size:13px; color:#1e293b; background:#f8fafc; padding:10px 12px; border-radius:6px; border:1px solid var(--border);">
                    <div><strong>Requester:</strong> ${notif.recipient_name || 'Requester'}</div>
                    ${notif.recipient_phone ? `<div><strong>Phone:</strong> ${notif.recipient_phone}</div>` : ''}
                </div>
                <div>
                    ${emailStatus}
                    ${smsStatus}
                    ${waAction}
                </div>
            `;

            document.getElementById('approvalModalBody').innerHTML = html;
            document.getElementById('approvalModal').style.display = 'flex';
        }

        function closeApprovalModal() {
            document.getElementById('approvalModal').style.display = 'none';
        }

        function confirmDonation(id) {
            if(confirm('Confirm that this user has successfully donated? This will increment your inventory.')) {
                fetch('update_donation_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`donation_id=${id}&action=confirm` })
                .then(r=>r.json()).then(d => { 
                    if(!d.success) alert(d.error || 'Unknown error'); 
                    else loadData(); 
                });
            }
        }

        function openInventoryModal() {
            let html = '';
            let types = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'];
            types.forEach(t => {
                let val = currentInventory[t] || 0;
                html += `<div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">${t}</label>
                            <input type="number" class="form-input inv-input" data-type="${t}" value="${val}" min="0" required>
                         </div>`;
            });
            document.getElementById('inventoryInputs').innerHTML = html;
            document.getElementById('inventoryModal').style.display = 'flex';
        }

        function closeInventoryModal() {
            document.getElementById('inventoryModal').style.display = 'none';
        }

        function submitInventory(e) {
            e.preventDefault();
            let payload = {};
            document.querySelectorAll('.inv-input').forEach(input => {
                payload[input.getAttribute('data-type')] = input.value;
            });
            fetch('update_inventory.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(r=>r.json()).then(res => {
                if(res.success) {
                    closeInventoryModal();
                    loadData();
                } else {
                    alert('Error updating inventory: ' + (res.error || 'Unknown error'));
                }
            }).catch(e => alert('Error: ' + e));
        }

        loadData();
        setInterval(loadData, 3000);
    </script>
</body>
</html>
