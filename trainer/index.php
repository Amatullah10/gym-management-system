<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php"); exit();
}
$page = 'dashboard';
$trainer_email = $_SESSION['email'];

$total_members  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM members WHERE membership_status='Active'"))['c'];
$active_plans   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM workout_plans WHERE status='Active'"))['c'];
$avg_progress   = round(mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(progress) as a FROM workout_plans WHERE status='Active'"))['a'] ?? 0);
$trainer_email_safe = mysqli_real_escape_string($conn, $trainer_email);
$today_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM trainer_sessions WHERE trainer_email='$trainer_email_safe' AND session_date=CURDATE() AND status='Upcoming'"))['c'];

$recent_q = mysqli_query($conn, "SELECT m.id, m.full_name, m.email,
    COALESCE(wp.goal,'Not Set') as goal,
    COALESCE(wp.current_plan,'Not Assigned') as current_plan,
    COALESCE(wp.progress,0) as progress,
    COALESCE(wp.status,'Pending') as workout_status
    FROM members m LEFT JOIN workout_plans wp ON m.id=wp.member_id
    ORDER BY m.created_at DESC LIMIT 5");
$recent_members = [];
while($r = mysqli_fetch_assoc($recent_q)) $recent_members[] = $r;

$sessions_q = mysqli_query($conn, "SELECT ts.*, m.full_name FROM trainer_sessions ts
    JOIN members m ON m.id=ts.member_id
    WHERE ts.trainer_email='$trainer_email_safe' AND ts.session_date >= CURDATE() AND ts.status='Upcoming'
    ORDER BY ts.session_date ASC, ts.start_time ASC LIMIT 5");
$upcoming_sessions = [];
while($r = mysqli_fetch_assoc($sessions_q)) $upcoming_sessions[] = $r;

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">Welcome back, Trainer! 👋</h1>
      <p class="page-subtitle">Here's an overview of your training activities</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-users"></i></div>
        <div class="stat-info"><h3><?= $total_members ?></h3><p>Active Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-dumbbell"></i></div>
        <div class="stat-info"><h3><?= $active_plans ?></h3><p>Active Plans</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info"><h3><?= $avg_progress ?>%</h3><p>Avg Progress</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h3><?= $today_sessions ?></h3><p>Today's Sessions</p></div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-md-6">
        <div class="table-container">
          <div class="table-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3>Upcoming Sessions</h3>
            <a href="schedule.php" style="color:var(--active-color);font-size:13px;text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
          </div>
          <?php if (empty($upcoming_sessions)): ?>
            <div style="padding:30px;text-align:center;color:#aaa;font-size:14px;">No upcoming sessions scheduled</div>
          <?php else: ?>
          <table class="modern-table">
            <tbody>
              <?php foreach($upcoming_sessions as $s): ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($s['full_name'],0,1)) ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($s['title']) ?></span>
                      <span class="meta"><?= htmlspecialchars($s['full_name']) ?> &bull; <?= date('d M', strtotime($s['session_date'])) ?> at <?= date('H:i', strtotime($s['start_time'])) ?></span>
                    </div>
                  </div>
                </td>
                <td><span class="app-badge app-badge-success" style="font-size:11px;white-space:nowrap;"><?= $s['session_type'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-6">
        <div class="table-container">
          <div class="table-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3>Recent Members</h3>
            <a href="assigned-member.php" style="color:var(--active-color);font-size:13px;text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
          </div>
          <table class="modern-table">
            <tbody>
              <?php foreach($recent_members as $m): ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($m['full_name'],0,1)) ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                      <span class="meta"><?= htmlspecialchars($m['goal']) ?></span>
                    </div>
                  </div>
                </td>
                <td style="min-width:130px;">
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;height:6px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
                      <div style="height:100%;background:var(--active-color);width:<?= $m['progress'] ?>%;"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:#333;"><?= $m['progress'] ?>%</span>
                  </div>
                </td>
                <td>
                  <a href="view-member-details.php?id=<?= $m['id'] ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>