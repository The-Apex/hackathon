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
        .info-row span:first-child {
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
                        let waLink = c.phone ? `https://wa.me/${c.phone.replace(/[^0-9]/g,'')}` : '#';
                        
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
                                </div>
                                <div class="center-footer">
                                    <a href="${waLink}" target="_blank" class="btn btn-outline" style="width:100%; text-align:center;">Chat on WhatsApp</a>
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
