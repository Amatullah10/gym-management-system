<?php
session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['admin', 'accountant'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../dbcon.php';

$page = 'payment-list';

// Load gym settings
$gym_settings = [];
$settings_res = mysqli_query($conn, "SELECT setting_key, setting_value FROM gym_settings");
if ($settings_res) {
    while ($row = mysqli_fetch_assoc($settings_res)) {
        $gym_settings[$row['setting_key']] = $row['setting_value'];
    }
}
$gym_name    = $gym_settings['gym_name']    ?? 'NextGen Fitness GYM';
$gym_address = $gym_settings['gym_address'] ?? '123 Main Street, Mumbai';
$gym_phone   = $gym_settings['gym_phone']   ?? '022-1234-5678';
$gym_email   = $gym_settings['gym_email']   ?? 'nextgenfitness1407@gmail.com';

// Get member_id from GET or POST
$member_id = (int)($_GET['member_id'] ?? $_POST['member_id'] ?? 0);
if (!$member_id) { header("Location: payments.php"); exit(); }

$res = mysqli_query($conn, "SELECT * FROM members WHERE id = $member_id");
if (!$res || mysqli_num_rows($res) === 0) { header("Location: payments.php"); exit(); }
$member = mysqli_fetch_assoc($res);

$lp = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = $member_id ORDER BY payment_date DESC LIMIT 1");
$last_payment = mysqli_fetch_assoc($lp);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount     = (float) $_POST['amount'];
    $service    = mysqli_real_escape_string($conn, $_POST['service']);
    $plan       = mysqli_real_escape_string($conn, $_POST['plan']);
    $pay_status = mysqli_real_escape_string($conn, $_POST['payment_status'] ?? 'Paid');
    $mem_status = mysqli_real_escape_string($conn, $_POST['member_status']);
    $method     = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
    $txn        = mysqli_real_escape_string($conn, $_POST['transaction_id'] ?? '');

    if (!$amount || !$service || !$plan) {
        $error = 'Please fill in all required fields.';
    } else {
        $ins = mysqli_query($conn, "INSERT INTO payments (member_id, amount, service, plan, status, payment_method, transaction_id)
                                    VALUES ($member_id, $amount, '$service', '$plan', '$pay_status', '$method', '$txn')");
        mysqli_query($conn, "UPDATE members SET membership_status='$mem_status' WHERE id=$member_id");
        if ($ins) {
            $last     = mysqli_query($conn, "SELECT id FROM payments WHERE member_id=$member_id ORDER BY id DESC LIMIT 1");
            $last_row = mysqli_fetch_assoc($last);
            header("Location: payment-receipt.php?payment_id=" . $last_row['id']);
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

    <a href="payments.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Payments
    </a>

    <h2 class="page-title" style="margin-bottom:24px;">Payment Form</h2>

    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
      <div class="section"><h3>Payments</h3></div>

      <form method="POST" action="payment-form.php?member_id=<?= $member_id ?>">
        <input type="hidden" name="member_id" value="<?= $member_id ?>">

        <div style="display:grid;grid-template-columns:280px 1fr;gap:40px;align-items:start;">

          <!-- LEFT: Gym card with logo -->
          <div style="text-align:left;">
            <?php
              $logo_full = __DIR__ . '/../assets/logo.png';
            ?>
            <?php if (file_exists($logo_full)): ?>
              <img src="../assets/logo.png"
                   alt="<?= htmlspecialchars($gym_name) ?>"
                   style="width:120px;height:120px;object-fit:contain;border-radius:16px;border:2px solid #fde8e8;margin-bottom:16px;display:block;">
            <?php else: ?>
              <div style="background:#fde8e8;border-radius:16px;width:120px;height:120px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <i class="fas fa-dumbbell" style="font-size:42px;color:var(--active-color);"></i>
              </div>
            <?php endif; ?>

            <h3 style="margin:0 0 4px;font-size:18px;font-weight:700;"><?= htmlspecialchars($gym_name) ?></h3>
            <p style="margin:0;color:#888;font-size:13px;"><?= htmlspecialchars($gym_address) ?></p>
            <p style="margin:2px 0;color:var(--active-color);font-size:13px;">Tel: <?= htmlspecialchars($gym_phone) ?></p>
            <p style="margin:0;color:var(--active-color);font-size:13px;">Email: <?= htmlspecialchars($gym_email) ?></p>
          </div>

          <!-- RIGHT: form fields -->
          <div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Member's Fullname:</label>
              <span style="font-weight:600;"><?= htmlspecialchars($member['full_name']) ?></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Service:</label>
              <input type="text" name="service" value="<?= htmlspecialchars($last_payment['service'] ?? 'Fitness') ?>" style="max-width:300px;" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Amount Per Month:</label>
              <input type="number" name="amount" value="<?= $last_payment['amount'] ?? 2500 ?>" style="max-width:300px;" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Plan:</label>
              <select name="plan" style="max-width:300px;" required>
                <option value="">-- Select Plan --</option>
                <option value="Monthly"   <?= ($last_payment['plan'] ?? '') === 'Monthly'   ? 'selected' : '' ?>>Monthly</option>
                <option value="Quarterly" <?= ($last_payment['plan'] ?? '') === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                <option value="Yearly"    <?= ($last_payment['plan'] ?? '') === 'Yearly'    ? 'selected' : '' ?>>Yearly</option>
              </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Payment Method:</label>
              <select name="payment_method" style="max-width:300px;">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="UPI">UPI</option>
                <option value="Online">Online</option>
              </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Transaction ID <span style="font-weight:400;color:#aaa;">(optional)</span>:</label>
              <input type="text" name="transaction_id" placeholder="e.g. UPI12345" style="max-width:300px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0;">
              <label style="color:var(--active-color);font-weight:600;margin:0;">Payment Status:</label>
              <select name="payment_status" style="max-width:300px;" required>
                <option value="Paid">Paid</option>
                <option value="Due">Due</option>
                <option value="Overdue">Overdue</option>
              </select>
            </div>
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