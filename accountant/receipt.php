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
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Page Header -->
    <div class="page-header flex justify-between align-center">
      <div>
        <h1 class="page-title">Payment Receipt</h1>
        <p class="page-subtitle">Receipt #<?= str_pad($receipt['id'], 6, '0', STR_PAD_LEFT) ?> — <?= date('d M Y', strtotime($receipt['payment_date'])) ?></p>
      </div>
      <div class="action-buttons">
        <a href="member-collections.php" class="btn app-btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn app-btn-primary">
          <i class="fa-solid fa-print"></i> Print Receipt
        </button>
      </div>
    </div>

    <!-- Receipt Container -->
    <div class="members-table-container mb-20" id="receipt-print">

      <!-- Header Band -->
      <div class="table-header flex justify-between align-center">
        <div class="member-cell">
          <div class="member-avatar">
            <i class="fa-solid fa-dumbbell"></i>
          </div>
          <div class="member-info">
            <span class="name">NextGen Fitness</span>
            <span class="joined">Official Payment Receipt</span>
          </div>
        </div>
        <div class="text-right">
          <span class="status-badge active">
            <i class="fa-solid fa-circle-check"></i> PAID
          </span>
          <p class="page-subtitle mt-10">Receipt #<?= str_pad($receipt['id'], 6, '0', STR_PAD_LEFT) ?></p>
          <p class="page-subtitle"><?= date('d M Y, h:i A', strtotime($receipt['payment_date'])) ?></p>
        </div>
      </div>

      <!-- Member Details Section -->
      <div class="main-content">
        <div class="section">
          <h3><i class="fa-solid fa-user"></i> Member Details</h3>
          <p class="section-subtitle">Personal information of the member</p>
          <div class="form-row">
            <div>
              <label>Member Name</label>
              <p><?= htmlspecialchars($receipt['full_name']) ?></p>
            </div>
            <div>
              <label>Member ID</label>
              <p>#<?= str_pad($receipt['member_code'], 4, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div>
              <label>Email</label>
              <p><?= htmlspecialchars($receipt['email']) ?></p>
            </div>
            <div>
              <label>Phone</label>
              <p><?= htmlspecialchars($receipt['phone']) ?></p>
            </div>
            <div>
              <label>Membership Type</label>
              <p><?= htmlspecialchars($receipt['membership_type']) ?></p>
            </div>
            <div>
              <label>Membership Status</label>
              <p>
                <span class="status-badge <?= strtolower($receipt['membership_status']) ?>">
                  <?= htmlspecialchars($receipt['membership_status']) ?>
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Payment Details Section -->
        <div class="section">
          <h3><i class="fa-solid fa-receipt"></i> Payment Details</h3>
          <p class="section-subtitle">Transaction and payment information</p>
          <div class="form-row">
            <div>
              <label>Service</label>
              <p><?= htmlspecialchars($receipt['service']) ?></p>
            </div>
            <div>
              <label>Plan</label>
              <p>
                <span class="plan-badge <?= strtolower($receipt['plan']) ?>">
                  <?= htmlspecialchars($receipt['plan']) ?>
                </span>
              </p>
            </div>
            <div>
              <label>Payment Method</label>
              <p>
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
              </p>
            </div>
            <div>
              <label>Payment Date</label>
              <p class="date-display"><?= date('d M Y, h:i A', strtotime($receipt['payment_date'])) ?></p>
            </div>
            <?php if (!empty($receipt['transaction_id'])): ?>
            <div>
              <label>Transaction ID</label>
              <p><?= htmlspecialchars($receipt['transaction_id']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($receipt['notes'])): ?>
            <div>
              <label>Notes</label>
              <p><?= htmlspecialchars($receipt['notes']) ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Total Amount -->
        <div class="section">
          <div class="flex justify-between align-center">
            <h3 class="page-title">Total Amount Paid</h3>
            <h3 class="page-title">₹<?= number_format($receipt['amount'], 2) ?></h3>
          </div>
        </div>

        <!-- Footer Note -->
        <div class="app-alert app-alert-success">
          <i class="fa-solid fa-circle-check"></i>
          Payment verified and confirmed by NextGen Fitness. Thank you for your payment. This is a computer-generated receipt.
        </div>

      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>