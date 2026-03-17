<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$page = 'staff-list';

// Fetch all staff
$sql    = "SELECT * FROM staff ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$staff_list = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staff_list[] = $row;
    }
}

// Stats
$total_staff    = count($staff_list);
$active_staff   = 0;
$inactive_staff = 0;
foreach ($staff_list as $s) {
    if ($s['status'] === 'Active') $active_staff++;
    else $inactive_staff++;
}

$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message   = isset($_GET['error'])   ? $_GET['error']   : '';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_staff_id'])) {
    $del_id   = (int) $_POST['delete_staff_id'];
    $get_email = mysqli_query($conn, "SELECT email FROM staff WHERE id = $del_id");
    if ($get_email && mysqli_num_rows($get_email) > 0) {
        $er    = mysqli_fetch_assoc($get_email);
        $email = mysqli_real_escape_string($conn, $er['email']);
        mysqli_query($conn, "DELETE FROM users WHERE email = '$email'");
    }
    if (mysqli_query($conn, "DELETE FROM staff WHERE id = $del_id")) {
        header("Location: staff-list.php?success=Staff member removed successfully!");
    } else {
        header("Location: staff-list.php?error=Failed to delete staff member.");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Staff - Gym Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    /* Action buttons - always 3 icons visible side by side */
    .action-buttons {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: nowrap;
    }
    .btn-action {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      border: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 15px;
      transition: all 0.2s;
      flex-shrink: 0;
    }
    .btn-action.view   { background: #f3e5f5; color: #9c27b0; }
    .btn-action.edit   { background: #e8f4fd; color: #2196f3; }
    .btn-action.delete { background: #ffebee; color: #f44336; }
    .btn-action.view:hover   { background: #9c27b0; color: #fff; }
    .btn-action.edit:hover   { background: #2196f3; color: #fff; }
    .btn-action.delete:hover { background: #f44336; color: #fff; }

    /* ── Scrollable table wrapper ── */
    .members-table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    .members-table {
      min-width: 900px; /* forces scroll before columns squish */
    }

    /* ── Tighter column widths ── */
    .members-table th,
    .members-table td {
      padding: 14px 12px !important;
      font-size: 13px;
    }
    /* Actions column — always fully visible */
    .members-table th:last-child,
    .members-table td:last-child {
      min-width: 110px;
      white-space: nowrap;
    }
    /* Shrink columns that can afford it */
    .members-table th:nth-child(4), /* Experience */
    .members-table td:nth-child(4) { width: 100px; }
    .members-table th:nth-child(5), /* Salary */
    .members-table td:nth-child(5) { width: 90px; }
    .members-table th:nth-child(6), /* Status */
    .members-table td:nth-child(6) { width: 90px; }
    .members-table th:nth-child(7), /* Actions */
    .members-table td:nth-child(7) { width: 110px; }

    /* View modal — profile header */
    .staff-profile-block {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
    }
    .staff-avatar-lg {
      width: 56px; height: 56px;
      border-radius: 50%;
      background: #fee;
      color: var(--active-color);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 22px;
      flex-shrink: 0;
    }

    /* View modal — info grid */
    .view-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px 24px;
    }
    .view-info-item label {
      font-size: 12px;
      color: #999;
      font-weight: 500;
      margin-bottom: 2px;
      display: block;
    }
    .view-info-item span {
      font-size: 14px;
      color: #1a1a1a;
      font-weight: 500;
    }

    /* Circle close button for view modal */
    .btn-close-circle {
      width: 30px; height: 30px;
      border-radius: 50%;
      border: 1.5px solid #e0e0e0;
      background: #fff;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      color: #666;
      font-size: 13px;
      transition: all 0.2s;
    }
    .btn-close-circle:hover {
      border-color: var(--active-color);
      color: var(--active-color);
    }
  </style>
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Alerts -->
    <?php if ($success_message): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="app-alert app-alert-error">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">Manage Staff</h1>
          <p class="page-subtitle">View and manage all gym staff members</p>
        </div>
        <a href="staff-add.php" class="btn app-btn-primary">
          <i class="fa-solid fa-plus"></i> Add Staff
        </a>
      </div>
    </div>

    <!-- Stats Cards (3 only) -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info"><h3><?= $total_staff ?></h3><p>Total Staff</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon active"><i class="fa-solid fa-user-check"></i></div>
        <div class="stat-info"><h3><?= $active_staff ?></h3><p>Active</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon inactive"><i class="fa-solid fa-user-xmark"></i></div>
        <div class="stat-info"><h3><?= $inactive_staff ?></h3><p>Inactive</p></div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="d-flex gap-3 mb-4">
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search staff by name, email or phone...">
      </div>
      <select class="filter-select" id="roleFilter">
        <option value="">All Roles</option>
        <option value="trainer">Trainer</option>
        <option value="receptionist">Receptionist</option>
        <option value="accountant">Accountant</option>
        <option value="manager">Manager</option>
        <option value="maintenance">Maintenance</option>
        <option value="other">Other</option>
      </select>
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Staff Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Staff Members</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>Staff Member</th>
            <th>Contact</th>
            <th>Role</th>
            <th>Experience</th>
            <th>Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="staffTableBody">
          <?php if (count($staff_list) > 0): ?>
            <?php foreach ($staff_list as $s):
              $initial    = strtoupper(substr($s['full_name'], 0, 1));
              $role_class = match($s['role']) {
                'trainer'      => 'premium',
                'receptionist' => 'standard',
                'accountant'   => 'basic',
                default        => 'basic'
              };
            ?>
            <tr data-role="<?= strtolower($s['role']) ?>"
                data-status="<?= strtolower($s['status']) ?>">

              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($s['full_name']) ?></span>
                    <span class="joined">
                      <i class="fa-regular fa-calendar"></i>
                      Joined <?= date('d M Y', strtotime($s['join_date'])) ?>
                    </span>
                  </div>
                </div>
              </td>

              <td>
                <div class="contact-cell">
                  <div class="email"><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($s['email']) ?></div>
                  <div class="phone"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($s['phone']) ?></div>
                </div>
              </td>

              <td><span class="plan-badge <?= $role_class ?>"><?= ucfirst($s['role']) ?></span></td>

              <td style="color:#666;font-size:13px;"><?= $s['experience'] ? htmlspecialchars($s['experience']) : '—' ?></td>

              <td style="font-weight:600;color:#1a1a1a;"><?= $s['salary'] ? '₹' . number_format($s['salary'], 0) : '—' ?></td>

              <td>
                <span class="status-badge <?= strtolower($s['status']) ?>">
                  <?= htmlspecialchars($s['status']) ?>
                </span>
              </td>

              <!-- 3 action icon buttons -->
              <td>
                <div class="action-buttons">
                  <button class="btn-action view"
                          onclick='openViewModal(<?= json_encode($s) ?>)'
                          title="View Details">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                  <button class="btn-action edit"
                          onclick='openEditModal(<?= json_encode($s) ?>)'
                          title="Edit">
                    <i class="fa-regular fa-pen-to-square"></i>
                  </button>
                  <button class="btn-action delete"
                          onclick="deleteStaff(<?= $s['id'] ?>, '<?= htmlspecialchars($s['full_name']) ?>')"
                          title="Delete">
                    <i class="fa-regular fa-trash-can"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4">
                No staff found. <a href="staff-add.php">Add your first staff member</a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>


<!-- ===================================================
     VIEW MODAL — read-only staff details (Image 5 style)
===================================================  -->
<div class="modal fade" id="viewStaffModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:480px;">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold" style="font-size:18px;">Staff Details</h5>
        <button type="button" class="btn-close-circle" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body pt-3 pb-4">

        <!-- Avatar + Name + Role -->
        <div class="staff-profile-block">
          <div class="staff-avatar-lg" id="view_avatar"></div>
          <div>
            <h5 class="mb-1 fw-bold" id="view_name"></h5>
            <span class="plan-badge premium" id="view_role_badge"></span>
          </div>
        </div>

        <hr style="border-color:#f0f0f0;margin:12px 0 18px;">

        <!-- Info Grid -->
        <div class="view-info-grid">
          <div class="view-info-item">
            <label>Email:</label>
            <span id="view_email"></span>
          </div>
          <div class="view-info-item">
            <label>Phone:</label>
            <span id="view_phone"></span>
          </div>
          <div class="view-info-item">
            <label>Experience:</label>
            <span id="view_experience"></span>
          </div>
          <div class="view-info-item">
            <label>Salary:</label>
            <span id="view_salary"></span>
          </div>
          <div class="view-info-item">
            <label>Join Date:</label>
            <span id="view_join_date"></span>
          </div>
          <div class="view-info-item">
            <label>Status:</label>
            <span id="view_status_badge"></span>
          </div>
          <div class="view-info-item" style="grid-column:1/-1;">
            <label>Skills / Specialization:</label>
            <span id="view_skills"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ===================================================
     EDIT MODAL — editable fields (Image 3 style)
===================================================  -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:480px;">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" style="font-size:18px;">Edit Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="staff-edit.php">
        <div class="modal-body">
          <input type="hidden" name="staff_id" id="edit_staff_id">

          <!-- Full Name -->
          <div class="mb-3">
            <label>Name</label>
            <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
          </div>

          <!-- Email + Phone -->
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label>Email</label>
              <input type="email" class="form-control" name="email" id="edit_email" required>
            </div>
            <div class="col-6">
              <label>Phone</label>
              <input type="text" class="form-control" name="phone" id="edit_phone" required>
            </div>
          </div>

          <!-- Role + Status -->
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label>Role</label>
              <select class="form-control" name="role" id="edit_role" required>
                <option value="trainer">Trainer</option>
                <option value="receptionist">Receptionist</option>
                <option value="accountant">Accountant</option>
                <option value="manager">Manager</option>
                <option value="maintenance">Maintenance</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-6">
              <label>Status</label>
              <select class="form-control" name="status" id="edit_status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <!-- Experience + Salary -->
          <div class="row g-3">
            <div class="col-6">
              <label>Experience</label>
              <input type="text" class="form-control" name="experience" id="edit_experience" placeholder="e.g., 3 years">
            </div>
            <div class="col-6">
              <label>Salary (₹)</label>
              <input type="number" class="form-control" name="salary" id="edit_salary" placeholder="25000">
            </div>
          </div>

        </div>
        <div class="modal-footer border-0 pt-2">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn app-btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ===================================================
     DELETE MODAL — (Image 4 style)
===================================================  -->
<div class="modal fade" id="deleteStaffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title text-danger fw-bold">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body pt-1 pb-2">
          <input type="hidden" name="delete_staff_id" id="delete_staff_id">
          <p class="mb-1">Are you sure you want to delete <strong id="deleteStaffName"></strong>?</p>
          <p class="text-muted small mb-0">This action cannot be undone.</p>
        </div>
        <div class="modal-footer border-0 pt-1">
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// VIEW MODAL
function openViewModal(s) {
  document.getElementById('view_avatar').textContent     = s.full_name.charAt(0).toUpperCase();
  document.getElementById('view_name').textContent       = s.full_name;
  document.getElementById('view_role_badge').textContent = s.role.charAt(0).toUpperCase() + s.role.slice(1);
  document.getElementById('view_email').textContent      = s.email;
  document.getElementById('view_phone').textContent      = s.phone;
  document.getElementById('view_experience').textContent = s.experience || '—';
  document.getElementById('view_salary').textContent     = s.salary ? '₹' + parseInt(s.salary).toLocaleString() : '—';
  document.getElementById('view_join_date').textContent  = s.join_date;
  document.getElementById('view_skills').textContent     = s.skills || '—';

  const sb = document.getElementById('view_status_badge');
  sb.textContent = s.status;
  sb.className   = 'status-badge ' + s.status.toLowerCase();

  new bootstrap.Modal(document.getElementById('viewStaffModal')).show();
}

// EDIT MODAL
function openEditModal(s) {
  document.getElementById('edit_staff_id').value   = s.id;
  document.getElementById('edit_full_name').value  = s.full_name;
  document.getElementById('edit_email').value      = s.email;
  document.getElementById('edit_phone').value      = s.phone;
  document.getElementById('edit_role').value       = s.role;
  document.getElementById('edit_status').value     = s.status;
  document.getElementById('edit_experience').value = s.experience || '';
  document.getElementById('edit_salary').value     = s.salary    || '';
  new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}

// DELETE MODAL
function deleteStaff(id, name) {
  document.getElementById('delete_staff_id').value       = id;
  document.getElementById('deleteStaffName').textContent = name;
  new bootstrap.Modal(document.getElementById('deleteStaffModal')).show();
}

// SEARCH + FILTERS
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('roleFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const role   = document.getElementById('roleFilter').value.toLowerCase();
  const status = document.getElementById('statusFilter').value.toLowerCase();

  document.querySelectorAll('#staffTableBody tr').forEach(row => {
    const matchSearch = !search || row.textContent.toLowerCase().includes(search);
    const matchRole   = !role   || row.dataset.role   === role;
    const matchStatus = !status || row.dataset.status === status;
    row.style.display = (matchSearch && matchRole && matchStatus) ? '' : 'none';
  });
}

// AUTO-HIDE ALERTS
setTimeout(() => {
  const a = document.querySelector('.app-alert');
  if (a) { a.style.transition = 'opacity 0.5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500); }
}, 5000);
</script>
</body>
</html>