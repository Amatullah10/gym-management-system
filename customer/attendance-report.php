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

$page = 'attendance-report';

$email = $_SESSION['email'];
$member_res = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);
$member_id = $member['id'] ?? 0;

$monthly = [];
$res = mysqli_query($conn, "SELECT MONTHNAME(check_in) as month, YEAR(check_in) as year, COUNT(*) as total FROM attendance WHERE member_id = '$member_id' GROUP BY YEAR(check_in), MONTH(check_in) ORDER BY YEAR(check_in) DESC, MONTH(check_in) DESC");
while ($row = mysqli_fetch_assoc($res)) { $monthly[] = $row; }

$total_days = array_sum(array_column($monthly, 'total'));
$best_month = !empty($monthly) ? max(array_column($monthly, 'total')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Report - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>.main-wrapper { margin-top: 0 !important; padding-top: 0 !important; }</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Attendance Reports</h1>
        <p class="page-subtitle">Monthly breakdown of your attendance</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $total_days ?></h3><p>Total Days Present</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-trophy"></i></div>
        <div class="stat-info"><h3><?= $best_month ?></h3><p>Best Month (days)</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-chart-bar"></i></div>
        <div class="stat-info"><h3><?= count($monthly) ?></h3><p>Active Months</p></div>
      </div>
    </div>

    <div class="members-table-container">
      <div class="table-header"><h3>Monthly Attendance Report</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Month</th><th>Year</th><th>Days Present</th></tr></thead>
        <tbody>
          <?php if (!empty($monthly)): ?>
            <?php foreach ($monthly as $i => $m): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $m['month'] ?></td>
                <td><?= $m['year'] ?></td>
                <td><strong><?= $m['total'] ?> days</strong></td>
              </tr>
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