<?php
session_start();
// This page is publicly accessible so users can see where to donate without logging in.
// But if they are logged in, we can show their name in the navbar.
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
$user_role = $is_logged_in ? $_SESSION['role'] : '';

function getDashboardLink($role) {
    if ($role === 'user') return 'user.php';
    if ($role === 'hospital') return 'hospital.php';
    if ($role === 'bank') return 'bank.php';
    return 'login.html';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Blood Centers — BloodSync</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-bar {
            background: var(--surface);
            padding: 16px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            align-items: center;
        }
        .filter-bar input, .filter-bar select {
            flex: 1;
            margin: 0;
        }
        .filter-bar button {
            width: auto;
            padding: 11px 24px;
        }
        .center-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .center-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }
        .center-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .center-header h3 {
            font-size: 1.1rem;
            color: var(--text);
            margin-bottom: 4px;
        }
        .center-role {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 100px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-bank { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .role-hospital { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        
        .center-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .info-row > span:first-child {
            color: var(--primary);
            margin-top: 2px;
        }
        .center-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--border);
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
            <?php if ($is_logged_in): ?>
                <a href="<?php echo getDashboardLink($user_role); ?>" class="nav-btn nav-btn-primary">Dashboard</a>
            <?php else: ?>
                <a href="login.html" class="nav-btn nav-btn-ghost">Sign In</a>
                <a href="register.html" class="nav-btn nav-btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="page-wrapper animate-in">
        <div class="page-header">
            <h1>📍 Find Blood Centers</h1>
            <p>Locate nearby hospitals and blood banks to donate or request blood.</p>
        </div>

        <div class="filter-bar">
            <input type="text" id="searchInput" class="form-input" placeholder="Search by name or area...">
            <select id="cityFilter" class="form-select">
                <option value="All">All Cities</option>
                <option value="Ahmedabad">Ahmedabad</option>
                <option value="Surat">Surat</option>
                <option value="Vadodara">Vadodara</option>
                <option value="Rajkot">Rajkot</option>
                <option value="Mumbai">Mumbai</option>
            </select>
            <button class="btn btn-primary" onclick="loadCenters()">Search</button>
        </div>

        <div class="grid-3" id="centersGrid">
            <p style="color:var(--text-muted);text-align:center;grid-column: 1 / -1;padding:40px 0;">Loading centers...</p>
        </div>
    </div>

    <script>
        function loadCenters() {
            const city = document.getElementById('cityFilter').value;
            const search = document.getElementById('searchInput').value;
            
            fetch(`get_centers.php?city=${encodeURIComponent(city)}&search=${encodeURIComponent(search)}`)
                .then(r => r.json())
                .then(data => {
                    const grid = document.getElementById('centersGrid');
                    if (data.length === 0) {
                        grid.innerHTML = '<p style="color:var(--text-muted);text-align:center;grid-column: 1 / -1;padding:40px 0;">No centers found matching your criteria.</p>';
                        return;
                    }
                    
                    let html = '';
                    data.forEach(c => {
                        let isBank = c.role === 'bank';
                        let roleLabel = isBank ? 'Blood Bank' : 'Hospital';
                        let roleClass = isBank ? 'role-bank' : 'role-hospital';
                        
                        let waLogo = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width:16px; height:16px; fill:currentColor; margin-right:6px; vertical-align:middle; position:relative; top:-1px;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>`;
                        let msg = encodeURIComponent(`Hello ${c.name}, I found your center on BloodSync and would like to inquire about blood donation and availability.`);
                        let waLink = c.phone ? `https://wa.me/${c.phone.replace(/[^0-9]/g,'')}?text=${msg}` : '#';
                        
                        let groupsHtml = '';
                        if (c.available_groups) {
                            let groups = c.available_groups.split(',');
                            groups.forEach(g => {
                                groupsHtml += `<span style="display:inline-block; font-size:0.75rem; font-weight:600; padding:2px 6px; border:1px solid var(--border); border-radius:4px; margin: 2px 4px 2px 0;">${g.trim()}</span>`;
                            });
                        } else {
                            groupsHtml = '<span style="color:var(--text-muted);font-size:0.8rem;">No stock available</span>';
                        }
                        
                        html += `
                            <div class="center-card delay-${(c.id % 5) + 1}">
                                <div class="center-header">
                                    <h3>${c.name}</h3>
                                    <span class="center-role ${roleClass}">${roleLabel}</span>
                                </div>
                                <div class="center-info">
                                    <div class="info-row">
                                        <span>📍</span>
                                        <div>
                                            <strong>${c.city}</strong><br>
                                            <span style="font-size:0.85rem">${c.address || 'Address not provided'}</span>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <span>📞</span>
                                        <span>${c.phone}</span>
                                    </div>
                                    <div class="info-row" style="margin-top:4px;">
                                        <span>🩸</span>
                                        <div style="font-size:0.85rem; padding-top:2px;">
                                            <strong style="color:var(--text-secondary)">Available Groups:</strong><br>
                                            <div style="margin-top:4px;">${groupsHtml}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="center-footer">
                                    <a href="${waLink}" target="_blank" class="btn" style="width:100%; text-align:center; background-color:#25D366; color:white; border:none; display:flex; justify-content:center; align-items:center;">${waLogo} Chat on WhatsApp</a>
                                </div>
                            </div>
                        `;
                    });
                    grid.innerHTML = html;
                });
        }

        // Add event listeners for enter key on search input
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') loadCenters();
        });
        
        // Load initially
        loadCenters();
    </script>
</body>
</html>
