<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    header("Location: ../index.php"); exit();
}
$page = 'workout-plans';
$trainer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Edit mode
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$plan = null;
if ($edit_id) {
    $plan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plan_library WHERE id=$edit_id"));
    if (!$plan) { header("Location: workout-plans.php"); exit(); }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $difficulty  = mysqli_real_escape_string($conn, $_POST['difficulty']);
    $duration    = (int)$_POST['duration_weeks'];
    $exercises   = mysqli_real_escape_string($conn, $_POST['exercises']);

    if (!$name || !$category || !$difficulty || !$duration) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($edit_id) {
            mysqli_query($conn, "UPDATE workout_plan_library SET name='$name', description='$description',
                category='$category', difficulty='$difficulty', duration_weeks=$duration, exercises='$exercises'
                WHERE id=$edit_id");
            header("Location: workout-plans.php?success=Plan updated successfully!"); exit();
        } else {
            mysqli_query($conn, "INSERT INTO workout_plan_library (name, description, category, difficulty, duration_weeks, exercises, created_by)
                VALUES ('$name', '$description', '$category', '$difficulty', $duration, '$exercises', '$trainer_email')");
            header("Location: workout-plans.php?success=Plan created successfully!"); exit();
        }
    }
}

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <a href="workout-plans.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Workout Plans
    </a>

    <div class="page-header">
      <h1 class="page-title"><?= $edit_id ? 'Edit Plan' : 'Create Plan' ?></h1>
      <p class="page-subtitle"><?= $edit_id ? 'Update the workout plan details' : 'Add a new workout plan to the library' ?></p>
    </div>

    <?php if($error): ?>
      <div class="app-alert app-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container" style="max-width:800px;">
      <form method="POST">

        <div class="section">
          <h3>Plan Details</h3>
          <p class="section-subtitle">Basic information about this workout plan</p>

          <div class="form-row">
            <div>
              <label>Plan Name *</label>
              <input type="text" name="name" placeholder="e.g. Strength Training Pro" value="<?= htmlspecialchars($plan['name'] ?? '') ?>" required>
            </div>
            <div>
              <label>Category *</label>
              <select name="category" required>
                <option value="">Select Category</option>
                <?php foreach(['Strength','Cardio','General','Athletic'] as $c): ?>
                <option <?= ($plan['category'] ?? '')===$c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label>Description</label>
            <textarea name="description" placeholder="Describe what this plan involves..."><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="section">
          <h3>Plan Configuration</h3>

          <div class="form-row">
            <div>
              <label>Difficulty *</label>
              <select name="difficulty" required>
                <option value="">Select Difficulty</option>
                <?php foreach(['Beginner','Intermediate','Advanced'] as $d): ?>
                <option <?= ($plan['difficulty'] ?? '')===$d ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Duration (weeks) *</label>
              <input type="number" name="duration_weeks" min="1" max="52" placeholder="e.g. 8" value="<?= htmlspecialchars($plan['duration_weeks'] ?? '') ?>" required>
            </div>
          </div>
        </div>

        <div class="section">
          <h3>Exercises</h3>
          <p class="section-subtitle">List the exercises included in this plan</p>
          <div>
            <label>Exercises</label>
            <textarea name="exercises" rows="5" placeholder="e.g. Bench Press: 4x8, Squat: 4x8, Deadlift: 3x6..."><?= htmlspecialchars($plan['exercises'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display:flex;gap:15px;">
          <a href="workout-plans.php" class="btn app-btn-secondary" style="flex:1;text-align:center;"><i class="fas fa-times"></i> Cancel</a>
          <button type="submit" class="btn app-btn-primary" style="flex:1;"><i class="fas fa-save"></i> <?= $edit_id ? 'Update Plan' : 'Create Plan' ?></button>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>