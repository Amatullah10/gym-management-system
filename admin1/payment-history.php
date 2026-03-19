<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    header("Location: ../index.php"); exit();
}

$page = 'payments';
$member_id = (int)($_GET['id'] ?? 0);
if (!$member_id) { header("Location: payments.php"); exit(); }

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE id=$member_id"));
if (!$member) { header("Location: payments.php"); exit(); }

// Payment stats
$total_paid    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Paid'"))['t'];
$total_due     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Due'"))['t'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Overdue'"))['t'];

// All payments
$payments = [];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id ORDER BY payment_date DESC");
while($r = mysqli_fetch_assoc($res)) $payments[] = $r;

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment History - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <a href="payments.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
    <i class="fas fa-arrow-left"></i> Back to Payments
  </a>

  <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <div>
      <h1 class="page-title">Payment History</h1>
      <p class="page-subtitle"><?= htmlspecialchars($member['full_name']) ?> — <?= htmlspecialchars($member['membership_type']) ?></p>
    </div>
    <a href="payments.php?make_payment=<?= $member_id ?>" class="btn app-btn-primary">
      <i class="fas fa-plus"></i> Make Payment
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
      <div class="stat-info"><p>Total Paid</p><h3>₹<?= number_format($total_paid) ?></h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
      <div class="stat-info"><p>Due</p><h3>₹<?= number_format($total_due) ?></h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
      <div class="stat-info"><p>Overdue</p><h3>₹<?= number_format($total_overdue) ?></h3></div>
    </div>
  </div>

  <!-- Payments Table -->
  <div class="table-container">
    <div class="table-header"><h3>All Payments</h3></div>
    <table class="modern-table">
      <thead>
        <tr><th>#</th><th>Service</th><th>Plan</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if(empty($payments)): ?>
        <tr><td colspan="8" style="text-align:center;padding:30px;color:#aaa;">No payment records found.</td></tr>
        <?php else: foreach($payments as $i=>$p):
          $badge = $p['status']==='Paid'?'active':($p['status']==='Overdue'?'expired':'pending');
        ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($p['service']) ?></td>
          <td><?= htmlspecialchars($p['plan']) ?></td>
          <td><strong>₹<?= number_format($p['amount']) ?></strong></td>
          <td><?= htmlspecialchars($p['payment_method']) ?></td>
          <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
          <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
          <td>
            <?php if($p['status']==='Paid'): ?>
            <a href="../modules/payment-receipt.php?payment_id=<?= $p['id'] ?>" class="btn-action view" title="View Receipt">
              <i class="fas fa-receipt"></i>
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>