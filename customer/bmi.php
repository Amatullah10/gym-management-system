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

$page = 'bmi';

$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = round($weight / (($height / 100) * ($height / 100)), 2);
    $recorded_date = $_POST['recorded_date'];
    $insert = mysqli_query($conn, "INSERT INTO bmi_progress (member_id, height, weight, bmi, recorded_date) VALUES ('$member_id', '$height', '$weight', '$bmi', '$recorded_date')");
    if ($insert) {
        $success = "BMI recorded successfully! Your BMI is $bmi";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

$records = [];
$res = mysqli_query($conn, "SELECT * FROM bmi_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return ['Underweight', 'orange'];
    if ($bmi < 25) return ['Normal', 'active'];
    if ($bmi < 30) return ['Overweight', 'orange'];
    return ['Obese', 'danger'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BMI Progress - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .main-wrapper { margin-top: 0 !important; padding-top: 0 !important; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">BMI Progress</h1>
      <p class="page-subtitle">Track your Body Mass Index</p>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- BMI Info Cards -->
    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-weight-scale"></i></div>
        <div class="stat-info"><h3>&lt; 18.5</h3><p>Underweight</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon active"><i class="fa-solid fa-check-circle"></i></div>
        <div class="stat-info"><h3>18.5 - 24.9</h3><p>Normal</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3>25 - 29.9</h3><p>Overweight</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div class="stat-info"><h3>&gt; 30</h3><p>Obese</p></div>
      </div>
    </div>

    <!-- Add BMI Form -->
    <div class="form-container mb-4">
      <h3 style="margin-bottom:20px; color:#1a1a1a;">Calculate & Record BMI</h3>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Height (cm)</label>
            <input type="number" name="height" step="0.1" placeholder="e.g. 170" required>
          </div>
          <div>
            <label>Weight (kg)</label>
            <input type="number" name="weight" step="0.1" placeholder="e.g. 70" required>
          </div>
          <div>
            <label>Date</label>
            <input type="date" name="recorded_date" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <button type="submit" class="btn app-btn-primary mt-10">
          <i class="fa-solid fa-calculator"></i> Calculate & Save
        </button>
      </form>
    </div>

    <!-- BMI Records Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>BMI History</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Height (cm)</th>
            <th>Weight (kg)</th>
            <th>BMI</th>
            <th>Category</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r):
              [$cat, $cls] = getBMICategory($r['bmi']);
            ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $r['height'] ?> cm</td>
                <td><?= $r['weight'] ?> kg</td>
                <td><strong><?= $r['bmi'] ?></strong></td>
                <td><span class="status-badge <?= $cls ?>"><?= $cat ?></span></td>
                <td><?= $r['recorded_date'] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center" style="color:#aaa; padding:30px;">No records found. Calculate your first BMI!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>