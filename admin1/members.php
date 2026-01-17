<?php
session_start();
require_once '../dbcon.php'; // Your existing database connection

/* TEMP: simulate logged-in user */
$_SESSION['role'] = 'admin';
$page = 'members.php'; // For active sidebar highlight

// Fetch all members from database
$sql = "SELECT * FROM members ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$members = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

// Calculate stats
$total_members = count($members);
$active_members = 0;
$inactive_members = 0;

foreach ($members as $member) {
    if ($member['membership_status'] === 'Active') {
        $active_members++;
    } else {
        $inactive_members++;
    }
}

// Success message after registration/update
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Members - Gym Management</title>
  
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

<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    
    <!-- Success Message -->
    <?php if ($success_message): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>
    
    <!-- Page Header with Add Button -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Members</h1>
          <p class="page-subtitle">Manage gym members and their subscriptions</p>
        </div>
        <a href="member-entry.php" class="btn app-btn-primary">
          <i class="fa-solid fa-plus"></i> Add Member
        </a>
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
          <p>Active</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon inactive">
          <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div class="stat-info">
          <h3><?= $inactive_members ?></h3>
          <p>Inactive</p>
        </div>
      </div>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="d-flex gap-3 mb-4">
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search members by name, email or phone...">
      </div>
      
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="expired">Expired</option>
      </select>
      
      <select class="filter-select" id="planFilter">
        <option value="">All Plans</option>
        <option value="premium">Premium</option>
        <option value="basic">Basic</option>
        <option value="standard">Standard</option>
      </select>
    </div>
    
    <!-- Members Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Members</h3>
      </div>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Contact</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Expiry</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="membersTableBody">
          <?php if (count($members) > 0): ?>
            <?php foreach ($members as $member): 
              $initial = strtoupper(substr($member['full_name'], 0, 1));
              $plan_type = explode(' - ', $member['membership_type'])[0]; // Extract "Basic", "Premium", etc.
            ?>
            <tr data-member-id="<?= $member['id'] ?>" 
                data-status="<?= strtolower($member['membership_status']) ?>" 
                data-plan="<?= strtolower($plan_type) ?>">
              
              <!-- Member -->
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($member['full_name']) ?></span>
                    <span class="joined">
                      <i class="fa-regular fa-calendar"></i>
                      Joined <?= date('Y-m-d', strtotime($member['created_at'])) ?>
                    </span>
                  </div>
                </div>
              </td>
              
              <!-- Contact -->
              <td>
                <div class="contact-cell">
                  <div class="email">
                    <i class="fa-regular fa-envelope"></i>
                    <?= htmlspecialchars($member['email']) ?>
                  </div>
                  <div class="phone">
                    <i class="fa-solid fa-phone"></i>
                    <?= htmlspecialchars($member['phone']) ?>
                  </div>
                </div>
              </td>
              
              <!-- Plan -->
              <td>
                <span class="plan-badge <?= strtolower($plan_type) ?>">
                  <?= htmlspecialchars($plan_type) ?>
                </span>
              </td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= strtolower($member['membership_status']) ?>">
                  <?= htmlspecialchars($member['membership_status']) ?>
                </span>
              </td>
              
              <!-- Expiry -->
              <td>
                <div class="expiry-date">
                  <?= date('Y-m-d', strtotime($member['end_date'])) ?>
                </div>
              </td>
              
              <!-- Actions -->
              <td>
                <div class="action-buttons">
                  <button class="btn-action edit" onclick='openEditModal(<?= json_encode($member) ?>)' title="Edit Member">
                    <i class="fa-regular fa-pen-to-square"></i>
                  </button>
                  <button class="btn-action delete" onclick="deleteMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['full_name']) ?>')" title="Delete Member">
                    <i class="fa-regular fa-trash-can"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No members found. <a href="member-entry.php">Add your first member</a></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-pen"></i> Edit Member Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="update-member.php">
        <div class="modal-body">
          <input type="hidden" name="member_id" id="edit_member_id">
          
          <!-- Personal Information -->
          <div class="section mb-4">
            <h6 class="mb-3"><i class="fa-solid fa-user"></i> Personal Information</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label>Full Name *</label>
                <input type="text" class="form-control" name="full_name" id="edit_name" required>
              </div>
              <div class="col-md-6">
                <label>Email *</label>
                <input type="email" class="form-control" name="email" id="edit_email" required>
              </div>
              <div class="col-md-6">
                <label>Phone *</label>
                <input type="text" class="form-control" name="phone" id="edit_phone" required>
              </div>
              <div class="col-md-6">
                <label>Date of Birth *</label>
                <input type="date" class="form-control" name="dob" id="edit_dob" required>
              </div>
              <div class="col-md-6">
                <label>Gender *</label>
                <select class="form-control" name="gender" id="edit_gender" required>
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-12">
                <label>Address *</label>
                <textarea class="form-control" name="address" id="edit_address" rows="2" required></textarea>
              </div>
            </div>
          </div>
          
          <!-- Membership Details -->
          <div class="section">
            <h6 class="mb-3"><i class="fa-solid fa-id-card"></i> Membership Details</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label>Membership Type *</label>
                <select class="form-control" name="membership_type" id="edit_plan" required>
                  <option value="">Select</option>
                  <option value="Basic - 799/month">Basic - 799/month</option>
                  <option value="Standard - 999/month">Standard - 999/month</option>
                  <option value="Premium - 1299/month">Premium - 1299/month</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Status *</label>
                <select class="form-control" name="membership_status" id="edit_status" required>
                  <option value="">Select</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Expired">Expired</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Duration *</label>
                <select class="form-control" name="duration" id="edit_duration" required>
                  <option value="">Select</option>
                  <option value="1 Month">1 Month</option>
                  <option value="3 Months">3 Months</option>
                  <option value="6 Months">6 Months</option>
                  <option value="12 Months">12 Months</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>End Date *</label>
                <input type="date" class="form-control" name="end_date" id="edit_expiry" required>
              </div>
              <div class="col-md-12">
                <label>Fitness Level *</label>
                <select class="form-control" name="fitness_level" id="edit_fitness" required>
                  <option value="">Select</option>
                  <option value="Beginner">Beginner</option>
                  <option value="Medium">Medium</option>
                  <option value="Advanced">Advanced</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark"></i> Cancel
          </button>
          <button type="submit" class="btn app-btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title text-danger">
          <i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="delete-member.php">
        <div class="modal-body">
          <input type="hidden" name="member_id" id="delete_member_id">
          <p class="mb-0">Are you sure you want to delete <strong id="deleteMemberName"></strong>?</p>
          <p class="text-muted small mb-0 mt-2">This action cannot be undone.</p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash-can"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Open Edit Modal with prefilled data
