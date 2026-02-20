
<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only receptionist to access
if ($_SESSION['role'] != 'receptionist') {
    header("Location: ../index.php");
    exit();
}

$page = 'dashboard'; // For active sidebar highlighting

// Fetch statistics from SAME TABLES as admin
$total_members_query = "SELECT COUNT(*) as total FROM members";
$total_result = mysqli_query($conn, $total_members_query);
$total_members = mysqli_fetch_assoc($total_result)['total'];

$active_members_query = "SELECT COUNT(*) as active FROM members WHERE membership_status = 'Active'";
$active_result = mysqli_query($conn, $active_members_query);
$active_members = mysqli_fetch_assoc($active_result)['active'];

$inactive_members_query = "SELECT COUNT(*) as inactive FROM members WHERE membership_status = 'Inactive'";
$inactive_result = mysqli_query($conn, $inactive_members_query);
$inactive_members = mysqli_fetch_assoc($inactive_result)['inactive'];

// Today's attendance
$today = date('Y-m-d');
$today_attendance_query = "SELECT COUNT(*) as present FROM attendance WHERE attendance_date = '$today' AND status = 'Present'";
$today_result = mysqli_query($conn, $today_attendance_query);
$today_attendance = mysqli_fetch_assoc($today_result)['present'];

// Fetch recent members from SAME TABLE as admin
$recent_members_query = "SELECT * FROM members ORDER BY created_at DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_members_query);
$recent_members = [];
while($row = mysqli_fetch_assoc($recent_result)) {
    $recent_members[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receptionist Dashboard - Gym Management</title>
  
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
    
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Welcome, Receptionist! 👋</h1>
        <p class="page-subtitle">Here's your overview for today</p>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_members ?></h3>
          <p>Total Members</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon active">
          <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-info">
          <h3><?= $active_members ?></h3>
          <p>Active Members</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon inactive">
          <i class="fa-solid fa-user-clock"></i>
        </div>
        <div class="stat-info">
          <h3><?= $inactive_members ?></h3>
          <p>Inactive Members</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
          <h3><?= $today_attendance ?></h3>
          <p>Today's Attendance</p>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <h2 style="font-size: 20px; margin-top: 20px; margin-bottom: 20px;">Quick Actions</h2>
    <div class="stats-grid">
      <a href="member-entry.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon total">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <div class="stat-info">
          <h3 style="font-size: 16px; font-weight: 600;">Add New Member</h3>
          <p>Register a new gym member</p>
        </div>
      </a>
      
      <a href="members.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon active">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-info">
          <h3 style="font-size: 16px; font-weight: 600;">View Members</h3>
          <p>Manage all members</p>
        </div>
      </a>
      
      <a href="mark-attendance.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon orange">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
          <h3 style="font-size: 16px; font-weight: 600;">Mark Attendance</h3>
          <p>Record daily attendance</p>
        </div>
      </a>
    </div>
    
    <!-- Recent Members -->
    <div class="members-table-container" style="margin-top: 30px;">
      <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Recent Members</h3>
        <a href="members.php" style="color: var(--active-color); text-decoration: none; font-size: 14px;">
          View All <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Contact</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($recent_members) > 0): ?>
            <?php foreach ($recent_members as $member): 
              $initial = strtoupper(substr($member['full_name'], 0, 1));
              $plan_type = strtolower(explode(' - ', $member['membership_type'])[0]);
            ?>
            <tr>
              <!-- Member -->
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($member['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($member['email']) ?></span>
                  </div>
                </div>
              </td>
              
              <!-- Contact -->
              <td>
                <div style="color: #666; font-size: 13px;">
                  <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($member['phone']) ?>
                </div>
              </td>
              
              <!-- Plan -->
              <td>
                <span class="plan-badge <?= $plan_type ?>">
                  <?= htmlspecialchars($member['membership_type']) ?>
                </span>
              </td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= strtolower($member['membership_status']) ?>">
                  <?= htmlspecialchars($member['membership_status']) ?>
                </span>
              </td>
              
              <!-- Joined -->
              <td>
                <span style="color: #666; font-size: 13px;">
                  <?= date('M d, Y', strtotime($member['created_at'])) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center">No members yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>