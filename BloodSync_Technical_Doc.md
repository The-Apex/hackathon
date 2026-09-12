# BloodSync Technical Documentation

## 1. Project Overview & Architecture
BloodSync is a comprehensive Blood Bank Management System that bridges the gap between public users, hospitals, and blood bank administrators. It enables users to donate or request blood, allows hospitals to submit and track bulk blood requests, and provides administrators with a centralized dashboard to monitor inventory and fulfill incoming requests in real-time.

**Tech Stack:**
- **Frontend:** Vanilla HTML5, CSS3, JavaScript (Fetch API)
- **Backend:** PHP (Procedural with MySQLi)
- **Database:** MySQL
- **Environment:** Apache/XAMPP

**System Flow Diagram:**
```text
[ User / Hospital Browser ] 
       |
       | (Submits Form / AJAX fetch())
       v
[ PHP API Endpoints ] (donate.php, request.php, get_*.php)
       |
       | (Executes SQL Queries)
       v
[ MySQL Database ] (blood_bank DB)
       |
       | (Returns Data / Success Status)
       v
[ PHP API Endpoints ]
       |
       | (Returns JSON / HTML Response)
       v
[ User / Admin Dashboard ] (Updates UI dynamically via JS)
```

## 2. Database Schema Reference
The system utilizes a MySQL database named `blood_bank`, containing three primary tables. The schema is initialized via the `init.php` script, which drops existing tables to prevent conflicts, creates fresh schemas, and seeds the initial inventory.

### Table: `inventory`
**Purpose:** Tracks the available units for each blood type.
| Column | Type | Constraints | Description |
|---|---|---|---|
| `blood_type` | VARCHAR(10) | PRIMARY KEY | The specific blood group (e.g., O+, A-). |
| `units` | INT | None | The current available stock in units. |

### Table: `donations`
**Purpose:** Logs all incoming blood donations from users.
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique donation identifier. |
| `name` | VARCHAR(100) | None | Name of the blood donor. |
| `blood_type` | VARCHAR(10) | None | The blood group donated. |
| `units` | INT | None | Number of units donated. |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Timestamp of the donation. |

### Table: `requests`
**Purpose:** Stores blood requests from both individual users and hospitals.
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique request identifier. |
| `requester_name` | VARCHAR(100) | None | Name of the individual or hospital. |
| `requester_type` | ENUM | DEFAULT 'user' | Indicates who made the request ('user' or 'hospital'). |
| `blood_type` | VARCHAR(10) | None | Blood group needed. |
| `units` | INT | None | Number of units requested. |
| `status` | ENUM | DEFAULT 'pending' | Current status ('pending', 'completed', 'received'). |
| `is_urgent` | BOOLEAN | DEFAULT FALSE | Flags emergency requests. |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Timestamp of the request. |

## 3. File-by-File Code & Functional Breakdown

### Frontend Pages

#### `index.html`
- **Responsibility:** Landing page acting as a portal gateway.
- **Functions:** Static HTML links navigating to `user.html`, `hospital.html`, and `bank.html` via animated portal cards.

#### `user.html`
- **Responsibility:** Dashboard for individual donors and recipients.
- **Functions:** Contains HTML forms to POST to `donate.php` (donor name, type, units) and `request.php` (requester name, type, units, urgency flag).

#### `hospital.html`
- **Responsibility:** Dashboard for hospitals to manage blood supply.
- **Functions:** 
  - `loadHospitalRequests()`: Polls `get_requests.php?type=hospital` via GET and dynamically renders pending/completed requests.
  - `markReceived(id)`: POSTs to `update_request_status.php` to acknowledge receipt of approved blood units.

#### `bank.html`
- **Responsibility:** Administrative dashboard for the blood bank.
- **Functions:** 
  - `loadData()`: Simultaneously fetches from `get_stats.php`, `get_inventory.php`, `get_recent_donations.php`, and `get_requests.php` using AJAX polling (every 3s).
  - `filterRequests()`: Sorts and filters the fetched requests array based on text input and blood type dropdown selection.
  - `markCompleted(id, bloodType, units)`: Posts to `update_request_status.php` to approve a request, passing data to deduct units from the inventory.

#### `style.css`
- **Responsibility:** Centralized styling for the entire application. 
- **Functions:** Implements a responsive CSS grid/flexbox layout, root color variables, glassmorphism card effects, scrollbars, and keyframe transition animations.

### Backend Scripts (PHP)

