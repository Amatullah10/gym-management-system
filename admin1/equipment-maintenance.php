<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-maintenance';
$page_title = 'Maintenance Schedule - Gym Management';
$success = '';
$error   = '';

// Mark as fixed — update status back to Working
if (isset($_GET['fix'])) {
    $fix_id = (int)$_GET['fix'];
    mysqli_query($conn, "UPDATE equipment SET status='Working' WHERE id='$fix_id'");
    header("Location: equipment-maintenance.php?success=fixed");
    exit();
}

// Mark as Out of Order
if (isset($_GET['outoforder'])) {
    $oo_id = (int)$_GET['outoforder'];
    mysqli_query($conn, "UPDATE equipment SET status='Out of Order' WHERE id='$oo_id'");
    header("Location: equipment-maintenance.php?success=updated");
    exit();
}

// Mark as Under Maintenance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_maintenance'])) {
    $equip_id = (int)$_POST['equip_id'];
    $update = mysqli_query($conn, "UPDATE equipment SET status='Maintenance' WHERE id='$equip_id'");
    if ($update) { $success = "Equipment marked as Under Maintenance!"; }
    else { $error = "Failed to update! " . mysqli_error($conn); }
}

// Fetch equipment needing attention
$maintenance_list = [];
$res = mysqli_query($conn, "SELECT * FROM equipment WHERE status IN ('Maintenance', 'Out of Order') ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($res)) { $maintenance_list[] = $row; }

// Fetch all working equipment for marking
$working_list = [];
$res2 = mysqli_query($conn, "SELECT * FROM equipment WHERE status = 'Working' ORDER BY equipment_name ASC");
while ($row = mysqli_fetch_assoc($res2)) { $working_list[] = $row; }

$total_maintenance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Maintenance'"))['t'];
$total_out         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Out of Order'"))['t'];
$total_working     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Working'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Maintenance Schedule</h1>
        <p class="page-subtitle">Track and manage equipment under maintenance or out of order</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <?= $_GET['success'] == 'fixed' ? 'Equipment marked as Working!' : 'Equipment status updated!' ?>
      </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info"><h3><?= $total_working ?></h3><p>Working</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="stat-info"><h3><?= $total_maintenance ?></h3><p>Under Maintenance</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-info"><h3><?= $total_out ?></h3><p>Out of Order</p></div>
      </div>
    </div>

    <!-- Mark Equipment for Maintenance -->
    <div class="form-container mb-20">
      <h3 style="font-size:17px; font-weight:600; margin-bottom:5px;">Send to Maintenance</h3>
      <p class="section-subtitle" style="margin-bottom:20px;">Select a working equipment to mark it under maintenance</p>
      <form method="POST">
        <input type="hidden" name="mark_maintenance" value="1">
        <div class="form-row">
          <div>
            <label>Select Equipment</label>
            <select name="equip_id" required>
              <option value="">-- Select Equipment --</option>
              <?php foreach ($working_list as $w): ?>
                <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['equipment_name']) ?> (Qty: <?= $w['quantity'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="margin-top:15px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-screwdriver-wrench"></i> Mark as Maintenance</button>
        </div>
      </form>
    </div>

    <!-- Equipment Needing Attention -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Equipment Needing Attention (<?= count($maintenance_list) ?>)</h3>
      </div>
      <div style="overflow-x:auto;">
        <table class="members-table" style="min-width:700px;">
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
            <?php if (!empty($maintenance_list)): ?>
              <?php foreach ($maintenance_list as $i => $e):
                $badge = $e['status'] == 'Maintenance' ? 'inactive' : 'expired';
                $icon  = $e['status'] == 'Maintenance' ? 'screwdriver-wrench' : 'circle-xmark';
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
                    <a href="equipment-maintenance.php?fix=<?= $e['id'] ?>" class="btn-action edit" title="Mark as Fixed" onclick="return confirm('Mark this equipment as Working?')">
                      <i class="fa-solid fa-check"></i>
                    </a>
                    <?php if ($e['status'] == 'Maintenance'): ?>
                    <a href="equipment-maintenance.php?outoforder=<?= $e['id'] ?>" class="btn-action delete" title="Mark as Out of Order" onclick="return confirm('Mark this equipment as Out of Order?')">
                      <i class="fa-solid fa-ban"></i>
                    </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center" style="padding:40px; color:#aaa;">
                  <i class="fa-solid fa-circle-check" style="font-size:35px; display:block; margin-bottom:10px; color:#c8e6c9;"></i>
                  All equipment is in working condition!
                </td>
              </tr>
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