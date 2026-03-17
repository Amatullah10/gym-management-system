<?php
session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['admin', 'accountant'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../dbcon.php';

$page = 'payment-list';

$payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
if (!$payment_id) { header("Location: payments.php"); exit(); }

$sql = "SELECT p.*, m.full_name, m.email, m.phone
        FROM payments p
        JOIN members m ON m.id = p.member_id
        WHERE p.id = $payment_id";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) === 0) { header("Location: payments.php"); exit(); }
$p = mysqli_fetch_assoc($res);

// Invoice number: GMS_ + random 7 digits based on id
$invoice = 'GMS_' . str_pad($payment_id * 9 + 1000000, 7, '0', STR_PAD_LEFT);

// Calculate valid until date from payment_date + plan duration
$plan_months  = ['Monthly' => 1, 'Quarterly' => 3, 'Yearly' => 12];
$months_to_add = $plan_months[$p['plan']] ?? 1;
$valid_until  = date('d F Y', strtotime($p['payment_date'] . " +{$months_to_add} months"));

$role = $_SESSION['role'];
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<style>
  @media print {
    .sidebar, .top-header, .no-print { display: none !important; }
    .main-wrapper { margin: 0 !important; }
    .receipt-card { box-shadow: none !important; border: 1px solid #ddd; }
  }
</style>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Back Link -->
    <a href="payments.php" class="no-print" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:30px;">
      <i class="fas fa-arrow-left"></i> Back to Payments
    </a>

    <!-- Receipt Card -->
    <div class="receipt-card" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;padding:40px;box-shadow:0 4px 24px rgba(0,0,0,0.1);">

      <!-- Logo -->
      <div style="text-align:center;margin-bottom:20px;">
        <div style="width:70px;height:70px;border-radius:50%;border:2px solid var(--active-color);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
          <i class="fas fa-dumbbell" style="font-size:28px;color:var(--active-color);"></i>
        </div>
        <h2 style="margin:0;font-size:22px;font-weight:700;">Payment Receipt</h2>
      </div>

      <!-- Invoice info -->
      <div style="display:flex;justify-content:space-between;font-size:13px;color:#555;margin-bottom:20px;">
        <div>
          <div>Invoice #<?= $invoice ?></div>
          <div>NextGen FItness GYM</div>
          <div>123 Main Street, Mumbai</div>
          <div>Tel: 022-1234-5678 | Email: nextgenfitness1407@gmail.com</div>
        </div>
        <div style="text-align:right;">
          Last Payment: <?= date('Y-m-d', strtotime($p['payment_date'])) ?>
        </div>
      </div>

      <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">

      <!-- Member -->
      <div style="text-align:center;margin-bottom:20px;">
        <div style="font-size:16px;font-weight:700;">Member: <?= htmlspecialchars($p['full_name']) ?></div>
        <div style="color:var(--active-color);font-size:14px;margin-top:4px;">
          Paid On: <?= date('d F Y - h:i a', strtotime($p['payment_date'])) ?>
        </div>
      </div>

      <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">

      <!-- Service table -->
      <table style="width:100%;font-size:14px;border-collapse:collapse;">
        <thead>
          <tr style="color:#999;">
            <th style="text-align:left;padding:8px 0;font-weight:500;">Service Taken</th>
            <th style="text-align:right;padding:8px 0;font-weight:500;">Valid Upto</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:10px 0;border-top:1px solid #f0f0f0;"><?= htmlspecialchars($p['service']) ?></td>
            <td style="padding:10px 0;border-top:1px solid #f0f0f0;text-align:right;"><?= htmlspecialchars($p['plan']) ?> &nbsp;<strong>(<?= $valid_until ?>)</strong></td>
          </tr>
          <tr>
            <td style="padding:10px 0;border-top:1px solid #f0f0f0;">Charge Per Month</td>
            <td style="padding:10px 0;border-top:1px solid #f0f0f0;text-align:right;">₹<?= number_format($p['amount'], 0) ?></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td style="padding:14px 0;border-top:2px solid #222;font-weight:700;">Total Amount</td>
            <td style="padding:14px 0;border-top:2px solid #222;text-align:right;font-weight:700;">₹<?= number_format($p['amount'], 0) ?></td>
          </tr>
        </tfoot>
      </table>

      <!-- Thank you note -->
      <p style="text-align:center;color:#aaa;font-size:13px;font-style:italic;margin-top:20px;">
        We sincerely appreciate your promptness regarding all payments from your side.
      </p>
    </div>

    <!-- Print Button -->
    <div class="no-print" style="text-align:center;margin-top:24px;">
      <button onclick="window.print()" style="background:var(--active-color);color:#fff;border:none;padding:12px 30px;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
        <i class="fas fa-print"></i> Print
      </button>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>