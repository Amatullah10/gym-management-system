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

$page = 'payment-reminders';

$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id, full_name, membership_type, end_date FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

// Upcoming due payments (next 30 days)
$upcoming = [];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'due' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $upcoming[] = $row;
}

// Overdue payments
$overdue = [];
$res2 = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'overdue' ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res2)) {
    $overdue[] = $row;
}

// Membership expiry
$days_to_expiry = 0;
if ($member && $member['end_date']) {
    $days_to_expiry = (strtotime($member['end_date']) - strtotime(date('Y-m-d'))) / 86400;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Reminders - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .main-wrapper { margin-top: 0 !important; padding-top: 0 !important; }
    .reminder-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px 25px;
      margin-bottom: 15px;
      border-left: 4px solid #941614;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .reminder-card.overdue { border-left-color: #d32f2f; background: #fff8f8; }
    .reminder-card.upcoming { border-left-color: #f57c00; background: #fffbf5; }
    .reminder-card.membership { border-left-color: #1976d2; background: #f5f9ff; }
    .reminder-icon { font-size: 28px; min-width: 40px; text-align: center; }
    .reminder-info h4 { margin: 0 0 5px; font-size: 16px; font-weight: 600; color: #1a1a1a; }
    .reminder-info p { margin: 0; font-size: 13px; color: #666; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">Payment Reminders</h1>
      <p class="page-subtitle">Stay on top of your payments</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-bell"></i></div>
        <div class="stat-info"><h3><?= count($overdue) ?></h3><p>Overdue</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= count($upcoming) ?></h3><p>Due Soon</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon <?= $days_to_expiry <= 7 ? 'danger' : ($days_to_expiry <= 30 ? 'orange' : 'green') ?>">
          <i class="fa-solid fa-id-card"></i>
        </div>
        <div class="stat-info">
          <h3><?= max(0, round($days_to_expiry)) ?> days</h3>
          <p>Membership Expiry</p>
        </div>
      </div>
    </div>

    <!-- Membership Reminder -->
    <?php if ($member && $member['end_date']): ?>
    <div class="reminder-card membership">
      <div class="reminder-icon" style="color:#1976d2;"><i class="fa-solid fa-id-card"></i></div>
      <div class="reminder-info">
        <h4>Membership Expiry</h4>
        <p>Your <strong><?= $member['membership_type'] ?></strong> membership expires on <strong><?= $member['end_date'] ?></strong>
          (<?= max(0, round($days_to_expiry)) ?> days remaining)</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Overdue Reminders -->
    <?php if (!empty($overdue)): ?>
      <h3 style="margin: 20px 0 10px; color:#d32f2f;">⚠️ Overdue Payments</h3>
      <?php foreach ($overdue as $r): ?>
        <div class="reminder-card overdue">
          <div class="reminder-icon" style="color:#d32f2f;"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="reminder-info">
            <h4>Payment Overdue — ₹<?= number_format($r['amount'], 2) ?></h4>
            <p>Was due on <strong><?= $r['due_date'] ?></strong>. Please pay immediately to avoid penalties.</p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Upcoming Reminders -->
    <?php if (!empty($upcoming)): ?>
      <h3 style="margin: 20px 0 10px; color:#f57c00;">🔔 Upcoming Payments</h3>
      <?php foreach ($upcoming as $r): 
        $days_left = (strtotime($r['due_date']) - strtotime(date('Y-m-d'))) / 86400;
      ?>
        <div class="reminder-card upcoming">
          <div class="reminder-icon" style="color:#f57c00;"><i class="fa-solid fa-bell"></i></div>
          <div class="reminder-info">
            <h4>Payment Due — ₹<?= number_format($r['amount'], 2) ?></h4>
            <p>Due on <strong><?= $r['due_date'] ?></strong> (<?= round($days_left) ?> days remaining). <?= $r['notes'] ?: '' ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($overdue) && empty($upcoming)): ?>
      <div class="app-alert app-alert-success" style="margin-top:20px;">
        <i class="fa-solid fa-circle-check"></i> 🎉 No pending reminders! You are all up to date.
      </div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>