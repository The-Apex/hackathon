<?php
require_once 'session_check.php';
requireLogin(['user']);

require_once 'db.php';
$donation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT d.*, u.name as center_name 
    FROM donations d 
    JOIN users u ON d.center_id = u.id 
    WHERE d.id = ? AND d.user_id = ? AND d.status = 'completed'
");
$stmt->bind_param("ii", $donation_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Certificate not found or unauthorized access.");
}

$donation = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Certificate - <?php echo htmlspecialchars($donation['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            -webkit-print-color-adjust: exact; /* Ensure colors print */
            print-color-adjust: exact;
        }
        .cert-container {
            width: 1050px;
            height: 742px; /* A4 Landscape aspect ratio */
            background: white;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        .cert-border {
            border: 4px solid #ef4444;
            height: 100%;
            padding: 40px;
            text-align: center;
            position: relative;
            background: #fff;
            border-radius: 8px;
        }
        .cert-border::before {
            content: '';
            position: absolute;
            top: 4px; left: 4px; right: 4px; bottom: 4px;
            border: 1px solid #f87171;
            border-radius: 4px;
        }
        .logo {
            font-size: 3.5rem;
            margin-bottom: 10px;
        }
        h1 {
            font-family: 'Dancing Script', cursive;
            font-size: 4.5rem;
            color: #1f2937;
            margin: 10px 0;
        }
        h2 {
            font-size: 1.5rem;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-top: 0;
            margin-bottom: 30px;
        }
        p {
            font-size: 1.25rem;
            color: #4b5563;
            line-height: 1.8;
            margin: 10px 0;
        }
        .donor-name {
            font-family: 'Dancing Script', cursive;
            font-size: 3.5rem;
            color: #ef4444;
            border-bottom: 2px solid #e5e7eb;
            display: inline-block;
            padding: 0 40px;
            margin: 15px 0 25px 0;
        }
        .cert-details {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
            font-size: 1.1rem;
        }
        .detail-item strong {
            display: block;
            color: #1f2937;
            font-size: 1.3rem;
            margin-top: 5px;
        }
        .footer {
            position: absolute;
            bottom: 50px;
            left: 80px;
            right: 80px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature {
            border-top: 1px solid #9ca3af;
            padding-top: 10px;
            width: 220px;
            color: #4b5563;
            font-weight: 600;
        }
        .badge {
            font-size: 0.95rem;
            background: #ef4444;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1f2937;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: 0.2s;
            z-index: 100;
        }
        .print-btn:hover {
            background: #000;
            transform: translateY(-2px);
        }

        @media print {
            @page { size: landscape; margin: 0; }
            body { background: white; padding: 0; }
            .cert-container { box-shadow: none; width: 100%; height: 100vh; padding: 20px; margin:0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="cert-container">
        <div class="cert-border">
            <div class="logo">🩸</div>
            <h2>Certificate of Appreciation</h2>
            <h1>Proud Blood Donor</h1>
            
            <p>This certificate is proudly presented to</p>
            <div class="donor-name"><?php echo htmlspecialchars($donation['name']); ?></div>
            
            <p>For their noble and life-saving contribution of <strong><?php echo $donation['units']; ?> Unit(s)</strong> of <strong><?php echo htmlspecialchars($donation['blood_type']); ?></strong> blood.<br>Your generosity brings hope and life to those in need.</p>
            
            <div class="cert-details">
                <div class="detail-item">
                    <span>Donated At</span>
                    <strong><?php echo htmlspecialchars($donation['center_name']); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Date of Donation</span>
                    <strong><?php echo date('F d, Y', strtotime($donation['created_at'])); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Save as PDF</button>

    <script>
        // Trigger print dialog automatically when page loads
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
