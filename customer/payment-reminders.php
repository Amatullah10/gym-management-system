<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'payment-reminders';
$email = $_SESSION['email'];

$member_res = mysqli_query($conn, "SELECT id, full_name, membership_type, end_date FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);
if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'><h3>Account Error</h3><p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p><a href='../auth/logout.php'>Logout</a></div>");
}
$member_id = $member['id'];

$upcoming = [];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'due' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res)) { $upcoming[] = $row; }

$overdue = [];
$res2 = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'overdue' ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res2)) { $overdue[] = $row; }

$days_to_expiry = 0;
if ($member['end_date']) {
    $days_to_expiry = round((strtotime($member['end_date']) - strtotime(date('Y-m-d'))) / 86400);
}
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Payment Reminders</h1>
        <p class="page-subtitle">Stay on top of your payments</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="stat-info"><h3><?= count($overdue) ?></h3><p>Overdue Payments</p></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-bell"></i></div><div class="stat-info"><h3><?= count($upcoming) ?></h3><p>Due This Month</p></div></div>
      <div class="stat-card">
        <div class="stat-icon <?= $days_to_expiry <= 7 ? 'red' : ($days_to_expiry <= 30 ? 'orange' : 'green') ?>"><i class="fa-solid fa-id-card"></i></div>
        <div class="stat-info"><h3><?= max(0, $days_to_expiry) ?> days</h3><p>Membership Expiry</p></div>
      </div>
    </div>

    <!-- Membership Status -->
    <div class="members-table-container" style="margin-bottom:20px;">
      <div class="table-header"><h3>Membership Status</h3></div>
      <table class="members-table">
        <thead><tr><th>Plan</th><th>Expiry Date</th><th>Days Remaining</th><th>Status</th></tr></thead>
        <tbody>
          <tr>
            <td><?= htmlspecialchars($member['membership_type']) ?></td>
            <td><?= $member['end_date'] ?: 'N/A' ?></td>
            <td><strong><?= max(0, $days_to_expiry) ?> days</strong></td>
            <td><span class="status-badge <?= $days_to_expiry <= 0 ? 'expired' : ($days_to_expiry <= 30 ? 'pending' : 'active') ?>"><?= $days_to_expiry <= 0 ? 'EXPIRED' : ($days_to_expiry <= 30 ? 'EXPIRING SOON' : 'ACTIVE') ?></span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Overdue Payments -->
    <?php if (!empty($overdue)): ?>
    <div class="members-table-container" style="margin-bottom:20px;">
      <div class="table-header"><h3>⚠️ Overdue Payments</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Amount</th><th>Due Date</th><th>Days Overdue</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($overdue as $i => $r):
            $days_overdue = max(0, round((strtotime(date('Y-m-d')) - strtotime($r['due_date'])) / 86400));
          ?>
            <tr><td><?= $i+1 ?></td><td><strong>₹<?= number_format($r['amount'],2) ?></strong></td><td><?= $r['due_date'] ?></td><td><strong style="color:#d32f2f;"><?= $days_overdue ?> days</strong></td><td><span class="status-badge expired">OVERDUE</span></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Upcoming Payments -->
    <?php if (!empty($upcoming)): ?>
    <div class="members-table-container">
      <div class="table-header"><h3>🔔 Upcoming Due Payments</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Amount</th><th>Due Date</th><th>Days Left</th><th>Notes</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($upcoming as $i => $r):
            $days_left = max(0, round((strtotime($r['due_date']) - strtotime(date('Y-m-d'))) / 86400));
          ?>
            <tr><td><?= $i+1 ?></td><td><strong>₹<?= number_format($r['amount'],2) ?></strong></td><td><?= $r['due_date'] ?></td><td><?= $days_left ?> days</td><td><?= htmlspecialchars($r['notes']) ?: '-' ?></td><td><span class="status-badge pending">DUE SOON</span></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <?php if (empty($overdue) && empty($upcoming)): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> 🎉 No pending reminders! You are all up to date.</div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>