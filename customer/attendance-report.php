<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'attendance-report';
$email = $_SESSION['email'];

$member_res = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);
if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'><h3>Account Error</h3><p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p><a href='../auth/logout.php'>Logout</a></div>");
}
$member_id = $member['id'];

$monthly = [];
$res = mysqli_query($conn, "SELECT MONTHNAME(attendance_date) as month, YEAR(attendance_date) as year, COUNT(*) as total FROM attendance WHERE member_id = '$member_id' AND status='Present' GROUP BY YEAR(attendance_date), MONTH(attendance_date) ORDER BY YEAR(attendance_date) DESC, MONTH(attendance_date) DESC");
while ($row = mysqli_fetch_assoc($res)) { $monthly[] = $row; }

$total_days = array_sum(array_column($monthly, 'total'));
$best_month = !empty($monthly) ? max(array_column($monthly, 'total')) : 0;
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Attendance Reports</h1>
        <p class="page-subtitle">Monthly breakdown of your attendance</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-calendar-check"></i></div><div class="stat-info"><h3><?= $total_days ?></h3><p>Total Days Present</p></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-trophy"></i></div><div class="stat-info"><h3><?= $best_month ?></h3><p>Best Month (days)</p></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-chart-bar"></i></div><div class="stat-info"><h3><?= count($monthly) ?></h3><p>Active Months</p></div></div>
    </div>

    <div class="members-table-container">
      <div class="table-header"><h3>Monthly Attendance Report</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Month</th><th>Year</th><th>Days Present</th></tr></thead>
        <tbody>
          <?php if (!empty($monthly)): ?>
            <?php foreach ($monthly as $i => $m): ?>
              <tr><td><?= $i+1 ?></td><td><?= $m['month'] ?></td><td><?= $m['year'] ?></td><td><strong><?= $m['total'] ?> days</strong></td></tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center" style="padding:30px; color:#aaa;">No attendance records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>