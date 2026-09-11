<?php
require_once 'session_check.php';
requireLogin(['user']);
$user = getCurrentUser();

$centers = [];
$res = $db->query("SELECT id, name, city FROM users WHERE role IN ('hospital', 'bank') ORDER BY city ASC, name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $centers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal — BloodSync</title>
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
            <span class="nav-btn" style="color: var(--text-muted); cursor: default;">👤 <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="logout.php" class="nav-btn nav-btn-ghost">Logout</a>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header">
            <h1>👤 User Portal</h1>
            <p>Donate blood to save lives or submit a request for blood you need.</p>
        </div>

        <div class="grid-2">
            <!-- DONATE BLOOD -->
            <div class="card delay-1">
                <div class="card-header">
                    <h2><span class="card-header-icon">💉</span> Donate Blood</h2>
                </div>
                <div class="card-body">
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
                            <select name="center_id" class="form-select" required>
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

            <!-- REQUEST BLOOD -->
            <div class="card delay-2">
                <div class="card-header">
                    <h2><span class="card-header-icon">📋</span> Request Blood</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="request.php">
                        <input type="hidden" name="requester_type" value="user">
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="requester_name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Center</label>
                            <select name="center_id" class="form-select" required>
                                <option value="">Choose a center...</option>
                                <?php foreach($centers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (' . $c['city'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
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
                            <label class="urgent-toggle">
                                <input type="checkbox" name="is_urgent" value="1">
                                <span>⚠ Emergency / Urgent Request</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-outline">📋 Submit Request</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- MY ACTIVITY -->
        <div style="margin-top: 28px;">
            <div class="grid-2">
                <div class="card delay-3">
                    <div class="card-header"><h2><span class="card-header-icon">💉</span> My Donations</h2></div>
                    <div class="card-body"><div id="my-donations" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
                </div>
                <div class="card delay-4">
                    <div class="card-header"><h2><span class="card-header-icon">📋</span> My Requests</h2></div>
                    <div class="card-body"><div id="my-requests" class="list-container"><p style="color:var(--text-muted);text-align:center;padding:40px 0;">Loading...</p></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadMyData() {
            fetch('get_my_donations.php').then(r=>r.json()).then(data => {
                let html = '';
                data.forEach(d => {
                    html += `<div class="list-item"><div class="list-item-left"><div class="blood-badge blood-badge-default">${d.blood_type}</div><div class="list-item-info"><h4>${d.blood_type} Donation</h4><p class="timestamp">${d.created_at}</p></div></div><span class="badge badge-success">+${d.units} units</span></div>`;
                });
                document.getElementById('my-donations').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No donations yet. Be the first!</p>';
            });

            fetch('get_requests.php?scope=mine').then(r=>r.json()).then(data => {
                let html = '';
                data.forEach(req => {
                    let badgeClass = req.status==='pending'?'badge-pending':(req.status==='completed'?'badge-completed':'badge-received');
                    html += `<div class="list-item ${req.is_urgent==1?'urgent-item':''}"><div class="list-item-left"><div class="blood-badge blood-badge-default">${req.blood_type}</div><div class="list-item-info"><h4>${req.blood_type} ${req.is_urgent==1?'<span class="badge badge-urgent">URGENT</span>':''}</h4><p class="timestamp">${req.created_at}</p></div></div><div class="list-item-right"><span class="units-pill">${req.units} units</span><span class="badge ${badgeClass}">${req.status}</span></div></div>`;
                });
                document.getElementById('my-requests').innerHTML = html || '<p style="color:var(--text-muted);text-align:center;padding:40px 0;">No requests yet.</p>';
            });
        }
        loadMyData();
        setInterval(loadMyData, 5000);
    </script>
</body>
</html>
