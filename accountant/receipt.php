<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'member-collections';
$page_title = 'Payment Receipt - Gym Management';

if (empty($_GET['id'])) { header("Location: member-collections.php"); exit(); }
$pid = (int)$_GET['id'];

$res = mysqli_query($conn, "
    SELECT p.*, m.full_name, m.email, m.phone, m.membership_type, m.membership_status,
           m.id AS member_code
    FROM payments p
    JOIN members m ON p.member_id = m.id
    WHERE p.id = $pid AND p.status = 'Paid'
");
$receipt = mysqli_fetch_assoc($res);

if (!$receipt) { header("Location: member-collections.php"); exit(); }

// Invoice number
$invoice = 'GMS_' . str_pad($pid * 9 + 1000000, 7, '0', STR_PAD_LEFT);

// Valid until based on plan
$plan_months   = ['Monthly' => 1, 'Quarterly' => 3, 'Yearly' => 12];
$months_to_add = $plan_months[$receipt['plan']] ?? 1;
$valid_until   = date('d F Y', strtotime($receipt['payment_date'] . " +{$months_to_add} months"));

include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Back & Print — hidden on print -->
    <div class="page-header flex justify-between align-center no-print">
      <div>
        <h1 class="page-title">Payment Receipt</h1>
        <p class="page-subtitle">Invoice #<?= $invoice ?></p>
      </div>
      <div class="action-buttons">
        <a href="member-collections.php" class="btn app-btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn app-btn-primary">
          <i class="fa-solid fa-print"></i> Print
        </button>
      </div>
    </div>

    <!-- Receipt Card -->
    <div class="members-table-container mb-20" id="receipt-print">
      <div class="main-content">

        <!-- Gym Logo & Title -->
        <div class="text-center mb-20">
          <div class="member-avatar" style="margin:0 auto 14px;">
            <i class="fa-solid fa-dumbbell"></i>
          </div>
          <h2>Payment Receipt</h2>
        </div>

        <!-- Invoice Info -->
        <div class="flex justify-between align-center mb-20">
          <div>
            <p class="page-subtitle">Invoice #<?= $invoice ?></p>
            <p class="page-subtitle">NextGen Fitness GYM</p>
            <p class="page-subtitle">123 Main Street, Mumbai</p>
            <p class="page-subtitle">Tel: 022-1234-5678 | Email: nextgenfitness1407@gmail.com</p>
          </div>
          <div class="text-right">
            <p class="page-subtitle">Last Payment: <?= date('Y-m-d', strtotime($receipt['payment_date'])) ?></p>
          </div>
        </div>

        <hr>

        <!-- Member Name & Paid On -->
        <div class="text-center mb-20 mt-20">
          <p class="page-title">Member: <?= htmlspecialchars($receipt['full_name']) ?></p>
          <p class="page-subtitle">Paid On: <?= date('d F Y - h:i a', strtotime($receipt['payment_date'])) ?></p>
        </div>

        <hr>

        <!-- Service Table -->
        <table class="members-table mt-20">
          <thead>
            <tr>
              <th>Service Taken</th>
              <th>Payment Method</th>
              <?php if (!empty($receipt['transaction_id'])): ?>
              <th>Transaction ID</th>
              <?php endif; ?>
              <th class="text-right">Valid Upto</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= htmlspecialchars($receipt['service']) ?></td>
              <td>
                <?php
                $method_icons = [
                    'Cash'   => 'fa-money-bill-wave',
                    'Card'   => 'fa-credit-card',
                    'UPI'    => 'fa-mobile-screen',
                    'Online' => 'fa-globe',
                ];
                $icon = $method_icons[$receipt['payment_method']] ?? 'fa-circle-dot';
                ?>
                <i class="fa-solid <?= $icon ?>"></i>
                <?= htmlspecialchars($receipt['payment_method']) ?>
              </td>
              <?php if (!empty($receipt['transaction_id'])): ?>
              <td><?= htmlspecialchars($receipt['transaction_id']) ?></td>
              <?php endif; ?>
              <td class="text-right">
                <span class="plan-badge <?= strtolower($receipt['plan']) ?>"><?= htmlspecialchars($receipt['plan']) ?></span>
                &nbsp;<strong>(<?= $valid_until ?>)</strong>
              </td>
            </tr>
            <tr>
              <td>Charge Per Month</td>
              <td></td>
              <?php if (!empty($receipt['transaction_id'])): ?>
              <td></td>
              <?php endif; ?>
              <td class="text-right">₹<?= number_format($receipt['amount'], 0) ?></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td><strong>Total Amount</strong></td>
              <td></td>
              <?php if (!empty($receipt['transaction_id'])): ?>
              <td></td>
              <?php endif; ?>
              <td class="text-right"><strong>₹<?= number_format($receipt['amount'], 0) ?></strong></td>
            </tr>
          </tfoot>
        </table>

        <?php if (!empty($receipt['notes'])): ?>
        <div class="app-alert app-alert-warning mt-20">
          <i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($receipt['notes']) ?>
        </div>
        <?php endif; ?>

        <!-- Thank You Note -->
        <div class="app-alert app-alert-success mt-20">
          <i class="fa-solid fa-circle-check"></i>
          We sincerely appreciate your promptness regarding all payments from your side.
        </div>

      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>