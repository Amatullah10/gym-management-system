<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only customer to access
if ($_SESSION['role'] != 'customer') {
    header("Location: ../index.php");
    exit();
}

$page = 'dashboard'; // For active sidebar highlighting

// Fetch statistics

// Total Members
$total_members_query = "SELECT COUNT(*) as total FROM members";
$total_result = mysqli_query($conn, $total_members_query);
$total_members = mysqli_fetch_assoc($total_result)['total'];

// Active Members
$active_members_query = "SELECT COUNT(*) as active FROM members WHERE membership_status = 'Active'";
$active_result = mysqli_query($conn, $active_members_query);
$active_members = mysqli_fetch_assoc($active_result)['active'];

// Today's Attendance
$today = date('Y-m-d');
$attendance_query = "SELECT COUNT(*) as today FROM attendance WHERE DATE(check_in) = '$today'";
$attendance_result = mysqli_query($conn, $attendance_query);
$today_attendance = mysqli_fetch_assoc($attendance_result)['today'];

// Total Staff
$staff_query = "SELECT COUNT(*) as total FROM users WHERE role != 'customer'";
$staff_result = mysqli_query($conn, $staff_query);
$total_staff = mysqli_fetch_assoc($staff_result)['total'];

// Fetch recent members
$recent_members_query = "SELECT id, full_name, email, phone, start_date, membership_status 
FROM members 
ORDER BY id DESC 
LIMIT 5";
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
  <title>Dashboard - FitnessPro</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">

  <style>
  body {
    margin: 0 !important;
    padding: 0 !important;
  }
  .main-wrapper {
    margin-top: 0 !important;
    padding-top: 0 !important;
  }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['email']) ?>!</p>
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
        <div class="stat-icon orange">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
          <h3><?= $today_attendance ?></h3>
          <p>Today's Attendance</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="stat-info">
          <h3><?= $total_staff ?></h3>
          <p>Total Staff</p>
        </div>
      </div>

    </div>

    <!-- Welcome Card -->
    <div class="section mb-4">
      <h3>Welcome <?= htmlspecialchars($_SESSION['email']) ?>!</h3>
      <p class="section-subtitle">You are logged in as <strong>member</strong>. Use the sidebar menu to navigate through different sections.</p>
    </div>
    
    <!-- Recent Members Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Recent Members</h3>
      </div>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Join Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($recent_members) > 0): ?>
            <?php foreach ($recent_members as $member): ?>
            <tr>
              <td><?= htmlspecialchars($member['id']) ?></td>
              <td><?= htmlspecialchars($member['full_name']) ?></td>
              <td><?= htmlspecialchars($member['email']) ?></td>
              <td><?= htmlspecialchars($member['phone']) ?></td>
              <td><?= htmlspecialchars($member['start_date']) ?></td>
              <td>
                <span class="status-badge <?= strtolower($member['membership_status']) ?>">
                  <?= strtoupper(htmlspecialchars($member['membership_status'])) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No members found.</td>
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