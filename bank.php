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

        <div class="grid-3">
            <!-- INVENTORY -->
            <div class="card delay-2">
                <div class="card-header"><h2><span class="card-header-icon">📦</span> Inventory</h2></div>
                <div class="card-body"><div id="inventory" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>
            <!-- DONORS -->
            <div class="card delay-3">
                <div class="card-header"><h2><span class="card-header-icon">💉</span> Donor Records</h2></div>
                <div class="card-body"><div id="donors" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
            </div>
            <!-- REQUESTS -->
            <div class="card delay-4">
                <div class="card-header"><h2><span class="card-header-icon">📋</span> Requests</h2></div>
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
                    <div id="requests" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allRequests = [];

        function loadData() {
            fetch('get_stats.php').then(r=>r.json()).then(d => {
                document.getElementById('stat-blood').innerText = d.total_blood + ' Units';
                document.getElementById('stat-requests').innerText = d.total_requests;
                document.getElementById('stat-donations').innerText = d.total_donations;
            });

            fetch('get_inventory.php').then(r=>r.json()).then(data => {
                let html = '';
                for (let type in data) {
                    let units = parseInt(data[type]);
                    let critical = units < 5;
                    html += `<div class="list-item ${critical?'critical-item':''}"><div class="list-item-left"><div class="blood-badge blood-badge-default">${type}</div><div class="list-item-info"><h4>${type} ${critical?'<span class="badge badge-critical">CRITICAL</span>':''}</h4><p>${units<5?'Low stock':'Healthy stock'}</p></div></div><span class="units-pill">${units} units</span></div>`;
                }
                document.getElementById('inventory').innerHTML = html;
            });

            fetch('get_donors.php').then(r=>r.json()).then(data => {
                let html = '';
                data.forEach(d => {
                    let waLink = d.phone ? `<a href="https://wa.me/${d.phone.replace(/[^0-9]/g,'')}" target="_blank" class="wa-link">📱 ${d.phone}</a>` : '';
                    html += `<div class="list-item"><div class="list-item-left"><div class="blood-badge blood-badge-default">${d.blood_type}</div><div class="list-item-info"><h4>${d.name}</h4><p>${waLink} · ${d.created_at}</p></div></div><span class="badge badge-success">+${d.units} units</span></div>`;
                });
                document.getElementById('donors').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No donors yet</p>';
            });

            fetch('get_requests.php').then(r=>r.json()).then(data => { allRequests = data; filterRequests(); });
        }

        function filterRequests() {
            let search = document.getElementById('searchBox').value.toLowerCase();
            let bType = document.getElementById('filterType').value;
            let filtered = allRequests.filter(r => r.requester_name.toLowerCase().includes(search) && (bType==='all'||r.blood_type===bType));
            let html = '';
            filtered.forEach(req => {
                let badgeClass = req.status==='pending'?'badge-pending':(req.status==='completed'?'badge-completed':'badge-received');
                let waLink = req.phone ? `<a href="https://wa.me/${req.phone.replace(/[^0-9]/g,'')}" target="_blank" class="wa-link">📱${req.phone}</a>` : '';
                html += `<div class="list-item ${req.is_urgent==1?'urgent-item':''}"><div class="list-item-left"><div class="blood-badge blood-badge-default">${req.blood_type}</div><div class="list-item-info"><h4>${req.requester_name} ${req.is_urgent==1?'<span class="badge badge-urgent">URGENT</span>':''}</h4><p>${req.requester_type.toUpperCase()} · ${waLink} · ${req.created_at}</p></div></div><div class="list-item-right"><span class="units-pill">${req.units} units</span><span class="badge ${badgeClass}">${req.status}</span>${req.status==='pending'?`<button class="btn btn-success btn-sm" onclick="markCompleted(${req.id},'${req.blood_type}',${req.units})">Approve</button>`:''}</div></div>`;
            });
            document.getElementById('requests').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No requests</p>';
        }

        function markCompleted(id, bloodType, units) {
            if(confirm(`Approve? Deduct ${units} units of ${bloodType}?`)) {
                fetch('update_request_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&status=completed&deduct_blood=${encodeURIComponent(bloodType)}&deduct_units=${units}` })
                .then(r=>r.text()).then(d => { if(d.includes('Error')) alert(d); else loadData(); });
            }
        }

        loadData();
        setInterval(loadData, 3000);
    </script>
</body>
</html>
