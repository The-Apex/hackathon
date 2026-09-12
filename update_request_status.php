<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'session_check.php';
requireLogin(['bank', 'hospital']);
require_once 'db.php';

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['role'];

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($id <= 0 || !in_array($status, ['completed', 'received'])) {
    echo json_encode(['status' => 'error', 'message' => 'Error: Invalid request parameters.']);
    exit;
}

if ($status === 'completed') {
    // Only registered blood banks or hospital blood centers can approve requests destined for their center
    if (!in_array($userRole, ['bank', 'hospital'])) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Unauthorized action. Only blood centers can approve requests.']);
        exit;
    }

    // Query request joined with requester profile
    $reqStmt = $db->prepare("
        SELECT r.id, r.user_id, r.center_id, r.requester_name, r.phone as req_phone, 
               r.blood_type, r.units, r.status, r.is_urgent, r.location_address,
               u.name as user_name, u.email as user_email, u.phone as user_phone
        FROM requests r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND r.center_id = ?
    ");
    $reqStmt->bind_param("ii", $id, $userId);
    $reqStmt->execute();
    $reqResult = $reqStmt->get_result();

    if ($reqResult->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Request not found or not assigned to your center.']);
        exit;
    }

    $requestData = $reqResult->fetch_assoc();
    if ($requestData['status'] !== 'pending') {
        echo json_encode(['status' => 'error', 'message' => 'Error: Request is already ' . htmlspecialchars($requestData['status']) . '.']);
        exit;
    }

    // Blood bank details
    $bankStmt = $db->prepare("SELECT id, name, phone, email, address, city FROM users WHERE id = ?");
    $bankStmt->bind_param("i", $userId);
    $bankStmt->execute();
    $bankData = $bankStmt->get_result()->fetch_assoc();

    $bankName = $bankData['name'] ?? 'BloodSync Blood Bank';
    $bankPhone = $bankData['phone'] ?? '';
    $bankEmail = $bankData['email'] ?? 'support@bloodsync.com';
    $bankAddress = ($bankData['address'] ?? '') . (!empty($bankData['city']) ? ', ' . $bankData['city'] : '');

    // Deduct blood / units
    $blood_type = isset($_POST['deduct_blood']) ? trim($_POST['deduct_blood']) : $requestData['blood_type'];
    $units = isset($_POST['deduct_units']) ? (int)$_POST['deduct_units'] : (int)$requestData['units'];

    $db->begin_transaction();
    try {
        // Lock inventory row to prevent concurrent race condition
        $invStmt = $db->prepare("SELECT units FROM inventory WHERE blood_type = ? AND center_id = ? FOR UPDATE");
        $invStmt->bind_param("si", $blood_type, $userId);
        $invStmt->execute();
        $invRow = $invStmt->get_result()->fetch_assoc();

        if (!$invRow || (int)$invRow['units'] < $units) {
            $db->rollback();
            $available = $invRow ? (int)$invRow['units'] : 0;
            echo json_encode(['status' => 'error', 'message' => "Error: Not enough blood in inventory! (Available: {$available}, Requested: {$units})"]);
            exit;
        }

        // Deduct inventory
        $deductStmt = $db->prepare("UPDATE inventory SET units = units - ? WHERE blood_type = ? AND center_id = ?");
        $deductStmt->bind_param("isi", $units, $blood_type, $userId);
        $deductStmt->execute();

        // Update request status
        $updateStmt = $db->prepare("UPDATE requests SET status = 'completed' WHERE id = ?");
        $updateStmt->bind_param("i", $id);
        $updateStmt->execute();

        $db->commit();

        // Prepare Recipient Contact Info
        $recipientName = !empty($requestData['requester_name']) ? $requestData['requester_name'] : (!empty($requestData['user_name']) ? $requestData['user_name'] : 'Requester');
        $recipientEmail = !empty($requestData['user_email']) ? trim($requestData['user_email']) : '';
        $recipientPhone = !empty($requestData['req_phone']) ? trim($requestData['req_phone']) : (!empty($requestData['user_phone']) ? trim($requestData['user_phone']) : '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $recipientPhone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // Standardize to Indian country code
        }

        $isUrgentText = $requestData['is_urgent'] ? ' [URGENT EMERGENCY]' : '';

        // 1. Email Notification Dispatch
        $emailSubject = "🩸 Blood Request Approved (#{$id}) - {$bankName}{$isUrgentText}";
        $htmlEmail = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
            <div style='background: #dc2626; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 22px;'>BloodSync Emergency Network</h1>
                <p style='margin: 5px 0 0; font-size: 14px;'>Blood Request Approval Notice</p>
            </div>
            <div style='padding: 24px; background: #ffffff; color: #1e293b; line-height: 1.6;'>
                <p style='font-size: 16px;'>Dear <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                <div style='background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px 16px; margin: 16px 0; color: #166534;'>
                    <strong style='font-size: 15px;'>✅ Good News! Your blood request has been APPROVED.</strong><br>
                    <span>The allocated blood units have been reserved and prepared for collection.</span>
                </div>
                
                <h3 style='border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; color: #0f172a;'>Request Summary</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Request ID:</td><td><strong>#" . $id . "</strong></td></tr>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Blood Group:</td><td><strong style='color: #dc2626; font-size: 16px;'>" . htmlspecialchars($blood_type) . "</strong></td></tr>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Units Allocated:</td><td><strong>" . $units . " units</strong></td></tr>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Status:</td><td><span style='background: #22c55e; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;'>APPROVED</span></td></tr>
                </table>

                <h3 style='border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; color: #0f172a;'>Pickup Location & Contact</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Blood Bank:</td><td><strong>" . htmlspecialchars($bankName) . "</strong></td></tr>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Address:</td><td>" . htmlspecialchars($bankAddress) . "</td></tr>
                    <tr><td style='padding: 6px 0; color: #64748b;'>Contact Phone:</td><td><a href='tel:" . htmlspecialchars($bankPhone) . "' style='color: #dc2626; font-weight: bold; text-decoration: none;'>" . htmlspecialchars($bankPhone) . "</a></td></tr>
                </table>

                <div style='background: #f8fafc; border-left: 4px solid #3b82f6; padding: 12px 16px; font-size: 13px; color: #475569;'>
                    <strong>Important Instructions:</strong>
                    <ul style='margin: 6px 0 0; padding-left: 20px;'>
                        <li>Please bring an authorized patient blood requisition form and photo ID.</li>
                        <li>Carry an insulated cold container for blood transportation if required.</li>
                        <li>Contact the blood bank at <strong>" . htmlspecialchars($bankPhone) . "</strong> prior to arrival for rapid handover.</li>
                    </ul>
                </div>
            </div>
            <div style='background: #f1f5f9; padding: 14px; text-align: center; font-size: 12px; color: #64748b;'>
                BloodSync &bull; Real-Time Emergency Blood Network &bull; This is an automated notification.
            </div>
        </div>
        ";

        // 1. Email Notification Dispatch
        require_once 'config.php';
        $emailDispatch = ['status' => 'logged', 'gateway' => 'none', 'details' => 'No email on record'];
        if (!empty($recipientEmail)) {
            $emailDispatch = sendEmailNotification($recipientEmail, $emailSubject, $htmlEmail, $bankEmail);
            
            // Log to notifications table
            $logStatus = ($emailDispatch['status'] === 'sent') ? 'sent' : 'logged';
            $notifStmt = $db->prepare("INSERT INTO notifications (request_id, recipient_name, recipient_phone, recipient_email, type, subject, message, status) VALUES (?, ?, ?, ?, 'email', ?, ?, ?)");
            $plainSummary = "Request #{$id} approved for {$units} units {$blood_type} at {$bankName}.";
            $notifStmt->bind_param("issssss", $id, $recipientName, $recipientPhone, $recipientEmail, $emailSubject, $plainSummary, $logStatus);
            $notifStmt->execute();
        }

        // 2. SMS Message & WhatsApp Alert
        $smsMessage = "BloodSync ALERT: Your blood request (#{$id}) for {$units} units of {$blood_type} has been APPROVED by {$bankName}. Pickup: {$bankAddress}. Contact: {$bankPhone}. Please collect immediately.";
        
        // Dispatch real SMS via configured gateway (Fast2SMS/Twilio) or log for simulated delivery
        $smsDispatch = sendSmsNotification($recipientPhone, $smsMessage);
        $smsStatus = ($smsDispatch['status'] === 'sent') ? 'sent' : 'logged';
        
        // Log SMS to notifications table
        $smsNotifStmt = $db->prepare("INSERT INTO notifications (request_id, recipient_name, recipient_phone, recipient_email, type, subject, message, status) VALUES (?, ?, ?, ?, 'sms', 'Blood Request Approved SMS', ?, ?)");
        $smsNotifStmt->bind_param("isssss", $id, $recipientName, $recipientPhone, $recipientEmail, $smsMessage, $smsStatus);
        $smsNotifStmt->execute();

        $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . urlencode($smsMessage) : "";

        echo json_encode([
            'status' => 'success',
            'message' => "Request #{$id} approved and {$units} units of {$blood_type} deducted successfully.",
            'notification' => [
                'email_status' => $emailDispatch['status'],
                'email_gateway' => $emailDispatch['gateway'] ?? 'local_simulation',
                'email_details' => $emailDispatch['details'] ?? '',
                'recipient_email' => $recipientEmail,
                'recipient_phone' => $recipientPhone,
                'recipient_name' => $recipientName,
                'sms_status' => $smsStatus,
                'sms_message' => $smsMessage,
                'whatsapp_url' => $waUrl,
                'bank_name' => $bankName,
                'bank_phone' => $bankPhone
            ]
        ]);
        exit;

    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status' => 'error', 'message' => "Error: Database transaction failed: " . $e->getMessage()]);
        exit;
    }

} elseif ($status === 'received') {
    // Only hospitals can acknowledge receipt of their approved requests
    if ($userRole !== 'hospital') {
        echo json_encode(['status' => 'error', 'message' => "Error: Unauthorized action. Only hospitals can mark supply as received."]);
        exit;
    }

    $chkStmt = $db->prepare("SELECT id, status FROM requests WHERE id = ? AND user_id = ?");
    $chkStmt->bind_param("ii", $id, $userId);
    $chkStmt->execute();
    $res = $chkStmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => "Error: Request record not found for your hospital."]);
        exit;
    }

    $reqRow = $res->fetch_assoc();
    if ($reqRow['status'] !== 'completed') {
        echo json_encode(['status' => 'error', 'message' => "Error: Cannot mark as received until blood bank approves it."]);
        exit;
    }

    $updStmt = $db->prepare("UPDATE requests SET status = 'received' WHERE id = ?");
    $updStmt->bind_param("i", $id);
    $updStmt->execute();
    echo json_encode(['status' => 'success', 'message' => "Request marked as received."]);
    exit;
}
