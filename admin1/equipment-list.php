<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-list';
$page_title = 'Equipment List - Gym Management';

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM equipment WHERE id = '$del_id'");
    header("Location: equipment-list.php?success=deleted");
    exit();
}

$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if ($search)        { $where .= " AND equipment_name LIKE '%$search%'"; }
if ($status_filter) { $where .= " AND status = '$status_filter'"; }

$equipment = [];
$res = mysqli_query($conn, "SELECT * FROM equipment $where ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($res)) { $equipment[] = $row; }

$total        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment"))['t'];
$working      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Working'"))['t'];
$maintenance  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Maintenance'"))['t'];
$out_of_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Out of Order'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Equipment List</h1>
        <p class="page-subtitle">View and manage all gym equipment</p>
      </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <?= $_GET['success'] == 'deleted' ? 'Equipment deleted successfully!' : 'Equipment updated successfully!' ?>
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

    <!-- Search & Filter -->
    <div class="form-container" style="margin-bottom:25px;">
      <h3 style="font-size:16px; font-weight:600; margin-bottom:15px;">Search Equipment</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Equipment name...">
          </div>
          <div>
            <label>Status</label>
            <select name="status">
              <option value="">All Status</option>
              <option value="Working"      <?= $status_filter == 'Working'      ? 'selected' : '' ?>>Working</option>
              <option value="Maintenance"  <?= $status_filter == 'Maintenance'  ? 'selected' : '' ?>>Maintenance</option>
              <option value="Out of Order" <?= $status_filter == 'Out of Order' ? 'selected' : '' ?>>Out of Order</option>
            </select>
          </div>
        </div>
        <div style="display:flex; gap:15px; margin-top:15px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
          <a href="equipment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
          <a href="equipment-add.php" class="btn app-btn-primary" style="margin-left:auto;"><i class="fa-solid fa-plus"></i> Add Equipment</a>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Equipment (<?= count($equipment) ?> found)</h3>
      </div>
      <div style="overflow-x:auto;">
        <table class="members-table" style="min-width:800px;">
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
          <tbody>
            <?php if (!empty($equipment)): ?>
              <?php foreach ($equipment as $i => $e):
                if ($e['status'] == 'Working')         { $badge = 'active';   $icon = 'circle-check'; }
                elseif ($e['status'] == 'Maintenance') { $badge = 'inactive'; $icon = 'screwdriver-wrench'; }
                else                                   { $badge = 'expired';  $icon = 'circle-xmark'; }
              ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar" style="background:#f3f6f9; color:#555; font-size:18px;">
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
                    <a href="equipment-edit.php?id=<?= $e['id'] ?>" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                    <a href="equipment-list.php?delete=<?= $e['id'] ?>" class="btn-action delete" title="Delete" onclick="return confirm('Delete this equipment?')"><i class="fa-solid fa-trash"></i></a>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>