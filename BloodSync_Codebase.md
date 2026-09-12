# BloodSync Codebase

## Overview
BloodSync is a comprehensive Blood Bank Management System designed to connect blood donors, hospitals, and blood banks. It provides dedicated portals for users to donate or request blood, hospitals to manage blood supply requests, and blood banks to oversee inventory and fulfill incoming requests.

## Key Features
- **User Portal**: Allows individuals to register blood donations or request blood for emergencies.
- **Hospital Portal**: Enables hospitals to request blood units for patients and track the status of their requests.
- **Blood Bank Portal**: A centralized dashboard for blood bank administrators to monitor inventory, track recent donations, and approve or manage incoming requests.
- **Real-time Inventory Tracking**: Displays critical stock alerts and current blood availability.
- **Automated Request Management**: Deducts units from inventory automatically upon request approval.

## Technologies Used
- **Frontend**: HTML5, CSS3 (Custom styling with Inter font), Vanilla JavaScript (Fetch API)
- **Backend**: PHP (Procedural with MySQLi)
- **Database**: MySQL

## Setup Instructions
1. Install a local web server environment (e.g., XAMPP, WAMP, or MAMP).
2. Start the **Apache** and **MySQL** modules.
3. Place the `BloodSync` project folder inside the server's root directory (e.g., `htdocs` for XAMPP).
4. Navigate to `http://localhost/<project_folder_name>/init.php` in your web browser to initialize the database automatically. This script will create the `blood_bank` database, required tables (`donations`, `requests`, `inventory`), and seed the initial inventory.
5. Open `http://localhost/<project_folder_name>/index.html` to access the main application.

## File Structure & Source Code

## index.html
**Purpose**: Serves as the landing page for the application.
**Features**: Provides navigation to three different portals (User, Hospital, Blood Bank) via clickable cards. Includes introductory branding and animations.
**Functions**: No Javascript logic, pure static HTML structure linking to respective pages.

``html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System | Welcome</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container animate-in">
        <h1>ðŸ©¸ Blood Bank Management System</h1>
        
        <div class="portals">
            <a href="user.html" class="portal-card delay-1">
                <div class="icon">ðŸ‘¤</div>
                <h2>User Portal</h2>
                <p>Donate blood or request blood for personal emergencies. Save lives today.</p>
            </a>

            <a href="hospital.html" class="portal-card delay-2">
                <div class="icon">ðŸš‘</div>
                <h2>Hospital Portal</h2>
                <p>Request blood units for hospital patients and track supply status.</p>
            </a>

            <a href="bank.html" class="portal-card delay-3">
                <div class="icon">ðŸ¥</div>
                <h2>Blood Bank Portal</h2>
                <p>Manage blood inventory and track incoming requests from hospitals and patients.</p>
            </a>
        </div>
    </div>
</body>
</html>
``

## style.css
**Purpose**: The central stylesheet providing a unified UI/UX.
**Features**: Defines CSS variables for themes, colors, and shadows. Implements a responsive grid, flexbox layouts, hover effects, CSS animations, and custom scrollbars.
**Functions**: Handles styling for panels, badges, tables, and buttons across all HTML files.

``css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --primary-color: #e53935;
    --primary-hover: #c62828;
    --bg-color: #f4f7f6;
    --card-bg: rgba(255, 255, 255, 0.9);
    --text-color: #2c3e50;
    --text-muted: #7f8c8d;
    --success-bg: #e8f5e9;
    --success-text: #2e7d32;
    --warning-bg: #fff3e0;
    --warning-text: #e65100;
    --danger-bg: #ffebee;
    --danger-text: #c62828;
    --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    --border-radius: 12px;
    --border-color: #eaeaea;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

