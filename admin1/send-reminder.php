<?php
session_start();
require_once '../dbcon.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']); exit();
}

$member_id = (int)($_POST['member_id'] ?? 0);
if (!$member_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']); exit();
}

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE id=$member_id"));
if (!$member) {
    echo json_encode(['success' => false, 'message' => 'Member not found']); exit();
}

// Get pending amount
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status IN ('Due','Overdue')"))['t'];

// Log reminder in payment_reminders table
$email = mysqli_real_escape_string($conn, $member['email']);
$msg   = mysqli_real_escape_string($conn, "Payment reminder sent for pending amount of ₹" . number_format($pending));
$by    = mysqli_real_escape_string($conn, $_SESSION['email']);

// Check if payment_reminders table exists, if so insert
$check = mysqli_query($conn, "SHOW TABLES LIKE 'payment_reminders'");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "INSERT INTO payment_reminders (member_id, message, sent_by, sent_date)
        VALUES ($member_id, '$msg', '$by', NOW())");
}

// Try to send email if mailer exists
$sent = false;
$mailer_path = __DIR__ . '/../auth/mailer.php';
if (file_exists($mailer_path)) {
    require_once $mailer_path;
    $body = "
    <div style='font-family:Inter,sans-serif;max-width:500px;margin:0 auto;padding:30px;'>
        <h2 style='color:#941614;'>NextGen Fitness</h2>
        <p>Dear <strong>{$member['full_name']}</strong>,</p>
        <p>This is a reminder that you have a pending payment of <strong>₹" . number_format($pending) . "</strong>.</p>
        <p>Please visit the gym or contact us to clear your dues at the earliest.</p>
        <p style='color:#999;font-size:12px;'>NextGen Fitness Management</p>
    </div>";
    $sent = sendMail($member['email'], $member['full_name'], 'Payment Reminder - NextGen Fitness', $body);
}

echo json_encode([
    'success' => true,
    'message' => $sent
        ? "Reminder email sent to {$member['email']}"
        : "Reminder logged successfully (email not configured)",
    'member_name' => $member['full_name'],
    'pending' => $pending
]);