<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page = 'record-payment';
$page_title = 'Record Payment - Gym Management';
$success = '';
$error   = '';

// Fetch members for dropdown
$members = [];
$res = mysqli_query($conn, "SELECT id, full_name, email FROM members ORDER BY full_name ASC");
while ($row = mysqli_fetch_assoc($res)) { $members[] = $row; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id      = (int)$_POST['member_id'];
    $amount         = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_date   = $_POST['payment_date'];
    $service        = mysqli_real_escape_string($conn, $_POST['service']);
    $plan           = $_POST['plan'];
    $status         = $_POST['status'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    $notes          = mysqli_real_escape_string($conn, $_POST['notes']);

    if (!$member_id || empty($amount) || empty($payment_date)) {
        $error = "Member, amount and date are required!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO payments (member_id, amount, payment_date, service, plan, status, payment_method, transaction_id, notes) 
            VALUES ('$member_id', '$amount', '$payment_date', '$service', '$plan', '$status', '$payment_method', '$transaction_id', '$notes')");
        if ($insert) {
            $success = "Payment recorded successfully!";
        } else {
            $error = "Failed to record payment! Error: " . mysqli_error($conn);
        }
    }
}
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Record Payment</h1>
        <p class="page-subtitle">Add a new payment record</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">

        <div class="section">
          <h3>Payment Details</h3>
          <p class="section-subtitle">Fill in the payment information below</p>

          <div class="form-row">
            <div>
              <label>Member</label>
              <select name="member_id" required>
                <option value="">-- Select Member --</option>
                <?php foreach ($members as $m): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Amount (₹)</label>
              <input type="number" name="amount" min="0" step="0.01" placeholder="e.g. 2500" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Payment Date</label>
              <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
              <label>Service</label>
              <input type="text" name="service" placeholder="e.g. Fitness, Personal Training">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Plan</label>
              <select name="plan">
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Yearly">Yearly</option>
              </select>
            </div>
            <div>
              <label>Payment Method</label>
              <select name="payment_method">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="UPI">UPI</option>
                <option value="Online">Online</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Status</label>
              <select name="status">
                <option value="Paid">Paid</option>
                <option value="Due">Due</option>
                <option value="Overdue">Overdue</option>
              </select>
            </div>
            <div>
              <label>Transaction ID</label>
              <input type="text" name="transaction_id" placeholder="Optional">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Notes</label>
              <textarea name="notes" placeholder="Any additional notes..."></textarea>
            </div>
          </div>
        </div>

        <div class="flex gap-3 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Record Payment</button>
          <a href="accountant_dashboard.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>