body {
    background: var(--bg-color);
    background-image: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
    color: var(--text-color);
    min-height: 100vh;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 40px;
    font-size: 2.5rem;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-header h1 {
    margin-bottom: 0;
    font-size: 2rem;
}

.back-link {
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 500;
    transition: color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-link:hover {
    color: var(--primary-color);
}

.portals {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    justify-content: center;
}

.portal-card {
    background: var(--card-bg);
    padding: 40px 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s;
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.portal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.7);
    border-color: var(--primary-color);
}

.portal-card .icon {
    font-size: 3rem;
    margin-bottom: 10px;
}

.portal-card h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
}

.portal-card p {
    color: var(--text-muted);
    line-height: 1.5;
}

/* Dashboard Specifics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    padding: 20px;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--shadow);
}

.stat-card h3 {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.panel {
    background: var(--card-bg);
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.panel h2 {
    color: var(--primary-color);
    margin-bottom: 20px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
}

.controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.controls input, .controls select {
    padding: 8px 12px;
    background: #ffffff;
    border: 1px solid var(--border-color);
    color: var(--text-color);
    border-radius: 6px;
    flex: 1;
}

/* Forms */
.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-muted);
}

input, select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
    background: #ffffff;
    color: var(--text-color);
}

input:focus, select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.2);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--danger-bg);
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid var(--danger-text);
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    transform: scale(1.2);
}

.checkbox-group label {
    margin: 0;
    color: var(--danger-text);
    font-weight: 700;
}

button {
    width: 100%;
    padding: 14px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.1rem;
    transition: background-color 0.3s, transform 0.1s;
    margin-top: 10px;
}

button.btn-secondary {
    background: #424242;
}

button.btn-secondary:hover {
    background: #616161;
}

button.btn-success {
    background: #2e7d32;
}

button.btn-success:hover {
    background: #1b5e20;
}

button:hover {
    background: var(--primary-hover);
}

button:active {
    transform: scale(0.98);
}

/* Status & Lists */
.msg-success {
    background: var(--success-bg);
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    color: var(--success-text);
    font-weight: 500;
    text-align: center;
    border: 1px solid var(--success-text);
}

