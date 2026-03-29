<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: make-payment.php"); exit();
}

$member_id      = (int)$_POST['member_id'];
$amount         = (float)$_POST['amount'];
$payment_date   = mysqli_real_escape_string($conn, $_POST['payment_date']   ?? date('Y-m-d'));
$service        = mysqli_real_escape_string($conn, $_POST['service']        ?? 'Membership Fee');
$plan           = mysqli_real_escape_string($conn, $_POST['plan']           ?? 'Monthly');
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
$notes          = mysqli_real_escape_string($conn, $_POST['notes']          ?? '');
$txn_id         = mysqli_real_escape_string($conn, $_POST['transaction_id'] ?? '');

if (!$member_id || !$amount || !$payment_date) {
    header("Location: make-payment.php?error=" . urlencode("Missing required fields")); exit();
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO payments
        (member_id, amount, service, plan, payment_method, payment_date, status, notes, transaction_id)
     VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'idssssss',
    $member_id, $amount, $service, $plan, $payment_method, $payment_date, $notes, $txn_id
);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    $payment_id = mysqli_insert_id($conn);
    header("Location: ../modules/payment-receipt.php?payment_id=$payment_id");
} else {
    header("Location: make-payment.php?error=" . urlencode("Payment failed: " . mysqli_error($conn)));
}
exit();