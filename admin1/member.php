<?php
session_start();

/* TEMP: simulate logged-in user */
$_SESSION['role'] = 'admin';
$page = 'members.php'; // For active sidebar highlight

// Sample member data
$members = [
    [
        'id' => 1,
        'name' => 'Rahul Sharma',
        'initial' => 'R',
        'joined' => '2024-01-15',
        'email' => 'rahul.sharma@email.com',
        'phone' => '+91 98765 43210',
        'dob' => '1995-03-15',
        'gender' => 'Male',
        'address' => '123 MG Road, Mumbai',
        'plan' => 'Premium',
        'status' => 'Active',
        'expiry' => '2025-01-15',
        'duration' => '12 Months'
    ],
    [
        'id' => 2,
        'name' => 'Priya Patel',
        'initial' => 'P',
        'joined' => '2024-03-20',
        'email' => 'priya.patel@email.com',
        'phone' => '+91 87654 32109',
        'dob' => '1998-07-22',
        'gender' => 'Female',
        'address' => '456 Linking Road, Bandra',
        'plan' => 'Basic',
        'status' => 'Active',
        'expiry' => '2025-03-20',
        'duration' => '6 Months'
    ],
    [
        'id' => 3,
        'name' => 'Amit Kumar',
        'initial' => 'A',
        'joined' => '2023-06-10',
        'email' => 'amit.kumar@email.com',
        'phone' => '+91 76543 21098',
        'dob' => '1992-11-08',
        'gender' => 'Male',
        'address' => '789 Andheri West, Mumbai',
        'plan' => 'Premium',
        'status' => 'Expired',
        'expiry' => '2024-06-10',
        'duration' => '3 Months'
    ],
    [
        'id' => 4,
        'name' => 'Sneha Reddy',
        'initial' => 'S',
        'joined' => '2024-05-05',
        'email' => 'sneha.reddy@email.com',
        'phone' => '+91 65432 10987',
        'dob' => '1996-02-14',
        'gender' => 'Female',
        'address' => '321 Powai, Mumbai',
        'plan' => 'Basic',
        'status' => 'Active',
        'expiry' => '2025-05-05',
        'duration' => '12 Months'
    ],
    [
        'id' => 5,
        'name' => 'Vikram Singh',
        'initial' => 'V',
        'joined' => '2024-02-28',
        'email' => 'vikram.singh@email.com',
        'phone' => '+91 54321 09876',
        'dob' => '1990-09-30',
        'gender' => 'Male',
        'address' => '654 Juhu, Mumbai',
        'plan' => 'Premium',
        'status' => 'Inactive',
        'expiry' => '2025-02-28',
        'duration' => '1 Month'
    ],
    [
        'id' => 6,
        'name' => 'Anita Desai',
        'initial' => 'A',
        'joined' => '2024-07-12',
        'email' => 'anita.desai@email.com',
        'phone' => '+91 43210 98765',
        'dob' => '1994-05-18',
        'gender' => 'Female',
        'address' => '987 Colaba, Mumbai',
        'plan' => 'Basic',
        'status' => 'Active',
        'expiry' => '2025-07-12',
        'duration' => '6 Months'
    ]
];

$total_members = count($members);
$active_members = count(array_filter($members, fn($m) => $m['status'] === 'Active'));
$inactive_members = count(array_filter($members, fn($m) => $m['status'] !== 'Active'));
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
    
    <!-- Page Header with Add Button -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Members</h1>
          <p class="page-subtitle">Manage gym members and their subscriptions</p>
        </div>
        <a href="/admin/member-entry.php" class="btn app-btn-primary">
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
          <?php foreach ($members as $member): ?>
          <tr data-member-id="<?= $member['id'] ?>" data-status="<?= strtolower($member['status']) ?>" data-plan="<?= strtolower($member['plan']) ?>">
            <!-- Member -->
            <td>
              <div class="member-cell">
                <div class="member-avatar"><?= $member['initial'] ?></div>
                <div class="member-info">
                  <span class="name"><?= $member['name'] ?></span>
                  <span class="joined">
                    <i class="fa-regular fa-calendar"></i>
                    Joined <?= date('Y-m-d', strtotime($member['joined'])) ?>
                  </span>
                </div>
              </div>
            </td>
            
            <!-- Contact -->
            <td>
              <div class="contact-cell">
                <div class="email">
                  <i class="fa-regular fa-envelope"></i>
                  <?= $member['email'] ?>
                </div>
                <div class="phone">
                  <i class="fa-solid fa-phone"></i>
                  <?= $member['phone'] ?>
                </div>
              </div>
            </td>
            
            <!-- Plan -->
            <td>
              <span class="plan-badge <?= strtolower($member['plan']) ?>">
                <?= $member['plan'] ?>
              </span>
            </td>
            
            <!-- Status -->
            <td>
              <span class="status-badge <?= strtolower($member['status']) ?>">
                <?= $member['status'] ?>
              </span>
            </td>
            
            <!-- Expiry -->
            <td>
              <div class="expiry-date">
                <?= date('Y-m-d', strtotime($member['expiry'])) ?>
              </div>
            </td>
            
            <!-- Actions -->
            <td>
              <div class="action-buttons">
                <button class="btn-action edit" onclick='openEditModal(<?= json_encode($member) ?>)' title="Edit Member">
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
                <button class="btn-action delete" onclick="deleteMember(<?= $member['id'] ?>, '<?= $member['name'] ?>')" title="Delete Member">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
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
      <div class="modal-body">
        <form id="editMemberForm">
          <input type="hidden" id="edit_member_id">
          
          <!-- Personal Information -->
          <div class="section mb-4">
            <h6 class="mb-3"><i class="fa-solid fa-user"></i> Personal Information</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label>Full Name *</label>
                <input type="text" class="form-control" id="edit_name" required>
              </div>
              <div class="col-md-6">
                <label>Email *</label>
                <input type="email" class="form-control" id="edit_email" required>
              </div>
              <div class="col-md-6">
                <label>Phone *</label>
                <input type="text" class="form-control" id="edit_phone" required>
              </div>
              <div class="col-md-6">
                <label>Date of Birth *</label>
                <input type="date" class="form-control" id="edit_dob" required>
              </div>
              <div class="col-md-6">
                <label>Gender *</label>
                <select class="form-control" id="edit_gender" required>
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-12">
                <label>Address *</label>
                <textarea class="form-control" id="edit_address" rows="2" required></textarea>
              </div>
            </div>
          </div>
          
          <!-- Membership Details -->
          <div class="section">
            <h6 class="mb-3"><i class="fa-solid fa-id-card"></i> Membership Details</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label>Membership Plan *</label>
                <select class="form-control" id="edit_plan" required>
                  <option value="">Select</option>
                  <option value="Basic">Basic</option>
                  <option value="Premium">Premium</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Status *</label>
                <select class="form-control" id="edit_status" required>
                  <option value="">Select</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Expired">Expired</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Duration *</label>
                <select class="form-control" id="edit_duration" required>
                  <option value="">Select</option>
                  <option value="1 Month">1 Month</option>
                  <option value="3 Months">3 Months</option>
                  <option value="6 Months">6 Months</option>
                  <option value="12 Months">12 Months</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Expiry Date *</label>
                <input type="date" class="form-control" id="edit_expiry" required>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark"></i> Cancel
        </button>
        <button type="button" class="btn app-btn-primary" onclick="saveChanges()">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </div>
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
      <div class="modal-body">
        <p class="mb-0">Are you sure you want to delete <strong id="deleteMemberName"></strong>?</p>
        <p class="text-muted small mb-0 mt-2">This action cannot be undone.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i class="fa-solid fa-trash-can"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Open Edit Modal with prefilled data
