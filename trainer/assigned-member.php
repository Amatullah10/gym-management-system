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

// Fetch all members with their workout plans
$sql = "SELECT 
    m.id,
    m.full_name,
    m.email,
    m.phone,
    m.membership_type,
    m.membership_status,
    m.created_at,
    COALESCE(wp.goal, 'Not Set') as goal,
    COALESCE(wp.current_plan, 'Not Assigned') as current_plan,
    COALESCE(wp.progress, 0) as progress,
    COALESCE(wp.status, 'Pending') as workout_status
FROM members m
LEFT JOIN workout_plans wp ON m.id = wp.member_id
ORDER BY m.created_at DESC";

$result = mysqli_query($conn, $sql);

$members = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

$total_members = count($members);

// Success message
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assigned Members - Gym Management</title>
  
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
    
    <!-- Success Message -->
    <?php if ($success_message): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Assigned Members</h1>
          <p class="page-subtitle">Manage and track your <?= $total_members ?> assigned members</p>
        </div>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="member-entry.php" class="btn app-btn-primary">
          <i class="fa-solid fa-user-plus"></i> Add Member
        </a>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="d-flex gap-3 mb-4">
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by name or email...">
      </div>
      
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    
    <!-- Members Table -->
    <div class="members-table-container">
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
        <tbody id="membersTableBody">
          <?php if (count($members) > 0): ?>
            <?php foreach ($members as $member): 
              $initial = strtoupper(substr($member['full_name'], 0, 1));
            ?>
            <tr data-status="<?= strtolower($member['workout_status']) ?>">
              
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
                  <button class="btn-action view" onclick="viewMember(<?= $member['id'] ?>)" title="View Details">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                  <button class="btn-action edit" onclick="editWorkoutPlan(<?= $member['id'] ?>)" title="Edit Workout Plan">
                    <i class="fa-solid fa-dumbbell"></i>
                  </button>
                </div>
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

<script>
// View member details
function viewMember(id) {
  window.location.href = 'view-member-details.php?id=' + id;
}

// Edit workout plan
function editWorkoutPlan(id) {
  window.location.href = 'edit-workout-plan.php?id=' + id;
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
  const searchTerm = this.value.toLowerCase();
  const rows = document.querySelectorAll('#membersTableBody tr');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
  const statusFilter = this.value.toLowerCase();
  const rows = document.querySelectorAll('#membersTableBody tr');
  
  rows.forEach(row => {
    const status = row.getAttribute('data-status');
    const statusMatch = !statusFilter || status === statusFilter;
    row.style.display = statusMatch ? '' : 'none';
  });
});

// Auto-hide success message
setTimeout(function() {
  const alert = document.querySelector('.app-alert');
  if (alert) {
    alert.style.transition = 'opacity 0.5s';
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 500);
  }
}, 5000);
</script>

</body>
</html>