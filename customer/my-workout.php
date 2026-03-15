<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'my-workout';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

// Get workout plan
$workout = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plans WHERE member_id=$member_id"));

// Get assigned trainer
$trainer = null;
$ta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.full_name, s.email FROM trainer_assignments ta JOIN staff s ON s.id=ta.trainer_id WHERE ta.member_id=$member_id AND ta.status='Active'"));

// Get sessions count
$sessions_done = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM trainer_sessions WHERE member_id=$member_id AND status='Completed'"))['c'];
$sessions_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM trainer_sessions WHERE member_id=$member_id"))['c'];

// Get workout plan library details if plan name matches
$plan_library = null;
if ($workout && $workout['current_plan']) {
    $pname = mysqli_real_escape_string($conn, $workout['current_plan']);
    $plan_library = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plan_library WHERE name='$pname' LIMIT 1"));
}

// Days of week with exercises from plan library
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$day_focus = ['Chest & Triceps','Back & Biceps','Legs','Cardio & Core','Shoulders & Full Body','Active Recovery'];

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Workout Plan - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <div class="page-header">
    <h1 class="page-title">My Workout Plan</h1>
    <p class="page-subtitle">NextGen Fitness — Customer Portal</p>
  </div>

  <?php if(!$workout || !$workout['current_plan']): ?>
  <div class="app-alert app-alert-warning"><i class="fas fa-info-circle"></i> No workout plan assigned yet. Please contact your trainer.</div>
  <?php else: ?>

  <!-- Plan Header Card -->
  <div class="table-container" style="padding:25px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:15px;">
      <div>
        <h2 style="margin:0 0 6px;font-size:20px;font-weight:700;"><?= htmlspecialchars($workout['current_plan']) ?></h2>
        <?php if($ta): ?>
        <div style="font-size:13px;color:#666;">Assigned by <strong style="color:var(--active-color);"><?= htmlspecialchars($ta['full_name']) ?></strong></div>
        <?php endif; ?>
        <?php if($plan_library): ?>
        <div style="font-size:13px;color:#999;margin-top:4px;"><?= htmlspecialchars($plan_library['description']) ?></div>
        <?php endif; ?>
      </div>
      <span style="background:var(--active-color);color:white;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;"><?= $workout['status'] ?> Plan</span>
    </div>

    <!-- Plan Stats -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-top:20px;">
      <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;">
        <div style="font-size:28px;font-weight:700;color:var(--active-color);">5</div>
        <div style="font-size:12px;color:#999;">Days/Week</div>
      </div>
      <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;">
        <div style="font-size:28px;font-weight:700;color:var(--active-color);"><?= $sessions_done ?>/<?= $sessions_total ?: 20 ?></div>
        <div style="font-size:12px;color:#999;">Sessions Done</div>
      </div>
      <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;">
        <div style="font-size:28px;font-weight:700;color:var(--active-color);"><?= $workout['progress'] ?>%</div>
        <div style="font-size:12px;color:#999;">Completion</div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div style="margin-top:20px;">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
        <span style="font-weight:600;">Plan Progress</span>
        <span><?= $workout['progress'] ?>%</span>
      </div>
      <div style="height:10px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
        <div style="height:100%;background:var(--active-color);width:<?= $workout['progress'] ?>%;transition:width 0.5s;"></div>
      </div>
    </div>
  </div>

  <!-- Day-wise Exercise Plan -->
  <?php
  // Sample exercises per day based on focus
  $day_exercises = [
    [['Bench Press','4','10','90s','Increase weight each set'],['Incline DB Press','3','12','60s','—'],['Cable Fly','3','15','60s','—'],['Tricep Pushdown','4','12','45s','Keep elbows tucked'],['Overhead Extension','3','12','45s','—']],
    [['Pull-ups','4','8','90s','Full range of motion'],['Barbell Row','4','10','90s','Keep back straight'],['Lat Pulldown','3','12','60s','—'],['Bicep Curl','3','12','45s','—'],['Hammer Curl','3','12','45s','—']],
    [['Squats','4','10','120s','Keep chest up'],['Romanian Deadlift','4','10','90s','Keep back straight'],['Leg Press','3','15','60s','—'],['Leg Curl','3','12','45s','—'],['Calf Raises','4','20','30s','—']],
    [['Treadmill Run','1','30 min','—','Maintain 7 km/h pace'],['Plank','3','60s','30s','—'],['Russian Twists','3','20','30s','—'],['Leg Raises','3','15','30s','—'],['Mountain Climbers','3','30s','20s','—']],
    [['OHP Barbell','4','10','90s','—'],['Lateral Raises','4','15','45s','Light weight, strict form'],['Front Raises','3','12','45s','—'],['Deadlift','3','8','120s','Heavy sets'],['Battle Ropes','4','30s','30s','—']],
  ];

  foreach(array_slice($days,0,5) as $di=>$day):
    $exercises = $day_exercises[$di];
  ?>
  <div class="table-container" style="margin-bottom:15px;">
    <div style="padding:18px 25px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f0f0f0;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;background:#fee;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-dumbbell" style="color:var(--active-color);font-size:16px;"></i>
        </div>
        <div>
          <div style="font-weight:700;font-size:15px;"><?= $day ?></div>
          <span style="background:#f0f0f0;color:#555;font-size:11px;padding:2px 8px;border-radius:20px;"><?= $day_focus[$di] ?></span>
        </div>
      </div>
      <div style="font-size:12px;color:#999;"><i class="fas fa-clock"></i> <?= count($exercises) ?> exercises</div>
    </div>
    <table class="modern-table">
      <thead>
        <tr>
          <th>Exercise</th>
          <th>Sets</th>
          <th>Reps</th>
          <th>Rest</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($exercises as $ex): ?>
        <tr>
          <td><strong><?= $ex[0] ?></strong></td>
          <td style="color:var(--active-color);font-weight:700;"><?= $ex[1] ?></td>
          <td><?= $ex[2] ?></td>
          <td><?= $ex[3] ?></td>
          <td style="color:#666;font-size:13px;"><?= $ex[4] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>