<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    header("Location: ../index.php"); exit();
}
$page = 'assigned-members';
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$member_id) { header("Location: assigned-member.php"); exit(); }

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT m.*, wp.goal, wp.current_plan, wp.progress, wp.status as workout_status
    FROM members m LEFT JOIN workout_plans wp ON m.id=wp.member_id WHERE m.id=$member_id"));
if (!$member) { header("Location: assigned-member.php"); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal    = mysqli_real_escape_string($conn, $_POST['goal']);
    $plan    = mysqli_real_escape_string($conn, $_POST['current_plan']);
    $prog    = (int)$_POST['progress'];
    $status  = mysqli_real_escape_string($conn, $_POST['status']);
    $exists  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM workout_plans WHERE member_id=$member_id"));
    if ($exists) {
        mysqli_query($conn, "UPDATE workout_plans SET goal='$goal', current_plan='$plan', progress=$prog, status='$status' WHERE member_id=$member_id");
    } else {
        mysqli_query($conn, "INSERT INTO workout_plans (member_id, goal, current_plan, progress, status) VALUES ($member_id,'$goal','$plan',$prog,'$status')");
    }
    header("Location: assigned-member.php?success=Workout plan updated successfully!"); exit();
}

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <a href="assigned-member.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Members
    </a>

    <div class="page-header">
      <h1 class="page-title">Edit Workout Plan</h1>
      <p class="page-subtitle">Update workout goals and progress for <?= htmlspecialchars($member['full_name']) ?></p>
    </div>

    <div class="form-container" style="max-width:800px;">

      <div class="section">
        <h3>Member</h3>
        <div class="member-cell">
          <div class="member-avatar"><?= strtoupper(substr($member['full_name'],0,1)) ?></div>
          <div class="member-info">
            <span class="name"><?= htmlspecialchars($member['full_name']) ?></span>
            <span class="meta"><?= htmlspecialchars($member['email']) ?> &bull; <?= htmlspecialchars($member['phone']) ?></span>
          </div>
        </div>
      </div>

      <form method="POST">
        <div class="section">
          <h3>Workout Plan Details</h3>
          <p class="section-subtitle">Assign goals and track member progress</p>

          <div class="form-row">
            <div>
              <label>Goal *</label>
              <select name="goal" required>
                <option value="">Select Goal</option>
                <?php foreach(['Build Muscle','Weight Loss','General Fitness','Strength & Toning','Athletic Performance','Endurance Training'] as $g): ?>
                <option <?= $member['goal']==$g?'selected':'' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Current Plan *</label>
              <select name="current_plan" required>
                <option value="">Select Plan</option>
                <?php foreach(['Strength Training Pro','Fat Burn Challenge','Balanced Fitness',"Women's Strength",'Sports Performance','Cardio Blast','CrossFit Advanced'] as $p): ?>
                <option <?= $member['current_plan']==$p?'selected':'' ?>><?= $p ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Progress (%) *</label>
              <input type="number" name="progress" min="0" max="100" value="<?= $member['progress'] ?? 0 ?>" required>
            </div>
            <div>
              <label>Status *</label>
              <select name="status" required>
                <option value="">Select Status</option>
                <?php foreach(['Active','Pending','Inactive'] as $s): ?>
                <option <?= ($member['workout_status']==$s)?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div style="display:flex;gap:15px;">
          <button type="submit" class="btn app-btn-primary" style="flex:1;"><i class="fas fa-save"></i> Save Workout Plan</button>
          <a href="assigned-member.php" class="btn app-btn-secondary" style="flex:1;text-align:center;"><i class="fas fa-times"></i> Cancel</a>
        </div>
      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>