.inventory-list, .requests-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 500px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: transparent; border-radius: 4px; }
::-webkit-scrollbar-thumb { background: #d0d0d0; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #b0b0b0; }

.item {
    background: #ffffff;
    padding: 15px;
    border-left: 4px solid #e0e0e0;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s, border-color 0.3s;
}

.item:hover {
    transform: translateX(5px);
}

.item.urgent {
    background: var(--danger-bg);
    border-left-color: var(--primary-color);
}

.item.critical-stock {
    border: 1px solid var(--primary-color);
    background: var(--danger-bg);
}

.item-left {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.item-type {
    font-weight: 700;
    color: var(--text-color);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.item-units {
    background: #f0f0f0;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-color);
}

.item-details {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-warning { background: var(--warning-bg); color: var(--warning-text); }
.badge-success { background: var(--success-bg); color: var(--success-text); }
.badge-danger { background: var(--danger-bg); color: var(--danger-text); }
.badge-info { background: rgba(33, 150, 243, 0.2); color: #64b5f6; }

.timestamp {
    font-size: 0.75rem;
    color: #777;
    margin-top: 5px;
}

.action-btn {
    padding: 6px 12px;
    font-size: 0.85rem;
    margin-top: 0;
    width: auto;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in { animation: fadeIn 0.6s ease-out forwards; }
.delay-1 { animation: fadeIn 0.6s ease-out 0.1s forwards; opacity: 0; }
.delay-2 { animation: fadeIn 0.6s ease-out 0.2s forwards; opacity: 0; }
.delay-3 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }

``

## user.html
**Purpose**: Dashboard for individual users to interact with the blood bank.
**Features**: Two main forms for donating blood and requesting blood. Includes options to specify blood type, units, and mark a request as urgent.
**Functions**: Submits forms via POST to `donate.php` and `request.php`.

``html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Blood Bank System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container animate-in">
        <div class="dashboard-header">
            <h1>ðŸ‘¤ User Dashboard</h1>
            <a href="index.html" class="back-link">â† Back to Home</a>
        </div>
        
        <div class="dashboard-grid">
            <!-- DONATE BLOOD -->
            <div class="panel delay-1">
                <h2>Donate Blood</h2>
                <form method="POST" action="donate.php">
                    <input type="hidden" name="redirect" value="user.html">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Blood Type</label>
                        <select name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Units to Donate</label>
                        <input type="number" name="units" placeholder="Number of units" min="1" required>
                    </div>
                    <button type="submit">âœ… Submit Donation</button>
                </form>
            </div>

            <!-- REQUEST BLOOD -->
            <div class="panel delay-2">
                <h2>Request Blood</h2>
                <form method="POST" action="request.php">
                    <input type="hidden" name="requester_type" value="user">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="requester_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Blood Type Needed</label>
                        <select name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Units Needed</label>
                        <input type="number" name="units" placeholder="Number of units" min="1" required>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_urgent" id="is_urgent_user" value="1">
                            <label for="is_urgent_user">EMERGENCY / URGENT</label>
                        </div>
                    </div>
                    <button type="submit" class="btn-secondary">ðŸ“‹ Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

``

## bank.html
**Purpose**: Administrative dashboard for managing blood bank operations.
**Features**: Displays total statistics (blood available, total requests, donations). Shows current inventory, recent donations, and incoming requests with search and filter capabilities.
**Functions**:
- `loadData()`: Fetches stats, inventory, donations, and requests using fetch API and updates the DOM.
- `filterRequests()`: Filters requests based on text search and blood type dropdown.
- `markCompleted()`: Confirms and approves blood requests by calling `update_request_status.php`.

``html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Dashboard | Blood Bank System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container animate-in">
        <div class="dashboard-header">
            <h1>ðŸ¥ Blood Bank Dashboard</h1>
            <a href="index.html" class="back-link">â† Back to Home</a>
        </div>
        
        <!-- Top Stats -->
        <div class="stats-grid delay-1">
            <div class="stat-card">
                <h3>Total Blood Available</h3>
                <div class="value" id="stat-blood">0 Units</div>
            </div>
            <div class="stat-card">
                <h3>Total Requests</h3>
                <div class="value" id="stat-requests">0</div>
            </div>
            <div class="stat-card">
                <h3>Total Donations</h3>
                <div class="value" id="stat-donations">0</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- INVENTORY -->
            <div class="panel delay-2">
                <h2>Current Inventory</h2>
                <div id="inventory" class="inventory-list" style="max-height: 300px;">
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">Loading inventory...</p>
                </div>

                <h2 style="margin-top: 30px;">Recent Donations</h2>
                <div id="recent-donations" class="requests-list" style="max-height: 250px;">
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">Loading donations...</p>
                </div>
            </div>

            <!-- REQUESTS -->
            <div class="panel delay-3">
                <h2>Incoming Requests</h2>
                <div class="controls">
                    <input type="text" id="searchBox" placeholder="Search Hospital or User..." onkeyup="filterRequests()">
                    <select id="filterType" onchange="filterRequests()">
                        <option value="all">All Blood Types</option>
                        <option value="O+">O+</option>
                        <option value="A+">A+</option>
                        <option value="B+">B+</option>
                        <option value="AB+">AB+</option>
                        <option value="O-">O-</option>
                        <option value="A-">A-</option>
                        <option value="B-">B-</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
                <div id="requests" class="requests-list">
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">Loading requests...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allRequests = [];

        function loadData() {
            // Load Stats
            fetch('get_stats.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('stat-blood').innerText = data.total_blood + ' Units';
                    document.getElementById('stat-requests').innerText = data.total_requests;
                    document.getElementById('stat-donations').innerText = data.total_donations;
                });

            // Load Inventory
            fetch('get_inventory.php')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    for (let type in data) {
                        let units = parseInt(data[type]);
                        let criticalClass = units < 5 ? 'critical-stock' : '';
                        let alertBadge = units < 5 ? '<span class="badge badge-danger">CRITICAL STOCK</span>' : '';
                        html += `
                            <div class="item ${criticalClass}">
                                <div class="item-left">
                                    <span class="item-type">ðŸ©¸ ${type} ${alertBadge}</span>
                                </div>
                                <span class="item-units">${units} units</span>
                            </div>`;
                    }
                    document.getElementById('inventory').innerHTML = html;
                });

            // Load Recent Donations
            fetch('get_recent_donations.php')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    data.forEach(don => {
                        html += `
                            <div class="item" style="padding: 10px;">
                                <div class="item-left">
                                    <span style="color:var(--text-color); font-weight:600;">${don.name}</span>
                                    <span class="timestamp">${don.created_at}</span>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-success">+${don.units} units</span>
                                    <div style="color:var(--primary-color); font-weight:bold; font-size:0.8rem; margin-top:4px;">${don.blood_type}</div>
                                </div>
                            </div>`;
                    });
                    document.getElementById('recent-donations').innerHTML = html || '<p style="color: var(--text-muted); text-align: center; padding: 20px;">No donations yet</p>';
                });

            // Load Requests
            fetch('get_requests.php')
                .then(res => res.json())
                .then(data => {
                    allRequests = data;
                    filterRequests();
                });
        }

        function filterRequests() {
            let search = document.getElementById('searchBox').value.toLowerCase();
            let bType = document.getElementById('filterType').value;

            let filtered = allRequests.filter(req => {
                let matchSearch = req.requester_name.toLowerCase().includes(search);
                let matchType = bType === 'all' || req.blood_type === bType;
                return matchSearch && matchType;
            });

            // Render
            let html = '';
            filtered.forEach(req => {
                let badgeClass = req.status === 'pending' ? 'badge-warning' : (req.status === 'completed' ? 'badge-info' : 'badge-success');
                let urgentClass = req.is_urgent == 1 ? 'urgent' : '';
                
                html += `
                    <div class="item ${urgentClass}">
                        <div class="item-left">
                            <span class="item-type">
                                ðŸ©¸ ${req.blood_type} 
                                ${req.is_urgent == 1 ? '<span class="badge badge-danger">URGENT</span>' : ''}
                            </span>
                            <div class="item-details">
                                <strong>${req.requester_type.toUpperCase()}:</strong> ${req.requester_name} <br>
                                <span class="timestamp">Requested: ${req.created_at}</span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="item-units">${req.units} units</span><br>
                            <div style="margin-top: 8px; margin-bottom: 5px;">
                                <span class="badge ${badgeClass}">${req.status}</span>
                            </div>
                            ${req.status === 'pending' ? `<button class="action-btn btn-success" onclick="markCompleted(${req.id}, '${req.blood_type}', ${req.units})">Approve</button>` : ''}
                        </div>
                    </div>`;
            });
            document.getElementById('requests').innerHTML = html || '<p style="color: var(--text-muted); text-align: center; padding: 20px;">No matching requests</p>';
        }

        function markCompleted(id, bloodType, units) {
            if(confirm(`Approve request? This will deduct ${units} units of ${bloodType} from inventory.`)) {
                fetch('update_request_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}&status=completed&deduct_blood=${bloodType}&deduct_units=${units}`
                })
                .then(res => res.text())
                .then(data => {
                    if(data.includes('Error')) {
                        alert(data);
                    } else {
                        loadData();
                    }
                });
            }
        }

        // Initial load and polling
        loadData();
        setInterval(loadData, 3000);
    </script>
