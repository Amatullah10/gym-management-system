<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'dashboard';
$today = date('Y-m-d');
$current_month = date('Y-m');

$total_members   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members"))['t'];
$active_staff    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM staff WHERE status='Active'"))['t'];
$today_checkins  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE attendance_date='$today' AND status='Present'"))['t'];
$new_this_month  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE DATE_FORMAT(created_at,'%Y-%m')='$current_month'"))['t'];
$total_active    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'"))['t'];
$monthly_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'"))['t'];
$pending_dues    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE status IN ('Due','Overdue')"))['t'];

$premium_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Premium%'"))['t'];
$basic_count    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Basic%'"))['t'];
$standard_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Standard%'"))['t'];

$weekly_attendance = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE attendance_date='$date' AND status='Present'"))['t'];
    $weekly_attendance[] = ['day' => date('D', strtotime($date)), 'count' => $count];
}

$member_growth = [];
for ($i = 5; $i >= 0; $i--) {
    $month_date = date('Y-m', strtotime("-$i months"));
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE DATE_FORMAT(created_at,'%Y-%m') <= '$month_date'"))['t'];
    $member_growth[] = ['month' => date('M', strtotime("-$i months")), 'count' => $count];
}

// Real monthly revenue for last 6 months
$monthly_revenue_chart = [];
for ($i = 5; $i >= 0; $i--) {
    $month_date  = date('Y-m', strtotime("-$i months"));
    $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$month_date' AND status='Paid'"))['t'];
    $monthly_revenue_chart[] = ['month' => date('M', strtotime("-$i months")), 'revenue' => (int)$rev];
}

$capacity = 150;
$occupancy_percent = $capacity > 0 ? ($today_checkins / $capacity) * 100 : 0;
$retention_rate = $total_members > 0 ? ($total_active / $total_members) * 100 : 0;

$upcoming_renewals = [];
$renewal_date = date('Y-m-d', strtotime('+30 days'));
$res = mysqli_query($conn, "SELECT id, full_name, membership_type, end_date FROM members WHERE end_date BETWEEN '$today' AND '$renewal_date' ORDER BY end_date ASC LIMIT 4");
while ($row = mysqli_fetch_assoc($res)) $upcoming_renewals[] = $row;

$equipment_alerts = [];
$res2 = mysqli_query($conn, "SELECT * FROM equipment WHERE status IN ('Maintenance','Out of Order') LIMIT 3");
while ($row = mysqli_fetch_assoc($res2)) $equipment_alerts[] = $row;

