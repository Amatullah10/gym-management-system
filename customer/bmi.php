<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'bmi';
$email = $_SESSION['email'];

$member_res = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);
if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'><h3>Account Error</h3><p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p><a href='../auth/logout.php'>Logout</a></div>");
}
$member_id = $member['id'];

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = round($weight / (($height / 100) * ($height / 100)), 2);
    $recorded_date = $_POST['recorded_date'];
    $insert = mysqli_query($conn, "INSERT INTO bmi_progress (member_id, height, weight, bmi, recorded_date) VALUES ('$member_id', '$height', '$weight', '$bmi', '$recorded_date')");
    if ($insert) { $success = "BMI calculated and saved! Your BMI is <strong>$bmi</strong>"; }
    else { $error = "Something went wrong. Error: " . mysqli_error($conn); }
}

$records = [];
$res = mysqli_query($conn, "SELECT * FROM bmi_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC");
while ($row = mysqli_fetch_assoc($res)) { $records[] = $row; }

function getBMICategory($bmi) {
    if ($bmi < 18.5) return ['Underweight', 'inactive'];
    if ($bmi < 25)   return ['Normal', 'active'];
    if ($bmi < 30)   return ['Overweight', 'pending'];
    return ['Obese', 'expired'];
}
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">BMI Progress</h1>
        <p class="page-subtitle">Track your Body Mass Index</p>
      </div>
    </div>

    <?php if ($success): ?><div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div><?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-arrow-down"></i></div><div class="stat-info"><h3>&lt; 18.5</h3><p>Underweight</p></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-check"></i></div><div class="stat-info"><h3>18.5 - 24.9</h3><p>Normal</p></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="stat-info"><h3>25 - 29.9</h3><p>Overweight</p></div></div>
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-circle-exclamation"></i></div><div class="stat-info"><h3>&gt; 30</h3><p>Obese</p></div></div>
    </div>

    <div class="form-container">
      <h3 style="margin-bottom:20px; font-size:18px; font-weight:600;">Calculate & Record BMI</h3>
      <form method="POST">
        <div class="form-row">
          <div><label>Height (cm)</label><input type="number" name="height" step="0.1" placeholder="e.g. 170" required></div>
          <div><label>Weight (kg)</label><input type="number" name="weight" step="0.1" placeholder="e.g. 70" required></div>
          <div><label>Date</label><input type="date" name="recorded_date" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <button type="submit" class="btn app-btn-primary" style="margin-top:15px;"><i class="fa-solid fa-calculator"></i> Calculate & Save BMI</button>
      </form>
    </div>

    <div class="members-table-container">
      <div class="table-header"><h3>BMI History</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Height (cm)</th><th>Weight (kg)</th><th>BMI</th><th>Category</th><th>Date</th></tr></thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): [$cat, $cls] = getBMICategory($r['bmi']); ?>
              <tr><td><?= $i+1 ?></td><td><?= $r['height'] ?> cm</td><td><?= $r['weight'] ?> kg</td><td><strong><?= $r['bmi'] ?></strong></td><td><span class="status-badge <?= $cls ?>"><?= $cat ?></span></td><td><?= $r['recorded_date'] ?></td></tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center" style="padding:30px; color:#aaa;">No BMI records yet. Calculate your first BMI above!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>