function openEditModal(member) {
  document.getElementById('edit_member_id').value = member.id;
  document.getElementById('edit_name').value = member.full_name;
  document.getElementById('edit_email').value = member.email;
  document.getElementById('edit_phone').value = member.phone;
  document.getElementById('edit_dob').value = member.dob;
  document.getElementById('edit_gender').value = member.gender;
  document.getElementById('edit_address').value = member.address;
  document.getElementById('edit_plan').value = member.membership_type;
  document.getElementById('edit_status').value = member.membership_status;
  document.getElementById('edit_duration').value = member.duration;
  document.getElementById('edit_expiry').value = member.end_date;
  document.getElementById('edit_fitness').value = member.fitness_level;
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
  modal.show();
}

// Delete Member - Show confirmation modal
function deleteMember(id, name) {
  document.getElementById('delete_member_id').value = id;
  document.getElementById('deleteMemberName').textContent = name;
  
  const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  modal.show();
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
  filterTable();
});

// Plan filter
document.getElementById('planFilter').addEventListener('change', function() {
  filterTable();
});

// Combined filter function
function filterTable() {
  const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
  const planFilter = document.getElementById('planFilter').value.toLowerCase();
  const rows = document.querySelectorAll('#membersTableBody tr');
  
  rows.forEach(row => {
    const status = row.getAttribute('data-status');
    const plan = row.getAttribute('data-plan');
    
    const statusMatch = !statusFilter || status === statusFilter;
    const planMatch = !planFilter || plan === planFilter;
    
    row.style.display = (statusMatch && planMatch) ? '' : 'none';
  });
}

// Auto-hide success message after 5 seconds
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