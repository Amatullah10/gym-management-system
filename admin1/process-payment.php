<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    header("Location: ../index.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: payments.php"); exit();
}

$member_id     = (int)$_POST['member_id'];
$amount        = (float)$_POST['amount'];
$service       = mysqli_real_escape_string($conn, $_POST['service']);
$plan          = mysqli_real_escape_string($conn, $_POST['plan']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$payment_date  = mysqli_real_escape_string($conn, $_POST['payment_date']);
$notes         = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

if (!$member_id || !$amount || !$payment_date) {
    header("Location: payments.php?error=Missing required fields"); exit();
}

$ins = mysqli_query($conn, "INSERT INTO payments (member_id, amount, service, plan, payment_method, payment_date, status, notes)
    VALUES ($member_id, $amount, '$service', '$plan', '$payment_method', '$payment_date', 'Paid', '$notes')");

if ($ins) {
    $payment_id = mysqli_insert_id($conn);
    header("Location: ../modules/payment-receipt.php?payment_id=$payment_id");
} else {
    header("Location: payments.php?error=" . urlencode(mysqli_error($conn)));
}
exit();