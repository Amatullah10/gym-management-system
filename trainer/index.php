<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only trainer to access
if ($_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$page = 'dashboard'; // For active sidebar highlighting

// Fetch statistics
$total_members_query = "SELECT COUNT(*) as total FROM members";
$total_result = mysqli_query($conn, $total_members_query);
$total_members = mysqli_fetch_assoc($total_result)['total'];

$active_plans_query = "SELECT COUNT(*) as active FROM workout_plans WHERE status = 'Active'";
$active_result = mysqli_query($conn, $active_plans_query);
$active_plans = mysqli_fetch_assoc($active_result)['active'];

$pending_plans_query = "SELECT COUNT(*) as pending FROM workout_plans WHERE status = 'Pending'";
$pending_result = mysqli_query($conn, $pending_plans_query);
$pending_plans = mysqli_fetch_assoc($pending_result)['pending'];

$avg_progress_query = "SELECT AVG(progress) as avg_progress FROM workout_plans WHERE status = 'Active'";
$avg_result = mysqli_query($conn, $avg_progress_query);
$avg_progress = round(mysqli_fetch_assoc($avg_result)['avg_progress'] ?? 0);

// Fetch recent members
$recent_members_query = "SELECT 
    m.id,
    m.full_name,
    m.email,
    m.created_at,
    COALESCE(wp.goal, 'Not Set') as goal,
    COALESCE(wp.current_plan, 'Not Assigned') as current_plan,
    COALESCE(wp.progress, 0) as progress,
    COALESCE(wp.status, 'Pending') as workout_status
FROM members m
LEFT JOIN workout_plans wp ON m.id = wp.member_id
ORDER BY m.created_at DESC
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
  <title>Trainer Dashboard - Gym Management</title>
  
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
        <h1 class="page-title">Welcome, Trainer! 👋</h1>
        <p class="page-subtitle">Here's your training overview for today</p>
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
          <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div class="stat-info">
          <h3><?= $active_plans ?></h3>
          <p>Active Plans</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-info">
          <h3><?= $pending_plans ?></h3>
          <p>Pending Plans</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-info">
          <h3><?= $avg_progress ?>%</h3>
          <p>Avg Progress</p>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="section mb-4">
      <h3><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
      <p class="section-subtitle">Frequently used features</p>
      
      <div class="row g-3">
        <div class="col-md-4">
          <a href="assigned-member.php" class="quick-action-card">
            <i class="fa-solid fa-users-cog"></i>
            <div>
              <h4>Assigned Members</h4>
              <p>View and manage all members</p>
            </div>
          </a>
        </div>
        
        <div class="col-md-4">
          <a href="mark-attendance.php" class="quick-action-card">
            <i class="fa-solid fa-calendar-check"></i>
            <div>
              <h4>Mark Attendance</h4>
              <p>Record member attendance</p>
            </div>
          </a>
        </div>
        
        <div class="col-md-4">
          <a href="view-announcements.php" class="quick-action-card">
            <i class="fa-solid fa-bullhorn"></i>
            <div>
              <h4>Announcements</h4>
              <p>View gym announcements</p>
            </div>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Recent Members -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Recent Members</h3>
        <a href="assigned-member.php" style="color: var(--active-color); text-decoration: none; font-size: 14px;">
          View All <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Goal</th>
            <th>Current Plan</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($recent_members) > 0): ?>
            <?php foreach ($recent_members as $member): 
              $initial = strtoupper(substr($member['full_name'], 0, 1));
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
              
              <!-- Goal -->
              <td>
                <span style="color: #666;"><?= htmlspecialchars($member['goal']) ?></span>
              </td>
              
              <!-- Current Plan -->
              <td>
                <span style="color: #666;"><?= htmlspecialchars($member['current_plan']) ?></span>
              </td>
              
              <!-- Progress -->
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="flex: 1; height: 8px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                    <div style="height: 100%; background: var(--active-color); width: <?= $member['progress'] ?>%; transition: width 0.3s;"></div>
                  </div>
                  <span style="font-weight: 600; color: #333; min-width: 45px;"><?= $member['progress'] ?>%</span>
                </div>
              </td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= strtolower($member['workout_status']) ?>">
                  <?= htmlspecialchars($member['workout_status']) ?>
                </span>
              </td>
              
              <!-- Actions -->
              <td>
                <div class="action-buttons">
                  <a href="view-member-details.php?id=<?= $member['id'] ?>" class="btn-action view" title="View Details">
                    <i class="fa-regular fa-eye"></i>
                  </a>
                  <a href="edit-workout-plan.php?id=<?= $member['id'] ?>" class="btn-action edit" title="Edit Workout Plan">
                    <i class="fa-solid fa-dumbbell"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No members yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Quick Action Cards */
.quick-action-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.quick-action-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(148, 22, 20, 0.15);
  border-color: var(--active-color);
}

.quick-action-card i {
  font-size: 32px;
  color: var(--active-color);
  min-width: 40px;
}

.quick-action-card h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  font-weight: 600;
  color: #1a1a1a;
}

.quick-action-card p {
  margin: 0;
  font-size: 13px;
  color: #999;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>

</body>
</html>