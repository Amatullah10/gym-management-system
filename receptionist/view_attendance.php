<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'view-attendance';
$today = date('Y-m-d');

$filter_date   = isset($_GET['date'])   ? $_GET['date']   : $today;
$filter_member = isset($_GET['member']) ? mysqli_real_escape_string($conn, $_GET['member']) : '';

$where = "WHERE DATE(a.check_in) = '$filter_date'";
if ($filter_member) {
    $where .= " AND (m.full_name LIKE '%$filter_member%' OR m.email LIKE '%$filter_member%')";
}

$records = [];
$res = mysqli_query($conn, "SELECT a.*, m.full_name, m.email FROM attendance a JOIN members m ON a.member_id = m.id $where ORDER BY a.check_in DESC");
while ($row = mysqli_fetch_assoc($res)) { $records[] = $row; }

$total_today      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE DATE(check_in) = '$today'"))['t'];
$total_this_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE MONTH(check_in) = MONTH(NOW()) AND YEAR(check_in) = YEAR(NOW())"))['t'];
$total_all        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">View Attendance</h1>
        <p class="page-subtitle">All attendance records</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $total_today ?></h3><p>Today's Attendance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info"><h3><?= $total_this_month ?></h3><p>This Month</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-chart-bar"></i></div>
        <div class="stat-info"><h3><?= $total_all ?></h3><p>Total Records</p></div>
      </div>
    </div>

    <!-- Filters -->
    <div class="form-container" style="margin-bottom:25px;">
      <h3 style="font-size:16px; font-weight:600; margin-bottom:15px;">Filter Records</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>Date</label>
            <input type="date" name="date" value="<?= $filter_date ?>">
          </div>
          <div>
            <label>Search Member</label>
            <input type="text" name="member" value="<?= htmlspecialchars($filter_member) ?>" placeholder="Name or email...">
          </div>
        </div>
        <div style="display:flex; gap:15px; margin-top:15px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
          <a href="view_attendance.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Attendance Records — <?= date('d M Y', strtotime($filter_date)) ?> (<?= count($records) ?> records)</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Date</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Duration</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $a):
              $duration = '-';
              if ($a['check_out']) {
                $diff = strtotime($a['check_out']) - strtotime($a['check_in']);
                $duration = floor($diff/3600).'h '.floor(($diff%3600)/60).'m';
              }
              $initial = strtoupper(substr($a['full_name'], 0, 1));
            ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($a['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($a['email']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= date('d-m-Y', strtotime($a['check_in'])) ?></td>
              <td><?= date('h:i A', strtotime($a['check_in'])) ?></td>
              <td><?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '<span style="color:#aaa">Not recorded</span>' ?></td>
              <td><?= $duration ?></td>
              <td>
                <span class="status-badge <?= $a['check_out'] ? 'active' : 'pending' ?>">
                  <?= $a['check_out'] ? 'Completed' : 'Present' ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center" style="padding:30px; color:#aaa;">No attendance records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>