$announcements = [];
$res3 = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
while ($row = mysqli_fetch_assoc($res3)) $announcements[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .chart-container { background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
    .chart-title { font-size:16px; font-weight:600; margin-bottom:15px; color:#1a1a1a; display:flex; align-items:center; gap:8px; }
    .chart-title i { color:#dc3545; }
    .metric-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
    .metric-value { font-size:32px; font-weight:700; color:#1a1a1a; }
    .metric-label { font-size:13px; color:#666; margin-top:5px; }
    .progress-bar-custom { height:8px; background:#f0f0f0; border-radius:10px; overflow:hidden; margin-top:8px; }
    .progress-fill { height:100%; border-radius:10px; }
    .renewal-item { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0; }
    .renewal-item:last-child { border-bottom:none; }
    .renewal-amount { background:#f8f9fa; padding:6px 12px; border-radius:6px; font-weight:600; font-size:13px; }
    canvas { max-height:250px; }
  </style>
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Page Header -->
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
      <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Complete overview of your gym operations</p>
      </div>
      <div style="display:flex;gap:10px;">
        <a href="../modules/attendance-report.php" class="btn app-btn-secondary">
          <i class="fas fa-file-pdf"></i> Reports
        </a>
        <a href="../modules/member-entry.php" class="btn app-btn-primary">
          <i class="fas fa-user-plus"></i> Add Member
        </a>
      </div>
    </div>

    <!-- 6 STAT CARDS -->
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-users"></i></div>
        <div class="stat-info"><h3><?= $total_members ?></h3><p>Total Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-tie"></i></div>
        <div class="stat-info"><h3><?= $active_staff ?></h3><p>Active Staff</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($monthly_revenue) ?></h3><p>Monthly Revenue</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon total"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $today_checkins ?></h3><p>Today's Check-ins</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-plus"></i></div>
        <div class="stat-info"><h3><?= $new_this_month ?></h3><p>New This Month</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-receipt"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($pending_dues) ?></h3><p>Pending Dues</p></div>
      </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="row g-4 mt-1">
      <div class="col-md-8">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-chart-line"></i> Monthly Revenue (₹)</div>
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-chart-pie"></i> Membership Plans</div>
          <canvas id="membershipChart"></canvas>
        </div>
      </div>
    </div>

    <!-- CHARTS ROW 2 -->
    <div class="row g-4 mt-1">
      <div class="col-md-6">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-chart-column"></i> Weekly Attendance</div>
          <canvas id="attendanceChart"></canvas>
        </div>
      </div>
      <div class="col-md-6">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-arrow-trend-up"></i> Member Growth</div>
          <canvas id="growthChart"></canvas>
        </div>
      </div>
    </div>

    <!-- METRICS ROW -->
    <div class="row g-4 mt-1">
      <div class="col-md-4">
        <div class="metric-card">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <i class="fas fa-building" style="font-size:20px;color:#666;"></i>
            <span style="font-size:14px;color:#666;">Current Occupancy</span>
          </div>
          <div class="metric-value"><?= $today_checkins ?> / <?= $capacity ?></div>
          <div class="progress-bar-custom">
            <div class="progress-fill" style="width:<?= min($occupancy_percent,100) ?>%;background:#dc3545;"></div>
          </div>
          <div class="metric-label"><?= round($occupancy_percent) ?>% capacity</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="metric-card">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <i class="fas fa-chart-simple" style="font-size:20px;color:#666;"></i>
            <span style="font-size:14px;color:#666;">Retention Rate</span>
          </div>
          <div class="metric-value"><?= round($retention_rate) ?>%</div>
          <div class="progress-bar-custom">
            <div class="progress-fill" style="width:<?= $retention_rate ?>%;background:#22c55e;"></div>
          </div>
          <div class="metric-label">Active vs Total Members</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="metric-card">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <i class="fas fa-clock" style="font-size:20px;color:#666;"></i>
            <span style="font-size:14px;color:#666;">Total Active Members</span>
          </div>
          <div class="metric-value"><?= $total_active ?></div>
          <div class="progress-bar-custom">
            <div class="progress-fill" style="width:<?= $retention_rate ?>%;background:#dc3545;"></div>
          </div>
          <div class="metric-label">Out of <?= $total_members ?> total</div>
        </div>
      </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="row g-4 mt-1">
      <div class="col-md-4">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-bullhorn"></i> Recent Announcements</div>
          <?php if(!empty($announcements)): foreach($announcements as $a): ?>
          <div class="renewal-item">
            <div>
              <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($a['title']) ?></div>
              <div style="font-size:12px;color:#666;"><?= substr(htmlspecialchars($a['message']),0,50) ?>...</div>
            </div>
            <span class="status-badge <?= strtolower($a['category'])==='urgent'?'expired':'active' ?>" style="font-size:11px;"><?= $a['category'] ?></span>
          </div>
          <?php endforeach; else: ?>
          <div style="text-align:center;padding:30px;color:#aaa;">No announcements</div>
          <?php endif; ?>
          <a href="../modules/announcements.php" style="display:block;text-align:center;margin-top:15px;color:#dc3545;font-size:13px;text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-clock-rotate-left"></i> Upcoming Renewals</div>
          <?php if(!empty($upcoming_renewals)): foreach($upcoming_renewals as $r):
            $price = strpos($r['membership_type'],'Premium')!==false ? '₹1,299' : '₹799';
          ?>
          <div class="renewal-item">
            <div>
              <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($r['full_name']) ?></div>
              <div style="font-size:12px;color:#666;">Expires <?= date('d M Y', strtotime($r['end_date'])) ?></div>
            </div>
            <span class="renewal-amount"><?= $price ?></span>
          </div>
          <?php endforeach; else: ?>
          <div style="text-align:center;padding:30px;color:#aaa;">No renewals in next 30 days</div>
          <?php endif; ?>
          <a href="../modules/members.php" style="display:block;text-align:center;margin-top:15px;color:#dc3545;font-size:13px;text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-container">
          <div class="chart-title"><i class="fas fa-dumbbell"></i> Equipment Alerts</div>
          <?php if(!empty($equipment_alerts)): foreach($equipment_alerts as $e): ?>
          <div class="renewal-item">
            <div>
              <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($e['equipment_name'] ?? $e['name'] ?? '—') ?></div>
              <div style="font-size:12px;color:#666;"><?= $e['status'] ?></div>
            </div>
            <span class="status-badge <?= $e['status']==='Out of Order'?'expired':'inactive' ?>" style="font-size:11px;"><?= $e['status']==='Out of Order'?'Urgent':'Maintenance' ?></span>
          </div>
          <?php endforeach; else: ?>
          <div style="text-align:center;padding:30px;color:#22c55e;">
            <i class="fas fa-circle-check" style="font-size:30px;display:block;margin-bottom:8px;"></i>All equipment working
          </div>
          <?php endif; ?>
          <a href="equipment-list.php" style="display:block;text-align:center;margin-top:15px;color:#dc3545;font-size:13px;text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: [<?php foreach($monthly_revenue_chart as $r) echo "'".$r['month']."',"; ?>],
    datasets: [{ label: 'Revenue', data: [<?php foreach($monthly_revenue_chart as $r) echo $r['revenue'].","; ?>], borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.1)', fill: true, tension: 0.4, borderWidth: 2 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => '₹'+(v/1000)+'k' } } } }
});

new Chart(document.getElementById('membershipChart'), {
  type: 'doughnut',
  data: {
    labels: ['Premium','Basic','Standard'],
    datasets: [{ data: [<?= $premium_count ?>,<?= $basic_count ?>,<?= $standard_count ?>], backgroundColor: ['#dc3545','#6c757d','#22c55e'], borderWidth: 0 }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('attendanceChart'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($weekly_attendance as $w) echo "'".$w['day']."',"; ?>],
    datasets: [{ label: 'Attendance', data: [<?php foreach($weekly_attendance as $w) echo $w['count'].","; ?>], backgroundColor: '#dc3545', borderRadius: 6 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('growthChart'), {
  type: 'line',
  data: {
    labels: [<?php foreach($member_growth as $m) echo "'".$m['month']."',"; ?>],
    datasets: [{ label: 'Members', data: [<?php foreach($member_growth as $m) echo $m['count'].","; ?>], borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', fill: true, tension: 0.4, borderWidth: 2 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
</body>
</html>