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

$page = 'weight';

// Get member id from members table using session email
$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id, full_name FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $weight = $_POST['weight'];
    $recorded_date = $_POST['recorded_date'];
    $notes = $_POST['notes'];
    $insert = mysqli_query($conn, "INSERT INTO weight_progress (member_id, weight, recorded_date, notes) VALUES ('$member_id', '$weight', '$recorded_date', '$notes')");
    if ($insert) {
        $success = "Weight recorded successfully!";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

// Fetch weight records
$records = [];
$res = mysqli_query($conn, "SELECT * FROM weight_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weight Progress - FitnessPro</title>
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
      <h1 class="page-title">Weight Progress</h1>
      <p class="page-subtitle">Track your weight over time</p>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- Add Weight Form -->
    <div class="form-container mb-4">
      <h3 style="margin-bottom:20px; color:#1a1a1a;">Record Weight</h3>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Weight (kg)</label>
            <input type="number" name="weight" step="0.1" placeholder="e.g. 70.5" required>
          </div>
          <div>
            <label>Date</label>
            <input type="date" name="recorded_date" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div>
            <label>Notes</label>
            <input type="text" name="notes" placeholder="Optional notes">
          </div>
        </div>
        <button type="submit" class="btn app-btn-primary mt-10">
          <i class="fa-solid fa-plus"></i> Add Record
        </button>
      </form>
    </div>

    <!-- Weight Records Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Weight History</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Weight (kg)</th>
            <th>Date</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= $r['weight'] ?> kg</strong></td>
                <td><?= $r['recorded_date'] ?></td>
                <td><?= $r['notes'] ?: '-' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center" style="color:#aaa; padding:30px;">No records found. Add your first weight record!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>