<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-list';

// Handle Delete (POST method)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_equipment_id'])) {
    $del_id = (int)$_POST['delete_equipment_id'];
    if (mysqli_query($conn, "DELETE FROM equipment WHERE id = $del_id")) {
        header("Location: equipment-list.php?success=Equipment deleted successfully!");
    } else {
        header("Location: equipment-list.php?error=Failed to delete equipment.");
    }
    exit();
}

$equipment = [];
$res = mysqli_query($conn, "SELECT * FROM equipment ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($res)) { $equipment[] = $row; }

$total        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment"))['t'];
$working      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Working'"))['t'];
$maintenance  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Maintenance'"))['t'];
$out_of_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Out of Order'"))['t'];

$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message   = isset($_GET['error'])   ? $_GET['error']   : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Equipment List - Gym Management</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Equipment Management</h1>
        <p class="page-subtitle">View and manage all gym equipment</p>
      </div>
      <div>
        <a href="equipment-add.php" class="btn app-btn-primary">
          <i class="fa-solid fa-plus"></i> Add Equipment
        </a>
      </div>
    </div>

    <?php if ($success_message): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="app-alert app-alert-error">
        <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-dumbbell"></i></div>
        <div class="stat-info"><h3><?= $total ?></h3><p>Total Equipment</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info"><h3><?= $working ?></h3><p>Working</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="stat-info"><h3><?= $maintenance ?></h3><p>Under Maintenance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-info"><h3><?= $out_of_order ?></h3><p>Out of Order</p></div>
      </div>
    </div>

    <!-- Search & Filter (INLINE - MATCHES STAFF) -->
    <div class="d-flex gap-3 mb-4">
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search equipment by name...">
      </div>
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="working">Working</option>
        <option value="maintenance">Maintenance</option>
        <option value="out of order">Out of Order</option>
      </select>
    </div>

    <!-- Equipment Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Equipment</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Equipment Name</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Added On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="equipmentTableBody">
          <?php if (!empty($equipment)): ?>
            <?php foreach ($equipment as $i => $e):
              if ($e['status'] == 'Working')         { $badge = 'active'; $icon = 'circle-check'; }
              elseif ($e['status'] == 'Maintenance') { $badge = 'inactive'; $icon = 'screwdriver-wrench'; }
              else                                   { $badge = 'expired'; $icon = 'circle-xmark'; }
            ?>
            <tr data-status="<?= strtolower($e['status']) ?>">
              <td><?= $i+1 ?></td>
              <td>
                <div class="member-cell">
                  <div class="member-avatar" style="background:#f3f6f9; color:#555;">
                    <i class="fa-solid fa-dumbbell"></i>
                  </div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($e['equipment_name']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= $e['quantity'] ?></td>
              <td><span class="status-badge <?= $badge ?>"><i class="fa-solid fa-<?= $icon ?>"></i> <?= $e['status'] ?></span></td>
              <td class="date-display"><?= date('d M Y', strtotime($e['created_at'])) ?></td>
              <td>
                <div class="action-buttons">
                  <a href="equipment-edit.php?id=<?= $e['id'] ?>" class="btn-action edit" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <button class="btn-action delete"
                          onclick="deleteEquipment(<?= $e['id'] ?>, '<?= htmlspecialchars($e['equipment_name']) ?>')"
                          title="Delete">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center" style="padding:30px; color:#aaa;">No equipment found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteEquipmentModal" tabindex="-1">
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
          <input type="hidden" name="delete_equipment_id" id="delete_equipment_id">
          <p class="mb-1">Are you sure you want to delete <strong id="deleteEquipmentName"></strong>?</p>
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
// DELETE MODAL
function deleteEquipment(id, name) {
  document.getElementById('delete_equipment_id').value = id;
  document.getElementById('deleteEquipmentName').textContent = name;
  new bootstrap.Modal(document.getElementById('deleteEquipmentModal')).show();
}

// SEARCH + FILTER (JavaScript - like staff)
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('statusFilter').value.toLowerCase();

  document.querySelectorAll('#equipmentTableBody tr').forEach(row => {
    const matchSearch = !search || row.textContent.toLowerCase().includes(search);
    const matchStatus = !status || row.dataset.status === status;
    row.style.display = (matchSearch && matchStatus) ? '' : 'none';
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