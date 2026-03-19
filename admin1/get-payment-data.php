<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$member_id = (int)($_GET['id'] ?? 0);
if (!$member_id) {
    echo json_encode(['error' => 'Invalid member ID']); exit();
}

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE id=$member_id"));
if (!$member) {
    echo json_encode(['error' => 'Member not found']); exit();
}

// Last payment
$last_payment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id ORDER BY payment_date DESC LIMIT 1"));

// Total paid
$total_paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Paid'"))['t'];

// Pending amount
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status IN ('Due','Overdue')"))['t'];

// Membership fee based on type
$fee = 799;
if (strpos($member['membership_type'], 'Premium') !== false) $fee = 1299;
elseif (strpos($member['membership_type'], 'Standard') !== false) $fee = 999;

echo json_encode([
    'id'              => $member['id'],
    'full_name'       => $member['full_name'],
    'email'           => $member['email'],
    'membership_type' => $member['membership_type'],
    'membership_fee'  => $fee,
    'total_paid'      => $total_paid,
    'pending'         => $pending,
    'last_payment'    => $last_payment ? $last_payment['payment_date'] : null,
    'last_service'    => $last_payment ? $last_payment['service'] : 'Membership Fee',
    'last_plan'       => $last_payment ? $last_payment['plan'] : 'Monthly',
]);