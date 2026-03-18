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
$working_qty  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(working_units),0) as t FROM equipment"))['t'];

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

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
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
      <div class="table-header" style="display:flex;justify-content:space-between;align-items:center;">
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
    const reportDate    = '<?= date("d M Y") ?>';
    const fileName      = 'Equipment_Report_<?= date("Y-m-d") ?>.xlsx';

    const wb = XLSX.utils.book_new();

    // ── SHEET 1: SUMMARY ─────────────────────────────────────────────────────
    const summaryRows = [
        ['NEXTGEN FITNESS — EQUIPMENT REPORT'],
        ['Generated on: ' + reportDate],
        [],
        ['OVERVIEW'],
        ['Metric', 'Count', 'Percentage'],
        ['Total Equipment Types', <?= $total ?>, '100%'],
        ['Working',              <?= $working ?>,      '<?= $working_pct ?>%'],
        ['Under Maintenance',    <?= $maintenance ?>,  '<?= $maintenance_pct ?>%'],
        ['Out of Order',         <?= $out_of_order ?>, '<?= $out_pct ?>%'],
        [],
        ['UNIT COUNTS'],
        ['Metric', 'Units'],
        ['Total Units',   <?= $total_qty ?? 0 ?>],
        ['Working Units', <?= $working_qty ?? 0 ?>],
    ];

    const ws1 = XLSX.utils.aoa_to_sheet(summaryRows);

    // Column widths for summary sheet
    ws1['!cols'] = [
        { wch: 28 },
        { wch: 14 },
        { wch: 14 },
    ];

    // Merge title cell across columns
    ws1['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 2 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 2 } },
        { s: { r: 3, c: 0 }, e: { r: 3, c: 2 } },
        { s: { r: 10, c: 0 }, e: { r: 10, c: 2 } },
    ];

    XLSX.utils.book_append_sheet(wb, ws1, 'Summary');

    // ── SHEET 2: FULL EQUIPMENT LIST ─────────────────────────────────────────
    const listHeader = [
        ['NEXTGEN FITNESS — FULL EQUIPMENT LIST'],
        ['Generated on: ' + reportDate],
        [],
        ['#', 'Equipment Name', 'Total Quantity', 'Working Units', 'Non-Working Units', 'Status', 'Added On'],
    ];

    const listRows = equipmentData.map((e, i) => {
        const total    = parseInt(e.quantity)      || 0;
        const working  = parseInt(e.working_units) || 0;
        const nonWorking = total - working;
        const added    = e.created_at ? e.created_at.split(' ')[0] : '';
        return [
            i + 1,
            e.equipment_name,
            total,
            working,
            nonWorking,
            e.status,
            added,
        ];
    });

    const ws2 = XLSX.utils.aoa_to_sheet([...listHeader, ...listRows]);

    // Column widths for list sheet
    ws2['!cols'] = [
        { wch: 5  },   // #
        { wch: 28 },   // Equipment Name
        { wch: 16 },   // Total Quantity
        { wch: 16 },   // Working Units
        { wch: 18 },   // Non-Working Units
        { wch: 16 },   // Status
        { wch: 14 },   // Added On
    ];

    // Merge title rows
    ws2['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } },
    ];

    XLSX.utils.book_append_sheet(wb, ws2, 'Equipment List');

    // ── SHEET 3: STATUS-WISE BREAKDOWN ───────────────────────────────────────
    const statusOrder = ['Working', 'Maintenance', 'Out of Order'];

    const statusRows = [
        ['NEXTGEN FITNESS — STATUS-WISE BREAKDOWN'],
        ['Generated on: ' + reportDate],
        [],
    ];

    statusOrder.forEach(status => {
        const filtered = equipmentData.filter(e => e.status === status);
        if (filtered.length === 0) return;

        statusRows.push([status.toUpperCase()]);
        statusRows.push(['#', 'Equipment Name', 'Total Quantity', 'Working Units', 'Added On']);

        filtered.forEach((e, i) => {
            statusRows.push([
                i + 1,
                e.equipment_name,
                parseInt(e.quantity)      || 0,
                parseInt(e.working_units) || 0,
                e.created_at ? e.created_at.split(' ')[0] : '',
            ]);
        });

        statusRows.push([]); // blank row between groups
    });

    const ws3 = XLSX.utils.aoa_to_sheet(statusRows);

    ws3['!cols'] = [
        { wch: 5  },
        { wch: 28 },
        { wch: 16 },
        { wch: 16 },
        { wch: 14 },
    ];

    ws3['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 4 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 4 } },
    ];

    XLSX.utils.book_append_sheet(wb, ws3, 'By Status');

    // ── DOWNLOAD ─────────────────────────────────────────────────────────────
    XLSX.writeFile(wb, fileName);
}
</script>
</body>
</html>