<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in and has proper role
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only admin and receptionist to access this page
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../index.php");
    exit();
}

$page = 'attendance-report'; // For active sidebar highlighting

// Get date range (default to this month)
$period = isset($_GET['period']) ? $_GET['period'] : 'this_month';

// Calculate date range
$start_date = '';
$end_date = date('Y-m-d');

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        break;
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('first day of last month'));
        $end_date = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'this_year':
        $start_date = date('Y-01-01');
        break;
}

// Get total attendance count
$sql_total = "SELECT COUNT(*) as total FROM attendance WHERE attendance_date BETWEEN ? AND ?";
$stmt_total = mysqli_prepare($conn, $sql_total);
mysqli_stmt_bind_param($stmt_total, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$total_attendance = mysqli_fetch_assoc($result_total)['total'];

// Get attendance rate
$sql_rate = "SELECT 
    COUNT(DISTINCT a.member_id, a.attendance_date) as attended,
    COUNT(DISTINCT m.id) * DATEDIFF(?, ?) + COUNT(DISTINCT m.id) as total_possible
FROM members m
LEFT JOIN attendance a ON m.id = a.member_id AND a.attendance_date BETWEEN ? AND ?
WHERE m.membership_status = 'active'";
$stmt_rate = mysqli_prepare($conn, $sql_rate);
mysqli_stmt_bind_param($stmt_rate, "ssss", $end_date, $start_date, $start_date, $end_date);
mysqli_stmt_execute($stmt_rate);
$result_rate = mysqli_stmt_get_result($stmt_rate);
$rate_data = mysqli_fetch_assoc($result_rate);
$attendance_rate = $rate_data['total_possible'] > 0 
    ? round(($rate_data['attended'] / $rate_data['total_possible']) * 100) 
    : 0;

// Get average check-in time
$sql_avg_time = "SELECT AVG(TIME_TO_SEC(check_in_time)) as avg_seconds 
FROM attendance 
WHERE attendance_date BETWEEN ? AND ? AND check_in_time IS NOT NULL";
$stmt_avg_time = mysqli_prepare($conn, $sql_avg_time);
mysqli_stmt_bind_param($stmt_avg_time, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_avg_time);
$result_avg_time = mysqli_stmt_get_result($stmt_avg_time);
$avg_seconds = mysqli_fetch_assoc($result_avg_time)['avg_seconds'];
$avg_check_in = $avg_seconds ? gmdate("g:i A", $avg_seconds) : "N/A";

// Get absent members count
$sql_absent = "SELECT COUNT(*) as absent_count 
FROM attendance 
WHERE attendance_date BETWEEN ? AND ? AND status = 'Absent'";
$stmt_absent = mysqli_prepare($conn, $sql_absent);
mysqli_stmt_bind_param($stmt_absent, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_absent);
$result_absent = mysqli_stmt_get_result($stmt_absent);
$absent_count = mysqli_fetch_assoc($result_absent)['absent_count'];

// Get weekly attendance data
$sql_weekly = "SELECT 
    DAYNAME(attendance_date) as day_name,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Present' AND HOUR(check_in_time) >= 9 THEN 1 ELSE 0 END) as late
FROM attendance 
WHERE attendance_date BETWEEN ? AND ?
GROUP BY DAYOFWEEK(attendance_date), DAYNAME(attendance_date)
ORDER BY DAYOFWEEK(attendance_date)";
$stmt_weekly = mysqli_prepare($conn, $sql_weekly);
mysqli_stmt_bind_param($stmt_weekly, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_weekly);
$result_weekly = mysqli_stmt_get_result($stmt_weekly);

$weekly_data = [];
while ($row = mysqli_fetch_assoc($result_weekly)) {
    $weekly_data[] = $row;
}

// Get distribution data
$sql_distribution = "SELECT 
    SUM(CASE WHEN status = 'Present' AND (check_in_time IS NULL OR HOUR(check_in_time) < 9) THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Present' AND HOUR(check_in_time) >= 9 THEN 1 ELSE 0 END) as late
FROM attendance 
WHERE attendance_date BETWEEN ? AND ?";
$stmt_dist = mysqli_prepare($conn, $sql_distribution);
mysqli_stmt_bind_param($stmt_dist, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_dist);
$result_dist = mysqli_stmt_get_result($stmt_dist);
$distribution = mysqli_fetch_assoc($result_dist);

// Get top attendees
$sql_top = "SELECT 
    m.id,
    m.full_name,
    COUNT(a.id) as days_present,
    ROUND((COUNT(a.id) / DATEDIFF(?, ?)) * 100) as attendance_rate
FROM members m
LEFT JOIN attendance a ON m.id = a.member_id 
    AND a.attendance_date BETWEEN ? AND ? 
    AND a.status = 'Present'
WHERE m.membership_status = 'active'
GROUP BY m.id, m.full_name
HAVING days_present > 0
ORDER BY days_present DESC
LIMIT 10";
$stmt_top = mysqli_prepare($conn, $sql_top);
mysqli_stmt_bind_param($stmt_top, "ssss", $end_date, $start_date, $start_date, $end_date);
mysqli_stmt_execute($stmt_top);
$result_top = mysqli_stmt_get_result($stmt_top);

$top_attendees = [];
$rank = 1;
while ($row = mysqli_fetch_assoc($result_top)) {
    $row['rank'] = $rank++;
    $top_attendees[] = $row;
}

// Calculate previous period for comparison
$prev_start_date = date('Y-m-d', strtotime($start_date . ' -1 month'));
$prev_end_date = date('Y-m-d', strtotime($end_date . ' -1 month'));

// Get previous period stats for comparison
$sql_prev = "SELECT COUNT(*) as prev_total FROM attendance WHERE attendance_date BETWEEN ? AND ?";
$stmt_prev = mysqli_prepare($conn, $sql_prev);
mysqli_stmt_bind_param($stmt_prev, "ss", $prev_start_date, $prev_end_date);
mysqli_stmt_execute($stmt_prev);
$result_prev = mysqli_stmt_get_result($stmt_prev);
$prev_total = mysqli_fetch_assoc($result_prev)['prev_total'];

$total_change = $prev_total > 0 ? round((($total_attendance - $prev_total) / $prev_total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Reports - Gym Management System</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  
  <style>
    .chart-container {
      background: white;
      border-radius: var(--radius-lg);
      padding: 25px;
      box-shadow: var(--shadow-sm);
      border: 1px solid #f0f0f0;
      margin-bottom: 30px;
    }
    
    .chart-title {
      font-size: 18px;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 20px;
    }
    
    .chart-canvas {
      position: relative;
      height: 300px;
    }
    
    .comparison-text {
      font-size: 13px;
      margin-top: 5px;
    }
    
    .comparison-text.positive {
      color: var(--success-color);
    }
    
    .comparison-text.negative {
      color: var(--danger-color);
    }
    
    .peak-time {
      font-size: 13px;
      color: #666;
      display: flex;
      align-items: center;
      gap: 5px;
      margin-top: 5px;
    }
    
    .rank-badge {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
    }
    
    .rank-badge.gold {
      background: linear-gradient(135deg, #ffd700, #ffed4e);
      color: #8b6914;
    }
    
    .rank-badge.silver {
      background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
      color: #6b6b6b;
    }
    
    .rank-badge.bronze {
      background: linear-gradient(135deg, #cd7f32, #e8a87c);
      color: #6b3e1a;
    }
    
    .rank-badge.other {
      background: #f5f5f5;
      color: #666;
    }
  </style>
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    
    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Attendance Reports</h1>
          <p class="page-subtitle">Analyze attendance patterns and trends</p>
        </div>
        <div class="d-flex gap-3">
          <select class="filter-select" id="periodFilter" onchange="changePeriod(this.value)">
            <option value="today" <?= $period == 'today' ? 'selected' : '' ?>>Today</option>
            <option value="this_week" <?= $period == 'this_week' ? 'selected' : '' ?>>This Week</option>
            <option value="this_month" <?= $period == 'this_month' ? 'selected' : '' ?>>This Month</option>
            <option value="last_month" <?= $period == 'last_month' ? 'selected' : '' ?>>Last Month</option>
            <option value="this_year" <?= $period == 'this_year' ? 'selected' : '' ?>>This Year</option>
          </select>
          <button class="btn app-btn-primary" onclick="exportReport()">
            <i class="fa-solid fa-file-excel"></i> Export to Excel
          </button>
        </div>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fa-solid fa-user-group"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_attendance ?></h3>
          <p>Total Attendance</p>
          <div class="comparison-text <?= $total_change >= 0 ? 'positive' : 'negative' ?>">
            <i class="fa-solid fa-arrow-<?= $total_change >= 0 ? 'up' : 'down' ?>"></i>
            <?= abs($total_change) ?>% vs last period
          </div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon red">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-info">
          <h3><?= $attendance_rate ?>%</h3>
          <p>Attendance Rate</p>
          <div class="comparison-text positive">
            <i class="fa-solid fa-arrow-up"></i>
            +5% vs last period
          </div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-info">
          <h3><?= $avg_check_in ?></h3>
          <p>Avg. Check-in Time</p>
          <div class="peak-time">
            <i class="fa-solid fa-clock"></i>
            Peak: 6-8 AM
          </div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon danger">
          <i class="fa-solid fa-user-group"></i>
        </div>
        <div class="stat-info">
          <h3><?= $absent_count ?></h3>
          <p>Absent Members</p>
          <div class="comparison-text negative">
            <i class="fa-solid fa-arrow-down"></i>
            -8% vs last period
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
      
      <!-- Weekly Overview Chart -->
      <div class="chart-container">
        <h3 class="chart-title">Weekly Attendance Overview</h3>
        <div class="chart-canvas">
          <canvas id="weeklyChart"></canvas>
        </div>
      </div>
      
      <!-- Distribution Chart -->
      <div class="chart-container">
        <h3 class="chart-title">Attendance Distribution</h3>
        <div class="chart-canvas">
          <canvas id="distributionChart"></canvas>
        </div>
      </div>
      
    </div>
    
    <!-- Monthly Trend Chart -->
    <div class="chart-container">
      <h3 class="chart-title">Monthly Attendance Trend</h3>
      <div class="chart-canvas">
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>
    
    <!-- Top Attendees Table -->
    <div class="chart-container">
      <h3 class="chart-title">Top Attendees This Month</h3>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Member ID</th>
            <th>Name</th>
            <th>Days Present</th>
            <th>Attendance Rate</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($top_attendees) > 0): ?>
            <?php foreach ($top_attendees as $attendee): 
              $memberId = 'MEM' . str_pad($attendee['id'], 3, '0', STR_PAD_LEFT);
              $badgeClass = 'other';
              if ($attendee['rank'] == 1) $badgeClass = 'gold';
              elseif ($attendee['rank'] == 2) $badgeClass = 'silver';
              elseif ($attendee['rank'] == 3) $badgeClass = 'bronze';
            ?>
            <tr>
              <td>
                <div class="rank-badge <?= $badgeClass ?>">
                  <?= $attendee['rank'] ?>
                </div>
              </td>
              <td>
                <span style="font-weight: 600; color: #333;"><?= $memberId ?></span>
              </td>
              <td>
                <span style="color: #1a1a1a; font-weight: 500;"><?= htmlspecialchars($attendee['full_name']) ?></span>
              </td>
              <td>
                <span style="color: #666; font-weight: 600;"><?= $attendee['days_present'] ?></span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="flex: 1; height: 8px; background: #f0f0f0; border-radius: 10px; overflow: hidden; max-width: 200px;">
                    <div style="height: 100%; background: var(--success-color); width: <?= $attendee['attendance_rate'] ?>%;"></div>
                  </div>
                  <span style="font-weight: 600; color: #333; min-width: 45px;"><?= $attendee['attendance_rate'] ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center" style="padding: 40px; color: #999;">No attendance data available.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SheetJS for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
// Weekly Chart Data
const weeklyData = <?= json_encode($weekly_data) ?>;

// Weekly Attendance Chart
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
const weeklyChart = new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: weeklyData.map(d => d.day_name.substring(0, 3)),
        datasets: [
            {
                label: 'Present',
                data: weeklyData.map(d => d.present),
                backgroundColor: '#2e7d32',
                borderRadius: 8
            },
            {
                label: 'Absent',
                data: weeklyData.map(d => d.absent),
                backgroundColor: '#d32f2f',
                borderRadius: 8
            },
            {
                label: 'Late',
                data: weeklyData.map(d => d.late),
                backgroundColor: '#f57c00',
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: '#f0f0f0'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Distribution Data
const distributionData = <?= json_encode($distribution) ?>;

// Distribution Chart
const distCtx = document.getElementById('distributionChart').getContext('2d');
const distChart = new Chart(distCtx, {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent', 'Late'],
        datasets: [{
            data: [
                distributionData.present,
                distributionData.absent,
                distributionData.late
            ],
            backgroundColor: ['#2e7d32', '#d32f2f', '#f57c00'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: {
                        size: 13,
                        family: "'Inter', sans-serif"
                    }
                }
            }
        },
        cutout: '70%'
    }
});

// Monthly Trend Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Attendance Rate',
            data: [87, 91, 82, 92],
            borderColor: '#941614',
            backgroundColor: 'rgba(148, 22, 20, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointBackgroundColor: '#941614',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Attendance Rate: ' + context.parsed.y + '%';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: {
                    color: '#f0f0f0'
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Change period filter
function changePeriod(period) {
    window.location.href = 'attendance-report.php?period=' + period;
}

// Export report to Excel
function exportReport() {
    const topAttendees = <?= json_encode($top_attendees) ?>;
    const weeklyData   = <?= json_encode($weekly_data) ?>;

    const wb = XLSX.utils.book_new();

    /* ── Sheet 1: Summary ── */
    const rows = [
        ['Attendance Report - <?= date("d M Y") ?>'],
        [],
        ['Period',               '<?= ucwords(str_replace('_', ' ', $period)) ?>'],
        ['Date Range',           '<?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?>'],
        ['Generated On',         new Date().toLocaleString()],
        [],
        ['SUMMARY STATISTICS'],
        ['Metric',               'Value'],
        ['Total Attendance',     <?= $total_attendance ?>],
        ['Attendance Rate',      '<?= $attendance_rate ?>%'],
        ['Average Check-in Time','<?= $avg_check_in ?>'],
        ['Absent Members',       <?= $absent_count ?>],
        [],
        ['ATTENDANCE DISTRIBUTION'],
        ['Status',               'Count'],
        ['Present',              <?= (int)($distribution['present'] ?? 0) ?>],
        ['Absent',               <?= (int)($distribution['absent']  ?? 0) ?>],
        ['Late',                 <?= (int)($distribution['late']    ?? 0) ?>],
    ];

    const ws1 = XLSX.utils.aoa_to_sheet(rows);

    // Column widths for summary sheet
    ws1['!cols'] = [{ wch: 28 }, { wch: 30 }];

    XLSX.utils.book_append_sheet(wb, ws1, 'Summary');

    /* ── Sheet 2: Top Attendees ── */
    const attendeesRows = [
        ['Top Attendees - <?= date("d M Y") ?>'],
        [],
        ['Rank', 'Member ID', 'Name', 'Days Present', 'Attendance Rate']
    ];

    topAttendees.forEach(a => {
        attendeesRows.push([
            a.rank,
            'MEM' + String(a.id).padStart(3, '0'),
            a.full_name,
            a.days_present,
            a.attendance_rate + '%'
        ]);
    });

    const ws2 = XLSX.utils.aoa_to_sheet(attendeesRows);
    ws2['!cols'] = [{ wch: 8 }, { wch: 12 }, { wch: 28 }, { wch: 15 }, { wch: 18 }];
    XLSX.utils.book_append_sheet(wb, ws2, 'Top Attendees');

    /* ── Sheet 3: Weekly Breakdown ── */
    const weeklyRows = [
        ['Weekly Attendance Breakdown - <?= date("d M Y") ?>'],
        [],
        ['Day', 'Present', 'Absent', 'Late', 'Total']
    ];

    weeklyData.forEach(d => {
        const total = (parseInt(d.present) || 0) + (parseInt(d.absent) || 0) + (parseInt(d.late) || 0);
        weeklyRows.push([d.day_name, d.present, d.absent, d.late, total]);
    });

    const ws3 = XLSX.utils.aoa_to_sheet(weeklyRows);
    ws3['!cols'] = [{ wch: 14 }, { wch: 10 }, { wch: 10 }, { wch: 10 }, { wch: 10 }];
    XLSX.utils.book_append_sheet(wb, ws3, 'Weekly Breakdown');

    /* ── Download ── */
    XLSX.writeFile(wb, 'Attendance_Report_<?= date("Y-m-d") ?>.xlsx');
}
</script>

</body>
</html>