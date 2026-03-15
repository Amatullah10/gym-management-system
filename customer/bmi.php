<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'bmi';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

function getBMIInfo($bmi) {
    if (!$bmi) return ['—', '#aaa', 'unknown'];
    if ($bmi < 18.5) return ['Underweight', '#f57c00', 'orange'];
    if ($bmi < 25)   return ['Normal', '#2e7d32', 'active'];
    if ($bmi < 30)   return ['Overweight', '#f57c00', 'orange'];
    return ['Obese', '#d32f2f', 'expired'];
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = (float)$_POST['weight'];
    $height = (float)$_POST['height'];
    $bmi    = $height > 0 ? round($weight/(($height/100)*($height/100)),1) : 0;
    $date   = mysqli_real_escape_string($conn, $_POST['date']);
    $notes  = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
    $ins = mysqli_query($conn, "INSERT INTO progress_reports (member_id, date, weight, height, bmi, notes, recorded_by)
        VALUES ($member_id, '$date', $weight, $height, $bmi, '$notes', '$email')");
    if ($ins) { $success = "Progress recorded! Your BMI is <strong>$bmi</strong>"; }
    else { $error = "Failed: ".mysqli_error($conn); }
}

$records = [];
$res = mysqli_query($conn, "SELECT * FROM progress_reports WHERE member_id=$member_id ORDER BY date DESC");
while($r = mysqli_fetch_assoc($res)) $records[] = $r;
$latest = $records[0] ?? null;
$prev   = $records[1] ?? null;
[$bmi_cat, $bmi_color, $bmi_cls] = getBMIInfo($latest['bmi'] ?? null);

// Weight change
$weight_diff = ($latest && $prev) ? round($latest['weight'] - $prev['weight'], 1) : null;

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BMI & Progress - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <div class="page-header">
    <h1 class="page-title">BMI & Fitness Progress</h1>
    <p class="page-subtitle">NextGen Fitness — Customer Portal</p>
  </div>

  <?php if($success): ?><div class="app-alert app-alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
  <?php if($error): ?><div class="app-alert app-alert-error"><?= $error ?></div><?php endif; ?>

  <!-- Top Stats -->
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-heartbeat"></i></div>
      <div class="stat-info">
        <p>Current BMI</p>
        <h3><?= $latest['bmi'] ?? '—' ?></h3>
        <p style="color:<?= $bmi_color ?>;font-size:12px;"><?= $bmi_cat ?></p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon <?= $weight_diff !== null && $weight_diff <= 0 ? 'green' : 'orange' ?>">
        <i class="fas fa-weight-scale"></i>
      </div>
      <div class="stat-info">
        <p>Weight</p>
        <h3><?= $latest ? $latest['weight'].' kg' : '—' ?></h3>
        <?php if($weight_diff !== null): ?>
        <p style="font-size:12px;color:<?= $weight_diff<=0?'#2e7d32':'#f57c00' ?>;">
          <?= ($weight_diff>0?'+':'').$weight_diff ?> kg this entry
        </p>
        <?php endif; ?>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
      <div class="stat-info">
        <p>Total Records</p>
        <h3><?= count($records) ?></h3>
        <p style="font-size:12px;color:#aaa;"><?= $latest ? date('M d, Y', strtotime($latest['date'])) : '—' ?></p>
      </div>
    </div>
  </div>

  <!-- BMI Scale Visual -->
  <?php if($latest && $latest['bmi']): ?>
  <div class="table-container" style="padding:25px;margin-bottom:20px;">
    <h3 style="margin:0 0 20px;font-size:16px;font-weight:700;">BMI Scale</h3>
    <div style="position:relative;height:20px;border-radius:10px;overflow:hidden;display:flex;margin-bottom:10px;">
      <div style="flex:1;background:#64b5f6;"></div>
      <div style="flex:2;background:#66bb6a;"></div>
      <div style="flex:2;background:#ffa726;"></div>
      <div style="flex:2;background:#ef5350;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:#999;margin-bottom:15px;">
      <span>16<br>Under</span>
      <span>18.5<br>Normal</span>
      <span>25<br>Over</span>
      <span>30<br>Obese</span>
      <span>40+</span>
    </div>
    <div style="font-size:20px;font-weight:700;color:<?= $bmi_color ?>;">
      <?= $latest['bmi'] ?> — <?= $bmi_cat ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Add Record Form -->
    <div class="col-md-4">
      <div class="form-container">
        <h3 style="margin:0 0 20px;font-size:16px;font-weight:700;">Record Progress</h3>
        <form method="POST">
          <div><label>Date *</label><input type="date" name="date" value="<?= date('Y-m-d') ?>" required></div>
          <div style="margin-top:12px;"><label>Weight (kg) *</label><input type="number" name="weight" step="0.1" placeholder="e.g. 72" required></div>
          <div style="margin-top:12px;"><label>Height (cm) *</label><input type="number" name="height" step="0.1" placeholder="e.g. 175" required></div>
          <div style="margin-top:12px;"><label>Notes</label><textarea name="notes" placeholder="Any notes..."></textarea></div>
          <button type="submit" class="btn app-btn-primary w-100 mt-3"><i class="fas fa-save"></i> Save Record</button>
        </form>
      </div>
    </div>

    <!-- Monthly Progress Table -->
    <div class="col-md-8">
      <div class="table-container">
        <div class="table-header"><h3>Monthly Progress Report</h3></div>
        <table class="modern-table">
          <thead>
            <tr>
              <th>Month</th>
              <th>Weight (kg)</th>
              <th>BMI</th>
              <th>Height (cm)</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($records)): ?>
            <tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa;">No records yet. Add your first entry!</td></tr>
            <?php else: foreach($records as $i => $r):
              [$cat,$col,$cls] = getBMIInfo($r['bmi']);
            ?>
            <tr>
              <td>
                <?= date('M Y', strtotime($r['date'])) ?>
                <?php if($i===0): ?><span style="background:var(--active-color);color:white;font-size:10px;padding:2px 7px;border-radius:20px;margin-left:6px;">Latest</span><?php endif; ?>
              </td>
              <td><strong><?= $r['weight'] ?></strong></td>
              <td><span style="color:<?= $col ?>;font-weight:600;"><?= $r['bmi'] ?: '—' ?></span></td>
              <td><?= $r['height'] ?: '—' ?></td>
              <td style="font-size:12px;color:#666;"><?= htmlspecialchars($r['notes'] ?? '—') ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>