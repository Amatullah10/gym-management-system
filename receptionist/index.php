<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'dashboard';

// Stats
$total_members   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members"))['t'];
$active_members  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status = 'Active'"))['t'];
$expired_members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status = 'Expired'"))['t'];
$today = date('Y-m-d');

// FIXED: Use attendance_date and status columns
$today_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE attendance_date = '$today' AND status = 'Present'"))['t'];

// Recent members
$recent_members = [];
$res = mysqli_query($conn, "SELECT * FROM members ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) { $recent_members[] = $row; }

// FIXED: Today's attendance list - use attendance_date, check_in_time, check_out_time
$today_list = [];
$res2 = mysqli_query($conn, "SELECT a.*, m.full_name, m.email FROM attendance a JOIN members m ON a.member_id = m.id WHERE a.attendance_date = '$today' AND a.status = 'Present' ORDER BY a.check_in_time DESC LIMIT 6");
while ($row = mysqli_fetch_assoc($res2)) { $today_list[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receptionist Dashboard - Gym Management</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Receptionist Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's your overview for today — <?= date('d M Y') ?></p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info"><h3><?= $total_members ?></h3><p>Total Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-user-check"></i></div>
        <div class="stat-info"><h3><?= $active_members ?></h3><p>Active Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-user-clock"></i></div>
        <div class="stat-info"><h3><?= $expired_members ?></h3><p>Expired Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $today_attendance ?></h3><p>Today's Attendance</p></div>
      </div>
    </div>

    <!-- Recent Members Table -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3>Recently Registered Members</h3>
        <a href="../modules/members.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Phone</th>
            <th>Plan</th>
            <th>Start Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recent_members)): ?>
            <?php foreach ($recent_members as $m):
              $initial   = strtoupper(substr($m['full_name'], 0, 1));
              $plan_type = strtolower(explode(' - ', $m['membership_type'])[0]);
              $days_left = round((strtotime($m['end_date']) - strtotime(date('Y-m-d'))) / 86400);
              if ($days_left < 0) { $status = 'expired'; $status_text = 'EXPIRED'; }
              elseif ($days_left <= 30) { $status = 'inactive'; $status_text = 'EXPIRING SOON'; }
              else { $status = 'active'; $status_text = 'ACTIVE'; }
            ?>
            <tr>
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($m['email']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($m['phone']) ?></td>
              <td><span class="plan-badge <?= $plan_type ?>"><?= htmlspecialchars($m['membership_type']) ?></span></td>
              <td class="date-display"><?= $m['start_date'] ?></td>
              <td><span class="status-badge <?= $status ?>"><?= $status_text ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center" style="padding:20px; color:#aaa;">No members found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Today's Attendance Cards -->
    <div class="members-table-container">
      <div class="table-header flex justify-between align-center">
        <h3>
          Today's Attendance
          <span class="app-badge app-badge-warning" style="margin-left:8px;"><?= $today_attendance ?> members</span>
        </h3>
        <a href="../modules/view-attendance.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>

      <?php if (!empty($today_list)): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:15px; padding:20px;">
          <?php foreach ($today_list as $a):
            // FIXED: Use check_in_time and check_out_time (TIME columns)
            $check_in_time  = isset($a['check_in_time']) ? $a['check_in_time'] : null;
            $check_out_time = isset($a['check_out_time']) && $a['check_out_time'] ? $a['check_out_time'] : null;
            
            $duration = '-';
            if ($check_out_time && $check_in_time) {
              // Calculate duration from TIME fields
              $check_in_dt  = strtotime($a['attendance_date'] . ' ' . $check_in_time);
              $check_out_dt = strtotime($a['attendance_date'] . ' ' . $check_out_time);
              $diff = $check_out_dt - $check_in_dt;
              $duration = floor($diff/3600).'h '.floor(($diff%3600)/60).'m';
            }
            
            $initial = strtoupper(substr($a['full_name'], 0, 1));
            $is_out  = !empty($check_out_time) ? true : false;
          ?>
          <div class="stat-card" style="flex-direction:column; align-items:stretch; gap:12px; border-left:4px solid <?= $is_out ? 'var(--success-color)' : 'var(--warning-color)' ?>;">

            <div class="member-cell">
              <div class="member-avatar"><?= $initial ?></div>
              <div class="member-info" style="flex:1;">
                <span class="name"><?= htmlspecialchars($a['full_name']) ?></span>
                <span class="joined"><?= htmlspecialchars($a['email']) ?></span>
              </div>
              <span class="status-badge <?= $is_out ? 'active' : 'pending' ?>" style="font-size:11px; white-space:nowrap;">
                <?= $is_out ? 'Completed' : 'Present' ?>
              </span>
            </div>

            <div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr; gap:8px; margin:0;">
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-right-to-bracket" style="color:var(--success-color);"></i> In</p>
                <p style="font-size:13px; font-weight:600; color:#1a1a1a; margin:0;">
                  <?= $check_in_time ? date('h:i A', strtotime($check_in_time)) : '—' ?>
                </p>
              </div>
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-right-from-bracket" style="color:var(--danger-color);"></i> Out</p>
                <p style="font-size:13px; font-weight:600; color:<?= $is_out ? '#1a1a1a' : '#ccc' ?>; margin:0;">
                  <?= $is_out ? date('h:i A', strtotime($check_out_time)) : '—' ?>
                </p>
              </div>
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-clock" style="color:#2196f3;"></i> Time</p>
                <p style="font-size:13px; font-weight:600; color:#1a1a1a; margin:0;"><?= $duration ?></p>
              </div>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="text-center" style="padding:50px 20px;">
          <i class="fa-solid fa-calendar-xmark" style="font-size:45px; margin-bottom:15px; display:block; color:#ddd;"></i>
          <p style="font-size:15px; font-weight:500; color:#aaa;">No attendance recorded today yet.</p>
          <a href="mark-attendance.php" style="color:var(--active-color); font-size:14px; text-decoration:none;">
            <i class="fa-solid fa-plus"></i> Mark Attendance Now
          </a>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>