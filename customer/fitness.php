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

$page = 'fitness';

$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exercise_name = $_POST['exercise_name'];
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];
    $duration = $_POST['duration_minutes'];
    $recorded_date = $_POST['recorded_date'];
    $notes = $_POST['notes'];
    $insert = mysqli_query($conn, "INSERT INTO fitness_progress (member_id, exercise_name, sets, reps, duration_minutes, recorded_date, notes) VALUES ('$member_id', '$exercise_name', '$sets', '$reps', '$duration', '$recorded_date', '$notes')");
    if ($insert) {
        $success = "Fitness record added successfully!";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}

$records = [];
$res = mysqli_query($conn, "SELECT * FROM fitness_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
}

$total_exercises = count($records);
$total_duration = array_sum(array_column($records, 'duration_minutes'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fitness Progress - FitnessPro</title>
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
      <h1 class="page-title">Fitness Progress</h1>
      <p class="page-subtitle">Track your workouts and exercises</p>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-dumbbell"></i></div>
        <div class="stat-info"><h3><?= $total_exercises ?></h3><p>Total Exercises</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= $total_duration ?> min</h3><p>Total Duration</p></div>
      </div>
    </div>

    <!-- Add Fitness Form -->
    <div class="form-container mb-4">
      <h3 style="margin-bottom:20px; color:#1a1a1a;">Log Exercise</h3>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Exercise Name</label>
            <input type="text" name="exercise_name" placeholder="e.g. Push Ups" required>
          </div>
          <div>
            <label>Sets</label>
            <input type="number" name="sets" placeholder="e.g. 3">
          </div>
          <div>
            <label>Reps</label>
            <input type="number" name="reps" placeholder="e.g. 15">
          </div>
        </div>
        <div class="form-row">
          <div>
            <label>Duration (minutes)</label>
            <input type="number" name="duration_minutes" placeholder="e.g. 30">
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
          <i class="fa-solid fa-plus"></i> Log Exercise
        </button>
      </form>
    </div>

    <!-- Fitness Records Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Exercise History</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Exercise</th>
            <th>Sets</th>
            <th>Reps</th>
            <th>Duration</th>
            <th>Date</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($r['exercise_name']) ?></strong></td>
                <td><?= $r['sets'] ?: '-' ?></td>
                <td><?= $r['reps'] ?: '-' ?></td>
                <td><?= $r['duration_minutes'] ? $r['duration_minutes'].' min' : '-' ?></td>
                <td><?= $r['recorded_date'] ?></td>
                <td><?= $r['notes'] ?: '-' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center" style="color:#aaa; padding:30px;">No records found. Log your first exercise!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>