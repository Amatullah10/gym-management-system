<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'dashboard';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

// Attendance
$att_total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id"))['c'];
$att_present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id AND status='Present'"))['c'];
$att_pct     = $att_total > 0 ? round(($att_present/$att_total)*100) : 0;

// Latest progress
$progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM progress_reports WHERE member_id=$member_id ORDER BY date DESC LIMIT 1"));

// Workout plan
$workout = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plans WHERE member_id=$member_id"));

// Recent payments
$payments_q = mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id ORDER BY payment_date DESC LIMIT 3");
$payments = [];
while($r = mysqli_fetch_assoc($payments_q)) $payments[] = $r;

// Pending dues
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE member_id=$member_id AND status='Due'"))['total'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE member_id=$member_id AND status='Overdue'"))['c'];

// Days to expiry
$days_expiry = $member ? (int)round((strtotime($member['end_date']) - strtotime(date('Y-m-d')))/86400) : 0;

// BMI category
function getBMIInfo($bmi) {
    if (!$bmi) return ['—', '#aaa'];
    if ($bmi < 18.5) return ['Underweight', '#f57c00'];
    if ($bmi < 25)   return ['Normal', '#2e7d32'];
    if ($bmi < 30)   return ['Overweight', '#f57c00'];
    return ['Obese', '#d32f2f'];
}
[$bmi_cat, $bmi_color] = getBMIInfo($progress['bmi'] ?? null);

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper">
<div class="main-content">

  <div class="page-header">
    <h1 class="page-title">Dashboard Overview</h1>
    <p class="page-subtitle">NextGen Fitness — Customer Portal</p>
  </div>

  <!-- Top 4 Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon <?= $member['membership_status']==='Active' ? 'green' : 'red' ?>">
        <i class="fas fa-id-card"></i>
      </div>
      <div class="stat-info">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Membership Status</p>
        <h3 style="font-size:22px;"><?= $member['membership_status'] ?? '—' ?></h3>
        <p style="font-size:12px;color:#aaa;">Expires <?= $member ? date('M d, Y', strtotime($member['end_date'])) : '—' ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-weight-scale"></i></div>
      <div class="stat-info">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Current Weight</p>
        <h3 style="font-size:22px;"><?= $progress ? $progress['weight'].' kg' : '—' ?></h3>
        <p style="font-size:12px;color:#aaa;"><?= $progress ? 'Updated '.date('M d', strtotime($progress['date'])) : 'Not recorded' ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f5e9;color:<?= $bmi_color ?>;width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;">
        <i class="fas fa-percent"></i>
      </div>
      <div class="stat-info">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">BMI Score</p>
        <h3 style="font-size:22px;"><?= $progress['bmi'] ?? '—' ?></h3>
        <p style="font-size:12px;color:<?= $bmi_color ?>;"><?= $bmi_cat ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="fas fa-triangle-exclamation"></i></div>
      <div class="stat-info">
        <p style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Pending Dues</p>
        <h3 style="font-size:22px;">₹<?= number_format($pending) ?></h3>
        <p style="font-size:12px;color:#aaa;"><?= $overdue_count ?> overdue payment<?= $overdue_count!=1?'s':'' ?></p>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Fitness Progress -->
    <div class="col-md-7">
      <div class="table-container" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Fitness Progress</h3>
          <a href="bmi.php" style="color:var(--active-color);font-size:13px;text-decoration:none;">View Details →</a>
        </div>
        <?php if($workout): ?>
        <div style="margin-bottom:18px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
            <span style="font-weight:600;"><?= htmlspecialchars($workout['goal'] ?? 'Workout Goal') ?></span>
            <span style="color:#999;"><?= $workout['progress'] ?>%</span>
          </div>
          <div style="height:8px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
            <div style="height:100%;background:var(--active-color);width:<?= $workout['progress'] ?>%;"></div>
          </div>
        </div>
        <div style="margin-bottom:18px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
            <span style="font-weight:600;">Attendance Rate</span>
            <span style="color:#999;"><?= $att_pct ?>%</span>
          </div>
          <div style="height:8px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
            <div style="height:100%;background:var(--active-color);width:<?= $att_pct ?>%;"></div>
          </div>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:30px;color:#aaa;">No workout plan assigned yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-md-5">
      <div class="table-container" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Recent Payments</h3>
          <a href="my-payments.php" style="color:var(--active-color);font-size:13px;text-decoration:none;">View All →</a>
        </div>
        <?php if(empty($payments)): ?>
        <div style="text-align:center;padding:20px;color:#aaa;font-size:14px;">No payment records.</div>
        <?php else: foreach($payments as $p): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:#fee;border-radius:8px;display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-receipt" style="color:var(--active-color);font-size:14px;"></i>
            </div>
            <div>
              <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($p['service']) ?></div>
              <div style="font-size:11px;color:#aaa;"><?= date('M d, Y', strtotime($p['payment_date'])) ?></div>
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-weight:700;font-size:14px;">₹<?= number_format($p['amount']) ?></div>
            <div style="font-size:11px;color:<?= $p['status']==='Paid'?'#2e7d32':($p['status']==='Overdue'?'#d32f2f':'#f57c00') ?>;"><?= $p['status'] ?></div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Today's Workout -->
    <?php if($workout): ?>
    <div class="col-12">
      <div class="table-container" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Today's Workout</h3>
          <a href="my-workout.php" style="color:var(--active-color);font-size:13px;text-decoration:none;">Full Plan →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
          <?php
          $day_plan = htmlspecialchars($workout['current_plan'] ?? '');
          $exercises = ['Bench Press','Squats','Pull-ups','Plank','Deadlift','Running'];
          $muscles   = ['Chest','Legs','Back','Core','Full Body','Cardio'];
          $sets      = ['4×10','4×12','3×8','3×60s','4×6','30 min'];
          foreach(array_slice($exercises,0,4) as $i=>$ex):
          ?>
          <div style="background:#fff5f5;border-radius:12px;padding:16px;text-align:center;">
            <div style="font-size:24px;margin-bottom:8px;">💪</div>
            <div style="font-weight:700;font-size:13px;"><?= $ex ?></div>
            <div style="font-size:11px;color:#999;margin:3px 0;"><?= $muscles[$i] ?></div>
            <span style="background:var(--active-color);color:white;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;"><?= $sets[$i] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>