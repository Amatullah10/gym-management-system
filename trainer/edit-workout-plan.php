<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in and has proper role
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only trainer and admin to access this page
if ($_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page = 'assigned-members'; // For active sidebar highlighting

$success_message = '';
$error_message = '';

// Get member ID
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch member details
$sql = "SELECT m.*, wp.goal, wp.current_plan, wp.progress, wp.status as workout_status
        FROM members m
        LEFT JOIN workout_plans wp ON m.id = wp.member_id
        WHERE m.id = $member_id";
$result = mysqli_query($conn, $sql);
$member = mysqli_fetch_assoc($result);

if (!$member) {
    header("Location: assigned-members.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal = mysqli_real_escape_string($conn, $_POST['goal']);
    $current_plan = mysqli_real_escape_string($conn, $_POST['current_plan']);
    $progress = intval($_POST['progress']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Check if workout plan exists
    $check_sql = "SELECT id FROM workout_plans WHERE member_id = $member_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing plan
        $update_sql = "UPDATE workout_plans SET 
                       goal = '$goal',
                       current_plan = '$current_plan',
                       progress = $progress,
                       status = '$status'
                       WHERE member_id = $member_id";
        
        if (mysqli_query($conn, $update_sql)) {
            header("Location: assigned-members.php?success=Workout plan updated successfully!");
            exit();
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    } else {
        // Insert new plan
        $insert_sql = "INSERT INTO workout_plans (member_id, goal, current_plan, progress, status) 
                       VALUES ($member_id, '$goal', '$current_plan', $progress, '$status')";
        
        if (mysqli_query($conn, $insert_sql)) {
            header("Location: assigned-members.php?success=Workout plan created successfully!");
            exit();
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Workout Plan - <?= htmlspecialchars($member['full_name']) ?></title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    <div class="form-container mx-auto" style="max-width:900px;">

      <!-- Back Button -->
      <div class="mb-3">
        <a href="assigned-members.php" class="btn app-btn-secondary" style="padding: 8px 16px;">
          <i class="fa-solid fa-arrow-left"></i> Back to Members
        </a>
      </div>

      <h2><i class="fa-solid fa-dumbbell"></i> Edit Workout Plan</h2>

      <!-- Error Message -->
      <?php if ($error_message): ?>
        <div class="app-alert app-alert-error">
          <i class="fa-solid fa-circle-exclamation"></i> <?= $error_message ?>
        </div>
      <?php endif; ?>

      <!-- Member Info Card -->
      <div class="section">
        <h3><i class="fa-solid fa-user"></i> Member Information</h3>
        <div class="member-cell">
          <div class="member-avatar"><?= strtoupper(substr($member['full_name'], 0, 1)) ?></div>
          <div class="member-info">
            <span class="name"><?= htmlspecialchars($member['full_name']) ?></span>
            <span class="joined"><?= htmlspecialchars($member['email']) ?> | <?= htmlspecialchars($member['phone']) ?></span>
          </div>
        </div>
      </div>

      <form method="POST">

        <!-- Workout Plan Details -->
        <div class="section">
          <h3><i class="fa-solid fa-clipboard-list"></i> Workout Plan Details</h3>
          <p class="section-subtitle">Assign goals and track member progress</p>

          <div class="form-row">
            <div>
              <label>Goal *</label>
              <select name="goal" required>
                <option value="">Select Goal</option>
                <option <?= ($member['goal'] == 'Build Muscle') ? 'selected' : '' ?>>Build Muscle</option>
                <option <?= ($member['goal'] == 'Weight Loss') ? 'selected' : '' ?>>Weight Loss</option>
                <option <?= ($member['goal'] == 'General Fitness') ? 'selected' : '' ?>>General Fitness</option>
                <option <?= ($member['goal'] == 'Strength & Toning') ? 'selected' : '' ?>>Strength & Toning</option>
                <option <?= ($member['goal'] == 'Athletic Performance') ? 'selected' : '' ?>>Athletic Performance</option>
                <option <?= ($member['goal'] == 'Endurance Training') ? 'selected' : '' ?>>Endurance Training</option>
              </select>
            </div>

            <div>
              <label>Current Plan *</label>
              <select name="current_plan" required>
                <option value="">Select Plan</option>
                <option <?= ($member['current_plan'] == 'Strength Training Pro') ? 'selected' : '' ?>>Strength Training Pro</option>
                <option <?= ($member['current_plan'] == 'Fat Burn Challenge') ? 'selected' : '' ?>>Fat Burn Challenge</option>
                <option <?= ($member['current_plan'] == 'Balanced Fitness') ? 'selected' : '' ?>>Balanced Fitness</option>
                <option <?= ($member['current_plan'] == 'Women\'s Strength') ? 'selected' : '' ?>>Women's Strength</option>
                <option <?= ($member['current_plan'] == 'Sports Performance') ? 'selected' : '' ?>>Sports Performance</option>
                <option <?= ($member['current_plan'] == 'Cardio Blast') ? 'selected' : '' ?>>Cardio Blast</option>
                <option <?= ($member['current_plan'] == 'CrossFit Advanced') ? 'selected' : '' ?>>CrossFit Advanced</option>
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
                <option <?= ($member['workout_status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                <option <?= ($member['workout_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option <?= ($member['workout_status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-3">
          <button type="submit" class="btn app-btn-primary w-100">
            <i class="fa-solid fa-floppy-disk"></i> Save Workout Plan
          </button>
          <a href="assigned-members.php" class="btn app-btn-secondary w-100">
            <i class="fa-solid fa-xmark"></i> Cancel
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>