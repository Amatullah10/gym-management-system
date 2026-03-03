<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'overdue-payments';
$email = $_SESSION['email'];

$member_res = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);
if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'><h3>Account Error</h3><p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p><a href='../auth/logout.php'>Logout</a></div>");
}
$member_id = $member['id'];

$records = [];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'overdue' ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res)) { $records[] = $row; }
$total_overdue = array_sum(array_column($records, 'amount'));
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Overdue Payments</h1>
        <p class="page-subtitle">Payments that are past due date</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="stat-info"><h3><?= count($records) ?></h3><p>Overdue Payments</p></div></div>
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-indian-rupee-sign"></i></div><div class="stat-info"><h3>₹<?= number_format($total_overdue, 2) ?></h3><p>Total Overdue Amount</p></div></div>
    </div>

    <div class="members-table-container">
      <div class="table-header"><h3>Overdue Payments</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Amount</th><th>Due Date</th><th>Days Overdue</th><th>Notes</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r):
              $days_overdue = max(0, round((strtotime(date('Y-m-d')) - strtotime($r['due_date'])) / 86400));
            ?>
              <tr><td><?= $i+1 ?></td><td><strong>₹<?= number_format($r['amount'],2) ?></strong></td><td><?= $r['due_date'] ?></td><td><strong style="color:#d32f2f;"><?= $days_overdue ?> days</strong></td><td><?= htmlspecialchars($r['notes']) ?: '-' ?></td><td><span class="status-badge expired">OVERDUE</span></td></tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center" style="padding:30px; color:#aaa;">🎉 No overdue payments! Great job.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>