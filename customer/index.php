<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SESSION['role'] != 'customer') {
    header("Location: ../index.php");
    exit();
}

$page = 'dashboard';
$email = $_SESSION['email'];

$member_res = mysqli_query($conn, "SELECT * FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);

if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'>
        <h3>Account Error</h3>
        <p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p>
        <a href='../auth/logout.php'>Logout</a>
    </div>");
}
$member_id = $member['id'];

$att_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE member_id = '$member_id'");
$total_attendance = mysqli_fetch_assoc($att_res)['total'];

$month_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE member_id = '$member_id' AND MONTH(check_in) = MONTH(NOW()) AND YEAR(check_in) = YEAR(NOW())");
$month_attendance = mysqli_fetch_assoc($month_res)['total'];

$due_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE member_id = '$member_id' AND status = 'due'");
$due_count = mysqli_fetch_assoc($due_res)['total'];

$overdue_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE member_id = '$member_id' AND status = 'overdue'");
$overdue_count = mysqli_fetch_assoc($overdue_res)['total'];

$weight_res = mysqli_query($conn, "SELECT weight FROM weight_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC LIMIT 1");
$latest_weight = mysqli_fetch_assoc($weight_res)['weight'] ?? null;

$bmi_res = mysqli_query($conn, "SELECT bmi FROM bmi_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC LIMIT 1");
$latest_bmi = mysqli_fetch_assoc($bmi_res)['bmi'] ?? null;

$days_remaining = 0;
if ($member['end_date']) {
    $days_remaining = round((strtotime($member['end_date']) - strtotime(date('Y-m-d'))) / 86400);
}

$recent_att = [];
$res = mysqli_query($conn, "SELECT * FROM attendance WHERE member_id = '$member_id' ORDER BY check_in DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) { $recent_att[] = $row; }

$recent_pay = [];
$res2 = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' ORDER BY payment_date DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res2)) { $recent_pay[] = $row; }
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">My Dashboard</h1>
        <p class="page-subtitle">Welcome back, <strong><?= htmlspecialchars($member['full_name']) ?></strong>!</p>
      </div>
    </div>

    <!-- Membership Status -->
    <div class="section" style="margin-bottom:25px;">
      <h3><i class="fa-solid fa-id-card" style="color:var(--active-color); margin-right:8px;"></i>Membership Status</h3>
      <p class="section-subtitle">Your current membership details</p>
      <div class="form-row">
        <div>
          <label>Plan</label>
          <p style="font-size:15px; font-weight:600; margin:0;"><?= htmlspecialchars($member['membership_type']) ?></p>
        </div>
        <div>
          <label>Start Date</label>
          <p style="font-size:15px; font-weight:600; margin:0;"><?= $member['start_date'] ?></p>
        </div>
        <div>
          <label>End Date</label>
          <p style="font-size:15px; font-weight:600; margin:0;"><?= $member['end_date'] ?></p>
        </div>
        <div>
          <label>Status</label>
          <span class="status-badge <?= strtolower($member['membership_status']) == 'active' ? 'active' : 'expired' ?>">
            <?= strtoupper($member['membership_status']) ?>
          </span>
        </div>
        <div>
          <label>Days Remaining</label>
          <p style="font-size:15px; font-weight:600; margin:0; color:<?= $days_remaining <= 7 ? '#d32f2f' : ($days_remaining <= 30 ? '#f57c00' : '#2e7d32') ?>;">
            <?= max(0, $days_remaining) ?> days
          </p>
        </div>
      </div>
    </div>

    <!-- Stats - Only their own data -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $total_attendance ?></h3><p>Total Attendance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info"><h3><?= $month_attendance ?></h3><p>This Month Attendance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="stat-info"><h3><?= $due_count ?></h3><p>Due Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon <?= $overdue_count > 0 ? 'red' : 'green' ?>"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3><?= $overdue_count ?></h3><p>Overdue Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-weight-scale"></i></div>
        <div class="stat-info"><h3><?= $latest_weight ? $latest_weight.' kg' : 'N/A' ?></h3><p>Latest Weight</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-heart-pulse"></i></div>
        <div class="stat-info"><h3><?= $latest_bmi ? $latest_bmi : 'N/A' ?></h3><p>Latest BMI</p></div>
      </div>
    </div>

    <!-- Recent Attendance -->
    <div class="members-table-container" style="margin-bottom:25px;">
      <div class="table-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Recent Attendance</h3>
        <a href="viewattendance.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th></tr></thead>
        <tbody>
          <?php if (!empty($recent_att)): ?>
            <?php foreach ($recent_att as $i => $r):
              $duration = '-';
              if ($r['check_out']) {
                $diff = strtotime($r['check_out']) - strtotime($r['check_in']);
                $duration = floor($diff/3600).'h '.floor(($diff%3600)/60).'m';
              }
            ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= date('d-m-Y', strtotime($r['check_in'])) ?></td>
                <td><?= date('h:i A', strtotime($r['check_in'])) ?></td>
                <td><?= $r['check_out'] ? date('h:i A', strtotime($r['check_out'])) : '<span style="color:#aaa">Not recorded</span>' ?></td>
                <td><?= $duration ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center" style="padding:20px; color:#aaa;">No attendance records yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Recent Payments -->
    <div class="members-table-container">
      <div class="table-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Recent Payments</h3>
        <a href="payment-history.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Amount</th><th>Due Date</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (!empty($recent_pay)): ?>
            <?php foreach ($recent_pay as $i => $r):
              $badge = $r['status'] == 'paid' ? 'active' : ($r['status'] == 'overdue' ? 'expired' : 'pending');
            ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><strong>₹<?= number_format($r['amount'], 2) ?></strong></td>
                <td><?= $r['due_date'] ?></td>
                <td><?= $r['payment_method'] ?: '-' ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= strtoupper($r['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center" style="padding:20px; color:#aaa;">No payment records yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>