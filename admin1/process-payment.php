<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    header("Location: ../index.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: payments.php"); exit();
}

$member_id      = (int)$_POST['member_id'];
$amount         = (float)$_POST['amount'];
$payment_date   = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? '');
$service        = mysqli_real_escape_string($conn, $_POST['service']         ?? '');
$plan           = mysqli_real_escape_string($conn, $_POST['plan']            ?? '');
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']  ?? '');
$notes          = mysqli_real_escape_string($conn, $_POST['notes']           ?? '');
// BUG FIX #2: transaction_id was previously crammed into the payment_method
// column as "UPI (UPIXXXXX)". Now saved to its own column correctly.
$txn_id         = mysqli_real_escape_string($conn, $_POST['transaction_id']  ?? '');

if (!$member_id || !$amount || !$payment_date) {
    header("Location: payments.php?error=" . urlencode("Missing required fields")); exit();
}

// BUG FIX #2 (cont) + BUG FIX #6: Use prepared statement to prevent SQL injection.
// Original query used raw string interpolation — replaced with bound parameters.
$stmt = mysqli_prepare($conn,
    "INSERT INTO payments
        (member_id, amount, service, plan, payment_method, payment_date, status, notes, transaction_id)
     VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?, ?)"
);

mysqli_stmt_bind_param($stmt, 'idssssss',
    $member_id,
    $amount,
    $service,
    $plan,
    $payment_method,
    $payment_date,
    $notes,
    $txn_id
);

$ins = mysqli_stmt_execute($stmt);

if ($ins) {
    $payment_id = mysqli_insert_id($conn);
    // BUG FIX #7 (partial): receipt lives in modules/, so path is correct from admin1/
    header("Location: ../modules/payment-receipt.php?payment_id=$payment_id");
} else {
    header("Location: payments.php?error=" . urlencode(mysqli_error($conn)));
}
exit();