<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once 'db.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT n.id, n.request_id, n.recipient_name, n.recipient_phone, n.recipient_email, 
           n.type, n.subject, n.message, n.status,
           r.blood_type, r.units, r.status as request_status,
           c.name as center_name, c.phone as center_phone, c.address as center_address, c.city as center_city,
           DATE_FORMAT(n.created_at, '%b %d, %Y %h:%i %p') as formatted_date
    FROM notifications n
    JOIN requests r ON n.request_id = r.id
    LEFT JOIN users c ON r.center_id = c.id
    WHERE r.user_id = ?
    ORDER BY n.id DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

$notifications = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = $row;
    }
}
echo json_encode($notifications);
exit;
