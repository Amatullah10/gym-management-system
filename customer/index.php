<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php"); exit();
}
if ($_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}

$page = 'dashboard';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Get this member's data
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

// Attendance stats
$att_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id"))['c'];
$att_present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id AND status='Present'"))['c'];
$att_pct     = $att_total > 0 ? round(($att_present / $att_total) * 100) : 0;
$this_month  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id AND status='Present' AND DATE_FORMAT(attendance_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')"))['c'];

// Latest progress
$latest_progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM progress_reports WHERE member_id=$member_id ORDER BY date DESC LIMIT 1"));

// Workout plan
$workout = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plans WHERE member_id=$member_id"));

// Last payment
$last_payment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id ORDER BY payment_date DESC LIMIT 1"));

// Days to membership expiry
$days_expiry = 0;
if ($member && $member['end_date']) {
    $days_expiry = (int)round((strtotime($member['end_date']) - strtotime(date('Y-m-d'))) / 86400);
}

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">My Dashboard</h1>
      <p class="page-subtitle">Welcome back, <?= htmlspecialchars($member['full_name'] ?? $email) ?>!</p>
    </div>

    <!-- Membership Status Banner -->
    <?php if ($member): ?>
    <div class="table-container" style="padding:20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
      <div>
        <div style="font-size:13px;color:#999;margin-bottom:4px;">Membership Status</div>
        <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($member['membership_type']) ?></div>
        <div style="font-size:12px;color:#666;margin-top:2px;">Member since <?= date('d M Y', strtotime($member['created_at'])) ?></div>
      </div>
      <div style="text-align:right;">
        <span class="status-badge <?= strtolower($member['membership_status']) ?>"><?= $member['membership_status'] ?></span>
        <div style="font-size:12px;color:#666;margin-top:6px;">
          <?php if ($days_expiry > 0): ?>
            Expires in <strong><?= $days_expiry ?> days</strong> (<?= date('d M Y', strtotime($member['end_date'])) ?>)
          <?php else: ?>
            <strong style="color:#d32f2f;">Membership Expired</strong>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info"><h3><?= $this_month ?></h3><p>This Month Visits</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info"><h3><?= $att_pct ?>%</h3><p>Attendance Rate</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-weight-scale"></i></div>
        <div class="stat-info"><h3><?= $latest_progress ? $latest_progress['weight'].' kg' : '—' ?></h3><p>Current Weight</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon <?= $days_expiry <= 7 ? 'red' : ($days_expiry <= 30 ? 'orange' : 'green') ?>">
          <i class="fas fa-id-card"></i>
        </div>
        <div class="stat-info"><h3><?= max(0,$days_expiry) ?> days</h3><p>Membership Expiry</p></div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Workout Plan -->
      <div class="col-md-6">
        <div class="table-container" style="padding:20px;">
          <h3 style="margin:0 0 15px;font-size:16px;font-weight:600;"><i class="fas fa-dumbbell" style="color:var(--active-color);"></i> My Workout Plan</h3>
          <?php if ($workout): ?>
          <div style="margin-bottom:10px;">
            <div style="font-size:12px;color:#999;">Current Plan</div>
            <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($workout['current_plan'] ?? 'Not Assigned') ?></div>
          </div>
          <div style="margin-bottom:15px;">
            <div style="font-size:12px;color:#999;">Goal</div>
            <div style="font-weight:600;"><?= htmlspecialchars($workout['goal'] ?? '—') ?></div>
          </div>
          <div>
            <div style="font-size:12px;color:#999;margin-bottom:6px;">Progress</div>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="flex:1;height:8px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
                <div style="height:100%;background:var(--active-color);width:<?= $workout['progress'] ?? 0 ?>%;"></div>
              </div>
              <span style="font-weight:700;font-size:14px;"><?= $workout['progress'] ?? 0 ?>%</span>
            </div>
          </div>
          <?php else: ?>
          <div style="text-align:center;padding:20px;color:#aaa;font-size:14px;">No workout plan assigned yet.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Latest Progress -->
      <div class="col-md-6">
        <div class="table-container" style="padding:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;font-size:16px;font-weight:600;"><i class="fas fa-chart-line" style="color:var(--active-color);"></i> Latest Progress</h3>
            <a href="bmi.php" style="font-size:12px;color:var(--active-color);text-decoration:none;">View All</a>
          </div>
          <?php if ($latest_progress): ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;">
              <div style="font-size:11px;color:#999;margin-bottom:4px;">Weight</div>
              <div style="font-size:22px;font-weight:700;"><?= $latest_progress['weight'] ?> kg</div>
            </div>
            <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;">
              <div style="font-size:11px;color:#999;margin-bottom:4px;">BMI</div>
              <div style="font-size:22px;font-weight:700;"><?= $latest_progress['bmi'] ?: '—' ?></div>
            </div>
          </div>
          <div style="font-size:12px;color:#aaa;margin-top:10px;text-align:center;">Recorded on <?= date('d M Y', strtotime($latest_progress['date'])) ?></div>
          <?php else: ?>
          <div style="text-align:center;padding:20px;color:#aaa;font-size:14px;">No progress recorded yet. <a href="weight.php" style="color:var(--active-color);">Add entry</a></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Last Payment -->
      <div class="col-md-6">
        <div class="table-container" style="padding:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;font-size:16px;font-weight:600;"><i class="fas fa-receipt" style="color:var(--active-color);"></i> Last Payment</h3>
            <a href="payment-history.php" style="font-size:12px;color:var(--active-color);text-decoration:none;">View All</a>
          </div>
          <?php if ($last_payment): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-size:22px;font-weight:700;">₹<?= number_format($last_payment['amount']) ?></div>
              <div style="font-size:12px;color:#666;"><?= htmlspecialchars($last_payment['service']) ?> — <?= $last_payment['plan'] ?></div>
              <div style="font-size:12px;color:#aaa;"><?= date('d M Y', strtotime($last_payment['payment_date'])) ?></div>
            </div>
            <span class="status-badge active">Paid</span>
          </div>
          <?php else: ?>
          <div style="text-align:center;padding:20px;color:#aaa;font-size:14px;">No payment records found.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-md-6">
        <div class="table-container" style="padding:20px;">
          <h3 style="margin:0 0 15px;font-size:16px;font-weight:600;"><i class="fas fa-bolt" style="color:var(--active-color);"></i> Quick Access</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <a href="view-attendance.php" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;text-decoration:none;color:#333;font-size:13px;font-weight:500;">
              <i class="fas fa-calendar-check" style="color:var(--active-color);width:16px;"></i> Attendance
            </a>
            <a href="weight.php" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;text-decoration:none;color:#333;font-size:13px;font-weight:500;">
              <i class="fas fa-weight-scale" style="color:var(--active-color);width:16px;"></i> Weight
            </a>
            <a href="bmi.php" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;text-decoration:none;color:#333;font-size:13px;font-weight:500;">
              <i class="fas fa-percent" style="color:var(--active-color);width:16px;"></i> BMI
            </a>
            <a href="payment-history.php" style="display:flex;align-items:center;gap:10px;padding:12px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;text-decoration:none;color:#333;font-size:13px;font-weight:500;">
              <i class="fas fa-receipt" style="color:var(--active-color);width:16px;"></i> Payments
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>