function openEditModal(member) {
  document.getElementById('edit_member_id').value = member.id;
  document.getElementById('edit_name').value = member.name;
  document.getElementById('edit_email').value = member.email;
  document.getElementById('edit_phone').value = member.phone;
  document.getElementById('edit_dob').value = member.dob;
  document.getElementById('edit_gender').value = member.gender;
  document.getElementById('edit_address').value = member.address;
  document.getElementById('edit_plan').value = member.plan;
  document.getElementById('edit_status').value = member.status;
  document.getElementById('edit_duration').value = member.duration;
  document.getElementById('edit_expiry').value = member.expiry;
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
  modal.show();
}

// Save Changes
function saveChanges() {
  const memberId = document.getElementById('edit_member_id').value;
  const formData = {
    id: memberId,
    name: document.getElementById('edit_name').value,
    email: document.getElementById('edit_email').value,
    phone: document.getElementById('edit_phone').value,
    dob: document.getElementById('edit_dob').value,
    gender: document.getElementById('edit_gender').value,
    address: document.getElementById('edit_address').value,
    plan: document.getElementById('edit_plan').value,
    status: document.getElementById('edit_status').value,
    duration: document.getElementById('edit_duration').value,
    expiry: document.getElementById('edit_expiry').value
  };
  
  // TODO: Send AJAX request to update member
  console.log('Updating member:', formData);
  
  // Close modal and show success message
  bootstrap.Modal.getInstance(document.getElementById('editMemberModal')).hide();
  
  // Show success alert (you can replace this with a toast notification)
  alert('✓ Member updated successfully!');
  
  // TODO: Refresh the table or update the row with new data
  // For now, we'll just reload the page
  // location.reload();
}

// Delete Member - Show confirmation modal
let memberToDelete = null;

function deleteMember(id, name) {
  memberToDelete = id;
  document.getElementById('deleteMemberName').textContent = name;
  
  const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  modal.show();
}

// Confirm Delete Button Click
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
  if (memberToDelete) {
    // TODO: Send AJAX request to delete member from database
    console.log('Deleting member ID:', memberToDelete);
    
    // Remove the row from table
    const row = document.querySelector(`tr[data-member-id="${memberToDelete}"]`);
    if (row) {
      row.style.transition = 'opacity 0.3s';
      row.style.opacity = '0';
      setTimeout(() => {
        row.remove();
        updateStats();
      }, 300);
    }
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    
    // Show success message
    setTimeout(() => {
      alert('✓ Member deleted successfully!');
    }, 400);
    
    memberToDelete = null;
  }
});

// Update stats after deletion
function updateStats() {
  const allRows = document.querySelectorAll('#membersTableBody tr');
  const activeRows = document.querySelectorAll('#membersTableBody tr[data-status="active"]');
  const inactiveRows = allRows.length - activeRows.length;
  
  // Update stat cards
  document.querySelector('.stat-icon.total + .stat-info h3').textContent = allRows.length;
  document.querySelector('.stat-icon.active + .stat-info h3').textContent = activeRows.length;
  document.querySelector('.stat-icon.inactive + .stat-info h3').textContent = inactiveRows;
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
</script>

</body>
</html>