#### `init.php`
- **Responsibility:** Database initialization and setup.
- **Logic:** Executes `DROP TABLE IF EXISTS`, `CREATE TABLE` for the three main tables, and seeds the `inventory` table with mock initial stock.

#### `donate.php`
- **Responsibility:** Handles blood donation form submissions.
- **Logic:** Receives POST data. Executes `INSERT INTO donations` and instantly increments stock via `UPDATE inventory SET units = units + $units`. Returns an HTML success state with a redirect button.

#### `request.php`
- **Responsibility:** Handles blood request form submissions.
- **Logic:** Receives POST data. Executes `INSERT INTO requests` recording the requester type and urgency flag. Returns an HTML success state.

#### `get_inventory.php`
- **Responsibility:** API endpoint to fetch current stock.
- **Logic:** GET method. Executes `SELECT blood_type, units FROM inventory`. Returns a JSON object mapped by blood type.

#### `get_requests.php`
- **Responsibility:** API endpoint to fetch pending and completed requests.
- **Logic:** GET method. Accepts optional `?type=hospital` query parameter. Executes a complex `SELECT` query utilizing `ORDER BY is_urgent DESC` and conditionally sorts by status to ensure critical requests are prioritized. Returns a JSON array.

#### `get_stats.php`
- **Responsibility:** API endpoint to fetch aggregate dashboard metrics.
- **Logic:** GET method. Executes `SUM(units)` on inventory, and `COUNT(*)` on requests/donations. Returns a JSON object for top-level stat cards.

#### `get_recent_donations.php`
- **Responsibility:** API endpoint to fetch latest donations.
- **Logic:** GET method. Executes `SELECT` on donations with `ORDER BY created_at DESC LIMIT 5`. Returns a JSON array.

#### `update_request_status.php`
- **Responsibility:** Processes request approvals and receipts.
- **Logic:** POST method. If a request is being approved (`status=completed`), it queries the inventory to check if `inventory.units >= requested.units`. If true, it performs an atomic `UPDATE inventory` deduction and `UPDATE requests` status change. Returns a plain text success or error message.

## 4. Core Features & Business Logic

- **Live Inventory Tracking & Critical Stock Alerts:** The admin dashboard polls the inventory API periodically. If any blood type stock falls below 5 units, the system automatically flags it with a "CRITICAL STOCK" warning badge and highlights the row in red.
- **Emergency / Urgent Request Prioritization:** When a user or hospital flags a request as "EMERGENCY / URGENT", the SQL query in `get_requests.php` dynamically sorts these requests to the very top of the admin queue (`ORDER BY is_urgent DESC`), visually highlighting them.
- **Request Approval Workflow:** When an admin approves a request, the backend (`update_request_status.php`) verifies stock levels. If sufficient, it deducts the units from the `inventory` table and marks the request as `completed`. This validation prevents negative stock overrides.
- **Hospital Request & "Mark Received" Lifecycle:** Hospitals track their own requests separately. Once the admin marks a request as `completed` (approved and dispatched), the hospital's dashboard displays a "Mark Received" button. Clicking this updates the status to `received`, completing the lifecycle and closing the loop.
- **Donor Records & Extensibility:** The system logs all incoming donations with accurate timestamps. The architecture naturally supports extensibility for Contact Actions (e.g., Call/WhatsApp integration based on donor info) and CSV Export by expanding the `donations` schema and utilizing the `get_recent_donations.php` endpoints for bulk extraction.

## 5. API & Endpoint Reference Table

| Endpoint Name | HTTP Method | Params Received | Response Format | Purpose |
|---|---|---|---|---|
| `init.php` | GET | None | HTML / Text | Initializes and resets the database schema. |
| `donate.php` | POST | `name`, `blood_type`, `units`, `redirect` | HTML | Submits a donation & increments inventory. |
| `request.php` | POST | `requester_name`, `requester_type`, `blood_type`, `units`, `is_urgent` | HTML | Submits a blood request. |
| `get_inventory.php` | GET | None | JSON Object | Returns current units available per blood type. |
| `get_requests.php` | GET | `type` (optional filter, e.g., 'hospital') | JSON Array | Returns a priority-sorted list of requests. |
| `get_stats.php` | GET | None | JSON Object | Returns total aggregate metrics (blood, requests, donations). |
| `get_recent_donations.php`| GET | None | JSON Array | Returns the 5 most recent donation records. |
| `update_request_status.php`| POST| `id`, `status`, `deduct_blood`, `deduct_units` | Text String | Updates request status and deducts inventory upon approval. |
