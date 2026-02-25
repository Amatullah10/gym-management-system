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

$page = 'view-attendance';

$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

// Stats
$total_attendance = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM attendance WHERE member_id = '$member_id'");
$total_attendance = mysqli_fetch_assoc($res)['cnt'];

$this_month = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM attendance WHERE member_id = '$member_id' AND MONTH(check_in) = MONTH(NOW()) AND YEAR(check_in) = YEAR(NOW())");
$this_month = mysqli_fetch_assoc($res)['cnt'];

// Fetch records
$records = [];
$res = mysqli_query($conn, "SELECT * FROM attendance WHERE member_id = '$member_id' ORDER BY check_in DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Attendance - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .main-wrapper { margin-top: 0 !important; padding-top: 0 !important; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">View Attendance</h1>
      <p class="page-subtitle">Your gym attendance records</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $total_attendance ?></h3><p>Total Attendance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info"><h3><?= $this_month ?></h3><p>This Month</p></div>
      </div>
    </div>

    <!-- Attendance Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Attendance Records</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): 
              $duration = '-';
              if ($r['check_out']) {
                $diff = strtotime($r['check_out']) - strtotime($r['check_in']);
                $hours = floor($diff / 3600);
                $mins = floor(($diff % 3600) / 60);
                $duration = $hours.'h '.$mins.'m';
              }
            ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('Y-m-d', strtotime($r['check_in'])) ?></td>
                <td><?= date('h:i A', strtotime($r['check_in'])) ?></td>
                <td><?= $r['check_out'] ? date('h:i A', strtotime($r['check_out'])) : '<span style="color:#aaa">Not recorded</span>' ?></td>
                <td><?= $duration ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center" style="color:#aaa; padding:30px;">No attendance records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>