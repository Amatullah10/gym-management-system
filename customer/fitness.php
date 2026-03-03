<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'fitness';
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
    $exercise = mysqli_real_escape_string($conn, $_POST['exercise_name']);
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];
    $duration = $_POST['duration_minutes'];
    $recorded_date = $_POST['recorded_date'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $insert = mysqli_query($conn, "INSERT INTO fitness_progress (member_id, exercise_name, sets, reps, duration_minutes, recorded_date, notes) VALUES ('$member_id', '$exercise', '$sets', '$reps', '$duration', '$recorded_date', '$notes')");
    if ($insert) { $success = "Exercise logged successfully!"; }
    else { $error = "Something went wrong. Error: " . mysqli_error($conn); }
}

$records = [];
$res = mysqli_query($conn, "SELECT * FROM fitness_progress WHERE member_id = '$member_id' ORDER BY recorded_date DESC");
while ($row = mysqli_fetch_assoc($res)) { $records[] = $row; }
$total_duration = array_sum(array_column($records, 'duration_minutes'));
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Fitness Progress</h1>
        <p class="page-subtitle">Log and track your workouts</p>
      </div>
    </div>

    <?php if ($success): ?><div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div><?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-dumbbell"></i></div><div class="stat-info"><h3><?= count($records) ?></h3><p>Total Exercises Logged</p></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-clock"></i></div><div class="stat-info"><h3><?= $total_duration ?> min</h3><p>Total Duration</p></div></div>
    </div>

    <div class="form-container">
      <h3 style="margin-bottom:20px; font-size:18px; font-weight:600;">Log Exercise</h3>
      <form method="POST">
        <div class="form-row">
          <div><label>Exercise Name</label><input type="text" name="exercise_name" placeholder="e.g. Push Ups" required></div>
          <div><label>Sets</label><input type="number" name="sets" placeholder="e.g. 3"></div>
          <div><label>Reps</label><input type="number" name="reps" placeholder="e.g. 15"></div>
        </div>
        <div class="form-row">
          <div><label>Duration (minutes)</label><input type="number" name="duration_minutes" placeholder="e.g. 30"></div>
          <div><label>Date</label><input type="date" name="recorded_date" value="<?= date('Y-m-d') ?>" required></div>
          <div><label>Notes (Optional)</label><input type="text" name="notes" placeholder="Any notes..."></div>
        </div>
        <button type="submit" class="btn app-btn-primary" style="margin-top:15px;"><i class="fa-solid fa-plus"></i> Log Exercise</button>
      </form>
    </div>

    <div class="members-table-container">
      <div class="table-header"><h3>Exercise History</h3></div>
      <table class="members-table">
        <thead><tr><th>#</th><th>Exercise</th><th>Sets</th><th>Reps</th><th>Duration</th><th>Date</th><th>Notes</th></tr></thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): ?>
              <tr><td><?= $i+1 ?></td><td><strong><?= htmlspecialchars($r['exercise_name']) ?></strong></td><td><?= $r['sets'] ?: '-' ?></td><td><?= $r['reps'] ?: '-' ?></td><td><?= $r['duration_minutes'] ? $r['duration_minutes'].' min' : '-' ?></td><td><?= $r['recorded_date'] ?></td><td><?= htmlspecialchars($r['notes']) ?: '-' ?></td></tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center" style="padding:30px; color:#aaa;">No exercises logged yet. Start logging above!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>