</body>
</html>

``

## hospital.html
**Purpose**: Dashboard for hospitals to request blood for patients and track requests.
**Features**: Form to submit hospital blood requests, list of their request statuses, and the ability to mark approved requests as 'received'.
**Functions**:
- `loadHospitalRequests()`: Fetches hospital-specific requests from the database.
- `markReceived()`: Updates the status of an approved request to received via `update_request_status.php`.

``html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard | Blood Bank System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container animate-in">
        <div class="dashboard-header">
            <h1>ðŸš‘ Hospital Dashboard</h1>
            <a href="index.html" class="back-link">â† Back to Home</a>
        </div>
        
        <div class="dashboard-grid">
            <!-- REQUEST BLOOD -->
            <div class="panel delay-1">
                <h2>Request Blood Supply</h2>
                <form method="POST" action="request.php">
                    <input type="hidden" name="requester_type" value="hospital">
                    <div class="form-group">
                        <label>Hospital Name</label>
                        <input type="text" name="requester_name" placeholder="Enter hospital name" required>
                    </div>
                    <div class="form-group">
                        <label>Blood Type Needed</label>
                        <select name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Units Needed</label>
                        <input type="number" name="units" placeholder="Number of units" min="1" required>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_urgent" id="is_urgent" value="1">
                            <label for="is_urgent">EMERGENCY / URGENT</label>
                        </div>
                    </div>
                    <button type="submit">ðŸš‘ Submit Hospital Request</button>
                </form>
            </div>

            <!-- HOSPITAL REQUESTS -->
            <div class="panel delay-2">
                <h2>Our Requests Status</h2>
                <div id="hospital-requests" class="requests-list">
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">Loading requests...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadHospitalRequests() {
            fetch('get_requests.php?type=hospital')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    data.forEach(req => {
                        let badgeClass = req.status === 'pending' ? 'badge-warning' : (req.status === 'completed' ? 'badge-info' : 'badge-success');
                        let urgentClass = req.is_urgent == 1 ? 'urgent' : '';
                        
                        html += `
                            <div class="item ${urgentClass}">
                                <div class="item-left">
                                    <span class="item-type">
                                        ðŸ©¸ ${req.blood_type} 
                                        ${req.is_urgent == 1 ? '<span class="badge badge-danger">URGENT</span>' : ''}
                                    </span>
                                    <div class="item-details">
                                        <strong>Hospital:</strong> ${req.requester_name} <br>
                                        <span class="timestamp">Requested: ${req.created_at}</span>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="item-units">${req.units} units</span><br>
                                    <div style="margin-top: 8px;">
                                        <span class="badge ${badgeClass}">${req.status}</span>
                                    </div>
                                    ${req.status === 'completed' ? `<button class="action-btn btn-success" onclick="markReceived(${req.id})" style="margin-top: 5px;">Mark Received</button>` : ''}
                                </div>
                            </div>`;
                    });
                    document.getElementById('hospital-requests').innerHTML = html || '<p style="color: var(--text-muted); text-align: center; padding: 20px;">No requests found</p>';
                });
        }

        function markReceived(id) {
            fetch('update_request_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&status=received`
            })
            .then(res => res.text())
            .then(data => {
                loadHospitalRequests();
            });
        }

        loadHospitalRequests();
        setInterval(loadHospitalRequests, 3000);
    </script>
