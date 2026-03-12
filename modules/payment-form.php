<?php
session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['admin', 'accountant'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../dbcon.php';

$page = 'payment-list';

// Get member_id from GET or POST (POST loses GET params)
$member_id = (int)($_GET['member_id'] ?? $_POST['member_id'] ?? 0);
if (!$member_id) { header("Location: payments.php"); exit(); }

$res = mysqli_query($conn, "SELECT * FROM members WHERE id = $member_id");
if (!$res || mysqli_num_rows($res) === 0) { header("Location: payments.php"); exit(); }
$member = mysqli_fetch_assoc($res);

// Get last payment for this member
$lp = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = $member_id ORDER BY payment_date DESC LIMIT 1");
$last_payment = mysqli_fetch_assoc($lp);

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount  = (float) $_POST['amount'];
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $plan    = mysqli_real_escape_string($conn, $_POST['plan']);
    $status  = mysqli_real_escape_string($conn, $_POST['member_status']);
    $method  = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
    $txn     = mysqli_real_escape_string($conn, $_POST['transaction_id'] ?? '');

    if (!$amount || !$service || !$plan) {
        $error = 'Please fill in all required fields.';
    } else {
        $ins = mysqli_query($conn, "INSERT INTO payments (member_id, amount, service, plan, status, payment_method, transaction_id)
                                    VALUES ($member_id, $amount, '$service', '$plan', 'Paid', '$method', '$txn')");
        // Update member status
        mysqli_query($conn, "UPDATE members SET membership_status='$status' WHERE id=$member_id");

        if ($ins) {
            // Get the last inserted payment id manually
            $last = mysqli_query($conn, "SELECT id FROM payments WHERE member_id=$member_id ORDER BY id DESC LIMIT 1");
            $last_row = mysqli_fetch_assoc($last);
            $new_payment_id = $last_row['id'];
            header("Location: payment-receipt.php?payment_id=$new_payment_id");
            exit();
        } else {
            $error = 'Payment failed: ' . mysqli_error($conn);
        }
    }
}

$role = $_SESSION['role'];
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <!-- Back Link -->
    <a href="payments.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Payments
    </a>

    <h2 class="page-title" style="margin-bottom:24px;">Payment Form</h2>

    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
      <div class="section">
        <h3>Payments</h3>
      </div>

      <form method="POST" action="payment-form.php?member_id=<?= $member_id ?>">
        <input type="hidden" name="member_id" value="<?= $member_id ?>">
        <div style="display:grid;grid-template-columns:280px 1fr;gap:40px;align-items:start;">

          <!-- Left: Gym Card -->
          <div style="text-align:left;">
            <div style="background:#fde8e8;border-radius:16px;width:120px;height:120px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
              <i class="fas fa-dumbbell" style="font-size:42px;color:var(--active-color);"></i>
            </div>
            <h3 style="margin:0 0 4px;font-size:18px;font-weight:700;">Power Fitness Gym</h3>
            <p style="margin:0;color:#888;font-size:13px;">123 Main Street, Mumbai</p>
            <p style="margin:2px 0;color:var(--active-color);font-size:13px;">Tel: 022-1234-5678</p>
            <p style="margin:0;color:var(--active-color);font-size:13px;">Email: info@powerfitness.in</p>
          </div>

          <!-- Right: Form Fields -->
          <div>
            <!-- Member Name (readonly) -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Member's Fullname:</label>
              <span style="font-weight:600;"><?= htmlspecialchars($member['full_name']) ?></span>
            </div>

            <!-- Service -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Service:</label>
              <input type="text" name="service" value="<?= htmlspecialchars($last_payment['service'] ?? 'Fitness') ?>" style="max-width:300px;" required>
            </div>

            <!-- Amount -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Amount Per Month:</label>
              <input type="number" name="amount" value="<?= $last_payment['amount'] ?? 2500 ?>" style="max-width:300px;" required>
            </div>

            <!-- Plan -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Plan:</label>
              <select name="plan" style="max-width:300px;" required>
                <option value="">-- Select Plan --</option>
                <option value="Monthly"   <?= ($last_payment['plan'] ?? '') === 'Monthly'   ? 'selected' : '' ?>>Monthly</option>
                <option value="Quarterly" <?= ($last_payment['plan'] ?? '') === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                <option value="Yearly"    <?= ($last_payment['plan'] ?? '') === 'Yearly'    ? 'selected' : '' ?>>Yearly</option>
              </select>
            </div>

            <!-- Payment Method -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Payment Method:</label>
              <select name="payment_method" style="max-width:300px;">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="UPI">UPI</option>
                <option value="Online">Online</option>
              </select>
            </div>

            <!-- Transaction ID (optional) -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Transaction ID <span style="font-weight:400;color:#aaa;">(optional)</span>:</label>
              <input type="text" name="transaction_id" placeholder="e.g. UPI12345" style="max-width:300px;">
            </div>

            <!-- Member Status -->
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Member's Status:</label>
              <select name="member_status" style="max-width:300px;">
                <option value="Active"   <?= $member['membership_status'] === 'Active'   ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $member['membership_status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="Expired"  <?= $member['membership_status'] === 'Expired'  ? 'selected' : '' ?>>Expired</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div style="text-align:center;margin-top:30px;">
          <button type="submit" class="btn" style="background:#2e7d32;color:#fff;padding:13px 40px;border-radius:10px;font-size:15px;font-weight:700;border:none;cursor:pointer;">
            Make Payment
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>