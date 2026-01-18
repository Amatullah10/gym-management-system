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

// Get member ID
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch member details with workout plan
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

// Extract plan type
$plan_type = explode(' - ', $member['membership_type'])[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Details - <?= htmlspecialchars($member['full_name']) ?></title>
  
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
    
    <!-- Back Button -->
    <div class="mb-3">
      <a href="assigned-members.php" class="btn app-btn-secondary" style="padding: 8px 16px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Members
      </a>
    </div>

    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Member Details</h1>
          <p class="page-subtitle">Complete profile and workout information</p>
        </div>
        <a href="edit-workout-plan.php?id=<?= $member['id'] ?>" class="btn app-btn-primary">
          <i class="fa-solid fa-dumbbell"></i> Edit Workout Plan
        </a>
      </div>
    </div>

    <div style="max-width: 1200px;">
      
      <!-- Personal Information -->
      <div class="section">
        <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
        
        <div class="member-cell mb-4">
          <div class="member-avatar" style="width: 60px; height: 60px; font-size: 24px;">
            <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
          </div>
          <div class="member-info">
            <span class="name" style="font-size: 20px;"><?= htmlspecialchars($member['full_name']) ?></span>
            <span class="joined">Member since <?= date('F Y', strtotime($member['created_at'])) ?></span>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Email</label>
            <div style="padding: 10px 0; color: #666;">
              <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($member['email']) ?>
            </div>
          </div>
          <div>
            <label>Phone</label>
            <div style="padding: 10px 0; color: #666;">
              <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($member['phone']) ?>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Date of Birth</label>
            <div style="padding: 10px 0; color: #666;">
              <?= date('F d, Y', strtotime($member['dob'])) ?>
            </div>
          </div>
          <div>
            <label>Gender</label>
            <div style="padding: 10px 0; color: #666;">
              <?= htmlspecialchars($member['gender']) ?>
            </div>
          </div>
        </div>

        <div>
          <label>Address</label>
          <div style="padding: 10px 0; color: #666;">
            <?= htmlspecialchars($member['address']) ?>
          </div>
        </div>
      </div>

      <!-- Membership Information -->
      <div class="section">
        <h3><i class="fa-solid fa-id-card"></i> Membership Information</h3>
        
        <div class="form-row">
          <div>
            <label>Membership Type</label>
            <div style="padding: 10px 0;">
              <span class="plan-badge <?= strtolower($plan_type) ?>">
                <?= htmlspecialchars($member['membership_type']) ?>
              </span>
            </div>
          </div>
          <div>
            <label>Status</label>
            <div style="padding: 10px 0;">
              <span class="status-badge <?= strtolower($member['membership_status']) ?>">
                <?= htmlspecialchars($member['membership_status']) ?>
              </span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Duration</label>
            <div style="padding: 10px 0; color: #666;">
              <?= htmlspecialchars($member['duration']) ?>
            </div>
          </div>
          <div>
            <label>Start Date</label>
            <div style="padding: 10px 0; color: #666;">
              <?= date('F d, Y', strtotime($member['start_date'])) ?>
            </div>
          </div>
          <div>
            <label>End Date</label>
            <div style="padding: 10px 0; color: #666;">
              <?= date('F d, Y', strtotime($member['end_date'])) ?>
            </div>
          </div>
        </div>

        <div>
          <label>Fitness Level</label>
          <div style="padding: 10px 0; color: #666;">
            <?= htmlspecialchars($member['fitness_level']) ?>
          </div>
        </div>
      </div>

      <!-- Workout Plan Information -->
      <div class="section">
        <h3><i class="fa-solid fa-dumbbell"></i> Workout Plan Information</h3>
        
        <div class="form-row">
          <div>
            <label>Goal</label>
            <div style="padding: 10px 0; color: #666;">
              <?= htmlspecialchars($member['goal'] ?? 'Not Set') ?>
            </div>
          </div>
          <div>
            <label>Current Plan</label>
            <div style="padding: 10px 0; color: #666;">
              <?= htmlspecialchars($member['current_plan'] ?? 'Not Assigned') ?>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Progress</label>
            <div style="padding: 10px 0;">
              <div class="d-flex align-items-center gap-2">
                <div style="flex: 1; height: 10px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                  <div style="height: 100%; background: var(--active-color); width: <?= $member['progress'] ?? 0 ?>%; transition: width 0.3s;"></div>
                </div>
                <span style="font-weight: 600; color: #333; min-width: 50px;"><?= $member['progress'] ?? 0 ?>%</span>
              </div>
            </div>
          </div>
          <div>
            <label>Workout Status</label>
            <div style="padding: 10px 0;">
              <span class="status-badge <?= strtolower($member['workout_status'] ?? 'pending') ?>">
                <?= htmlspecialchars($member['workout_status'] ?? 'Pending') ?>
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>
    
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>