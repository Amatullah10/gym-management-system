<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-report';
$page_title = 'Equipment Reports - Gym Management';

$total        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment"))['t'];
$working      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Working'"))['t'];
$maintenance  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Maintenance'"))['t'];
$out_of_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Out of Order'"))['t'];
$total_qty    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as t FROM equipment"))['t'];
$working_qty  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(working_units) as t FROM equipment"))['t'];

$all_equipment = [];
$res = mysqli_query($conn, "SELECT * FROM equipment ORDER BY status ASC, equipment_name ASC");
while ($row = mysqli_fetch_assoc($res)) { $all_equipment[] = $row; }

$working_pct     = $total > 0 ? round(($working / $total) * 100) : 0;
$maintenance_pct = $total > 0 ? round(($maintenance / $total) * 100) : 0;
$out_pct         = $total > 0 ? round(($out_of_order / $total) * 100) : 0;
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header flex justify-between align-center">
      <div>
        <h1 class="page-title">Equipment Reports</h1>
        <p class="page-subtitle">Overview and summary of all gym equipment — <?= date('d M Y') ?></p>
      </div>
      <div>
        <button onclick="exportToExcel()" class="btn app-btn-primary">
          <i class="fa-solid fa-file-excel"></i> Export to Excel
        </button>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-dumbbell"></i></div>
        <div class="stat-info"><h3><?= $total ?></h3><p>Total Equipment Types</p></div>
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

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-layer-group"></i></div>
        <div class="stat-info"><h3><?= $total_qty ?? 0 ?></h3><p>Total Units</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
        <div class="stat-info"><h3><?= $working_qty ?? 0 ?></h3><p>Working Units</p></div>
      </div>
    </div>

    <div class="members-table-container mb-20">
      <div class="table-header">
        <h3>Status Breakdown</h3>
      </div>
      <div style="padding:25px;">
        <div style="margin-bottom:20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <span style="font-size:14px; font-weight:600; color:#2e7d32;"><i class="fa-solid fa-circle-check"></i> Working</span>
            <span style="font-size:13px; color:#666;"><?= $working ?> of <?= $total ?> (<?= $working_pct ?>%)</span>
          </div>
          <div style="background:#f0f0f0; border-radius:50px; height:10px; overflow:hidden;">
            <div style="width:<?= $working_pct ?>%; background:#2e7d32; height:100%; border-radius:50px;"></div>
          </div>
        </div>
        <div style="margin-bottom:20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <span style="font-size:14px; font-weight:600; color:#f57c00;"><i class="fa-solid fa-screwdriver-wrench"></i> Under Maintenance</span>
            <span style="font-size:13px; color:#666;"><?= $maintenance ?> of <?= $total ?> (<?= $maintenance_pct ?>%)</span>
          </div>
          <div style="background:#f0f0f0; border-radius:50px; height:10px; overflow:hidden;">
            <div style="width:<?= $maintenance_pct ?>%; background:#f57c00; height:100%; border-radius:50px;"></div>
          </div>
        </div>
        <div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <span style="font-size:14px; font-weight:600; color:#d32f2f;"><i class="fa-solid fa-circle-xmark"></i> Out of Order</span>
            <span style="font-size:13px; color:#666;"><?= $out_of_order ?> of <?= $total ?> (<?= $out_pct ?>%)</span>
          </div>
          <div style="background:#f0f0f0; border-radius:50px; height:10px; overflow:hidden;">
            <div style="width:<?= $out_pct ?>%; background:#d32f2f; height:100%; border-radius:50px;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="members-table-container">
      <div class="table-header flex justify-between align-center">
        <h3>Full Equipment Report</h3>
        <a href="equipment-list.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">Manage <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div style="overflow-x:auto;">
        <table class="members-table" style="min-width:700px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Equipment Name</th>
              <th>Total Quantity</th>
              <th>Working Units</th>
              <th>Status</th>
              <th>Added On</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($all_equipment)): ?>
              <?php foreach ($all_equipment as $i => $e):
                if ($e['status'] == 'Working')         { $badge = 'active';   $icon = 'circle-check'; }
                elseif ($e['status'] == 'Maintenance') { $badge = 'inactive'; $icon = 'screwdriver-wrench'; }
                else                                   { $badge = 'expired';  $icon = 'circle-xmark'; }
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
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
                <td><?= $e['working_units'] ?? 0 ?></td>
                <td><span class="status-badge <?= $badge ?>"><i class="fa-solid fa-<?= $icon ?>"></i> <?= $e['status'] ?></span></td>
                <td class="date-display"><?= date('d M Y', strtotime($e['created_at'])) ?></td>
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
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    const equipmentData = <?= json_encode($all_equipment) ?>;

    const rows = [
        ['Equipment Report - <?= date("d M Y") ?>'],
        [],
        ['Summary'],
        ['Total Equipment Types', <?= $total ?>],
        ['Working', <?= $working ?>],
        ['Under Maintenance', <?= $maintenance ?>],
        ['Out of Order', <?= $out_of_order ?>],
        ['Total Units', <?= $total_qty ?? 0 ?>],
        ['Working Units', <?= $working_qty ?? 0 ?>],
        [],
        ['Full Equipment List'],
        ['#', 'Equipment Name', 'Total Quantity', 'Working Units', 'Status', 'Added On']
    ];

    equipmentData.forEach((e, i) => {
        rows.push([i + 1, e.equipment_name, e.quantity, e.working_units ?? 0, e.status, e.created_at]);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Equipment Report');
    XLSX.writeFile(wb, 'Equipment_Report_<?= date("Y-m-d") ?>.xlsx');
}
</script>
</body>
</html>