</body>
</html>

``

## init.php
**Purpose**: Database initialization and setup script.
**Features**: Drops existing tables to avoid conflicts, creates schema for `donations`, `requests`, and `inventory`, and seeds initial inventory data.
**Functions**: Connects to MySQL, executes DDL queries (`CREATE TABLE`), and performs initial data insertion (`INSERT INTO`).

``php
<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Drop existing tables to apply new schema
$db->query("DROP TABLE IF EXISTS donations");
$db->query("DROP TABLE IF EXISTS requests");
$db->query("DROP TABLE IF EXISTS inventory");

// Create Donations Table
$db->query("CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    blood_type VARCHAR(10),
    units INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create Requests Table
$db->query("CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_name VARCHAR(100),
    requester_type ENUM('user', 'hospital') DEFAULT 'user',
    blood_type VARCHAR(10),
    units INT,
    status ENUM('pending', 'completed', 'received') DEFAULT 'pending',
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create Inventory Table
$db->query("CREATE TABLE IF NOT EXISTS inventory (
    blood_type VARCHAR(10) PRIMARY KEY,
    units INT
)");

// Seed Inventory
$db->query("INSERT INTO inventory VALUES ('O+', 10)");
$db->query("INSERT INTO inventory VALUES ('A+', 8)");
$db->query("INSERT INTO inventory VALUES ('B+', 12)");
$db->query("INSERT INTO inventory VALUES ('AB+', 5)");
$db->query("INSERT INTO inventory VALUES ('O-', 4)");
$db->query("INSERT INTO inventory VALUES ('A-', 2)");
$db->query("INSERT INTO inventory VALUES ('B-', 3)");
$db->query("INSERT INTO inventory VALUES ('AB-', 1)");

echo "âœ… Database initialized with new Hackathon schema!";
?>

``

## donate.php
**Purpose**: Backend handler for blood donation submissions.
**Features**: Validates and sanitizes input, creates a new record in `donations` table, and adds the donated units to the `inventory` table.
**Functions**: Performs SQL `INSERT` and `UPDATE` queries, then renders a success message with a redirect button to the user dashboard.

``php
<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$name = $db->real_escape_string($_POST['name']);
$blood_type = $db->real_escape_string($_POST['blood_type']);
$units = (int)$_POST['units'];

$db->query("INSERT INTO donations (name, blood_type, units) VALUES ('$name', '$blood_type', $units)");
$db->query("UPDATE inventory SET units = units + $units WHERE blood_type = '$blood_type'");

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'user.html';

echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='style.css'></head><body style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f7f6;'>";
echo "<div class='panel' style='text-align:center;'>";
echo "<div class='msg-success'>âœ… Thank you $name for donating $units units of $blood_type blood!</div>";
echo "<a href='$redirect'><button>â† Back to Dashboard</button></a>";
echo "</div></body></html>";
?>

``

## request.php
**Purpose**: Backend handler for submitting blood requests.
**Features**: Handles both user and hospital requests, sanitizes input, and saves the request details (including urgency flag) to the `requests` table.
**Functions**: Performs SQL `INSERT` into the `requests` table and renders a success message based on the requester type.

``php
<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$requester_name = $db->real_escape_string($_POST['requester_name']);
$requester_type = $db->real_escape_string($_POST['requester_type']); // 'user' or 'hospital'
$blood_type = $db->real_escape_string($_POST['blood_type']);
$units = (int)$_POST['units'];
$is_urgent = isset($_POST['is_urgent']) && $_POST['is_urgent'] == '1' ? 1 : 0;

$db->query("INSERT INTO requests (requester_name, requester_type, blood_type, units, is_urgent) 
            VALUES ('$requester_name', '$requester_type', '$blood_type', $units, $is_urgent)");

$redirect = $requester_type == 'hospital' ? 'hospital.html' : 'user.html';

echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='style.css'></head><body style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f7f6;'>";
echo "<div class='panel' style='text-align:center;'>";
echo "<div class='msg-success'>âœ… Blood request for $units units of $blood_type submitted successfully!</div>";
echo "<a href='$redirect'><button>â† Back to Dashboard</button></a>";
echo "</div></body></html>";
?>

``

## get_inventory.php
**Purpose**: API endpoint to fetch current blood inventory.
**Features**: Queries the `inventory` table and returns the units available for each blood type.
**Functions**: Executes a `SELECT` query and formats the result as a JSON object.

``php
<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$result = $db->query("SELECT blood_type, units FROM inventory");
$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[$row['blood_type']] = $row['units'];
}
echo json_encode($inventory);
?>

``

## get_requests.php
**Purpose**: API endpoint to fetch list of blood requests.
**Features**: Supports filtering by `requester_type` (e.g., hospital). Sorts the returned data with a priority logic: urgent requests first, pending requests next, followed by completed/received requests, and lastly by creation date.
**Functions**: Executes a complex `SELECT` query with `ORDER BY CASE` logic, returning the result as a JSON array.

``php
<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$typeFilter = isset($_GET['type']) ? $db->real_escape_string($_GET['type']) : '';
$where = $typeFilter == 'hospital' ? "WHERE requester_type = 'hospital'" : "";

// Sort by: Urgent first -> Pending -> Completed/Received
$query = "SELECT id, requester_name, requester_type, blood_type, units, status, is_urgent, 
          DATE_FORMAT(created_at, '%b %d %H:%i') as created_at 
          FROM requests 
          $where
          ORDER BY is_urgent DESC, 
          CASE WHEN status = 'pending' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END, 
          created_at DESC";

$result = $db->query($query);
$requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
echo json_encode($requests);
?>

``

## get_stats.php
**Purpose**: API endpoint to fetch dashboard statistics.
**Features**: Computes aggregate metrics such as total blood units available, total requests made, and total donations registered.
**Functions**: Executes multiple `SELECT` queries with `SUM()` and `COUNT()` aggregate functions and returns a JSON object.

``php
<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$stats = ['total_blood' => 0, 'total_requests' => 0, 'total_donations' => 0];

// Total Blood
$res = $db->query("SELECT SUM(units) as total FROM inventory");
if ($row = $res->fetch_assoc()) $stats['total_blood'] = $row['total'] ? $row['total'] : 0;

// Total Requests
$res = $db->query("SELECT COUNT(*) as total FROM requests");
if ($row = $res->fetch_assoc()) $stats['total_requests'] = $row['total'];

// Total Donations
$res = $db->query("SELECT COUNT(*) as total FROM donations");
if ($row = $res->fetch_assoc()) $stats['total_donations'] = $row['total'];

echo json_encode($stats);
?>

``

## get_recent_donations.php
**Purpose**: API endpoint to fetch the latest donations.
**Features**: Retrieves the most recent 5 donations for display in the blood bank portal.
**Functions**: Executes a `SELECT` query ordered by `created_at DESC` with a `LIMIT 5`, returning a JSON array.

``php
<?php
header('Content-Type: application/json');
$db = new mysqli("localhost", "root", "", "blood_bank");

$result = $db->query("SELECT name, blood_type, units, DATE_FORMAT(created_at, '%b %d %H:%i') as created_at FROM donations ORDER BY created_at DESC LIMIT 5");
$donations = [];
while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}
echo json_encode($donations);
?>

``

## update_request_status.php
**Purpose**: API endpoint to update the status of a blood request.
**Features**: Modifies request status (e.g., pending -> completed -> received). If a request is being approved (completed), it first verifies if enough inventory exists, deducts the required units, and then updates the request status. Includes inventory validation to prevent negative stock.
**Functions**: Validates inventory via a `SELECT` query, conditionally executes an `UPDATE` on the `inventory` table, and finally an `UPDATE` on the `requests` table.

``php
<?php
$db = new mysqli("localhost", "root", "", "blood_bank");

$id = (int)$_POST['id'];
$status = $db->real_escape_string($_POST['status']);

if (isset($_POST['deduct_blood']) && isset($_POST['deduct_units'])) {
    $blood_type = $db->real_escape_string($_POST['deduct_blood']);
    $units = (int)$_POST['deduct_units'];

    // Check inventory first
    $res = $db->query("SELECT units FROM inventory WHERE blood_type = '$blood_type'");
    $row = $res->fetch_assoc();
    if ($row['units'] < $units) {
        echo "Error: Not enough blood in inventory!";
        exit;
    }

    // Deduct
    $db->query("UPDATE inventory SET units = units - $units WHERE blood_type = '$blood_type'");
}

$db->query("UPDATE requests SET status = '$status' WHERE id = $id");
echo "Success";
?>

``


