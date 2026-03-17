<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'reports';
$today = date('Y-m-d');
$current_month = date('Y-m');

// ── MEMBERS ──────────────────────────────────────────────────────────────────
$total_members   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members"))['t'];
$active_members  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'"))['t'];
$expired_members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Expired'"))['t'];
$new_this_month  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE DATE_FORMAT(created_at,'%Y-%m')='$current_month'"))['t'];
$premium_count   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Premium%'"))['t'];
$basic_count     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Basic%'"))['t'];
$standard_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_type LIKE '%Standard%'"))['t'];

$recent_members = [];
$res = mysqli_query($conn, "SELECT full_name, membership_type, membership_status, created_at FROM members ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) $recent_members[] = $row;

// ── ATTENDANCE ───────────────────────────────────────────────────────────────
$today_present    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE attendance_date='$today' AND status='Present'"))['t'];
$month_present    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE DATE_FORMAT(attendance_date,'%Y-%m')='$current_month' AND status='Present'"))['t'];
$month_absent     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE DATE_FORMAT(attendance_date,'%Y-%m')='$current_month' AND status='Absent'"))['t'];
$total_attendance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance"))['t'];

$weekly_attendance = [];
for ($i = 6; $i >= 0; $i--) {
    $date  = date('Y-m-d', strtotime("-$i days"));
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE attendance_date='$date' AND status='Present'"))['t'];
    $weekly_attendance[] = ['day' => date('D, d M', strtotime($date)), 'count' => (int)$count];
}

// ── STAFF ────────────────────────────────────────────────────────────────────
$total_staff    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM staff"))['t'];
$active_staff   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM staff WHERE status='Active'"))['t'];
$inactive_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM staff WHERE status='Inactive'"))['t'];

$staff_roles = [];
$res2 = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM staff GROUP BY role ORDER BY count DESC");
while ($row = mysqli_fetch_assoc($res2)) $staff_roles[] = $row;

$all_staff = [];
$res3 = mysqli_query($conn, "SELECT full_name, role, status, created_at FROM staff ORDER BY status ASC, full_name ASC");
while ($row = mysqli_fetch_assoc($res3)) $all_staff[] = $row;

// ── PAYMENTS ─────────────────────────────────────────────────────────────────
$monthly_revenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'"))['t'];
$total_revenue    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE status='Paid'"))['t'];
$pending_dues     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE status IN ('Due','Overdue')"))['t'];
$total_paid_txns  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Paid'"))['t'];
$total_due_txns   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status IN ('Due','Overdue')"))['t'];

$revenue_months = [];
for ($i = 5; $i >= 0; $i--) {
    $m   = date('Y-m', strtotime("-$i months"));
    $lbl = date('M Y', strtotime("-$i months"));
    $amt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$m' AND status='Paid'"))['t'];
    $revenue_months[] = ['month' => $lbl, 'amount' => (int)$amt];
}

$recent_payments = [];
$res4 = mysqli_query($conn, "SELECT p.amount, p.status, p.payment_date, m.full_name FROM payments p LEFT JOIN members m ON p.member_id = m.id ORDER BY p.payment_date DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res4)) $recent_payments[] = $row;

// ── EQUIPMENT ────────────────────────────────────────────────────────────────
$total_eq       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment"))['t'];
$working_eq     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Working'"))['t'];
$maintenance_eq = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Maintenance'"))['t'];
$outoforder_eq  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment WHERE status='Out of Order'"))['t'];
$total_units    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(quantity),0) as t FROM equipment"))['t'];
$working_units  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(working_units),0) as t FROM equipment"))['t'];

$alert_equipment = [];
$res5 = mysqli_query($conn, "SELECT equipment_name, status, quantity, working_units FROM equipment WHERE status IN ('Maintenance','Out of Order') ORDER BY status ASC");
while ($row = mysqli_fetch_assoc($res5)) $alert_equipment[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - NextGen Fitness</title>
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

    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Reports</h1>
      <p class="page-subtitle">Complete gym overview — <?= date('d M Y') ?></p>
    </div>

    <!-- ── 1. MEMBERS REPORT ─────────────────────────────────────────────── -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3><i class="fas fa-users"></i> Members Report</h3>
        <a href="../modules/members.php" class="date-display">View All <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-users"></i></div>
            <div class="stat-info"><h3><?= $total_members ?></h3><p>Total Members</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info"><h3><?= $active_members ?></h3><p>Active</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-user-times"></i></div>
            <div class="stat-info"><h3><?= $expired_members ?></h3><p>Expired</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-user-plus"></i></div>
            <div class="stat-info"><h3><?= $new_this_month ?></h3><p>New This Month</p></div>
          </div>
        </div>

        <!-- Plan breakdown -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Membership Plan Breakdown</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Members</th>
                <th>Percentage</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $plans = [
                  ['Premium',  $premium_count,  'premium'],
                  ['Basic',    $basic_count,    'basic'],
                  ['Standard', $standard_count, 'standard'],
                ];
                foreach ($plans as [$name, $count, $cls]):
                  $pct = $total_members > 0 ? round(($count / $total_members) * 100) : 0;
              ?>
              <tr>
                <td><span class="plan-badge <?= $cls ?>"><?= $name ?></span></td>
                <td><?= $count ?></td>
                <td><?= $pct ?>%</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Recently joined -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Recently Joined</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Name</th><th>Plan</th><th>Status</th><th>Joined</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent_members as $m):
                $badge = $m['membership_status'] == 'Active' ? 'active' : 'expired';
              ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($m['full_name'], 0, 1)) ?></div>
                    <div class="member-info"><span class="name"><?= htmlspecialchars($m['full_name']) ?></span></div>
                  </div>
                </td>
                <td><?= htmlspecialchars($m['membership_type']) ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $m['membership_status'] ?></span></td>
                <td class="date-display"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recent_members)): ?>
              <tr><td colspan="4" class="text-center">No members found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── 2. ATTENDANCE REPORT ──────────────────────────────────────────── -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3><i class="fas fa-calendar-check"></i> Attendance Report</h3>
        <a href="../modules/view-attendance.php" class="date-display">View All <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-info"><h3><?= $today_present ?></h3><p>Present Today</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info"><h3><?= $month_present ?></h3><p>Present This Month</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-calendar-times"></i></div>
            <div class="stat-info"><h3><?= $month_absent ?></h3><p>Absent This Month</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-database"></i></div>
            <div class="stat-info"><h3><?= $total_attendance ?></h3><p>All Time Records</p></div>
          </div>
        </div>

        <!-- Last 7 days table -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Last 7 Days Attendance</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Day</th><th>Present Count</th></tr>
            </thead>
            <tbody>
              <?php foreach ($weekly_attendance as $w): ?>
              <tr>
                <td class="date-display"><?= $w['day'] ?></td>
                <td><?= $w['count'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── 3. STAFF REPORT ───────────────────────────────────────────────── -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3><i class="fas fa-user-tie"></i> Staff Report</h3>
        <a href="staff-list.php" class="date-display">View All <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-user-tie"></i></div>
            <div class="stat-info"><h3><?= $total_staff ?></h3><p>Total Staff</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info"><h3><?= $active_staff ?></h3><p>Active</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-user-times"></i></div>
            <div class="stat-info"><h3><?= $inactive_staff ?></h3><p>Inactive</p></div>
          </div>
        </div>

        <!-- Staff by role -->
        <?php if (!empty($staff_roles)): ?>
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Staff by Role</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Role</th><th>Count</th><th>Percentage</th></tr>
            </thead>
            <tbody>
              <?php foreach ($staff_roles as $sr):
                $pct = $total_staff > 0 ? round(($sr['count'] / $total_staff) * 100) : 0;
              ?>
              <tr>
                <td><?= htmlspecialchars($sr['role']) ?></td>
                <td><?= $sr['count'] ?></td>
                <td><?= $pct ?>%</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <!-- All staff -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>All Staff</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Name</th><th>Role</th><th>Status</th><th>Joined</th></tr>
            </thead>
            <tbody>
              <?php foreach ($all_staff as $s):
                $badge = $s['status'] == 'Active' ? 'active' : 'inactive';
              ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($s['full_name'], 0, 1)) ?></div>
                    <div class="member-info"><span class="name"><?= htmlspecialchars($s['full_name']) ?></span></div>
                  </div>
                </td>
                <td><?= htmlspecialchars($s['role']) ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $s['status'] ?></span></td>
                <td class="date-display"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($all_staff)): ?>
              <tr><td colspan="4" class="text-center">No staff found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── 4. PAYMENTS REPORT ────────────────────────────────────────────── -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3><i class="fas fa-credit-card"></i> Payments Report</h3>
        <a href="../modules/payments.php" class="date-display">View All <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-indian-rupee-sign"></i></div>
            <div class="stat-info"><h3>₹<?= number_format($monthly_revenue) ?></h3><p>This Month</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-wallet"></i></div>
            <div class="stat-info"><h3>₹<?= number_format($total_revenue) ?></h3><p>Total Collected</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-receipt"></i></div>
            <div class="stat-info"><h3>₹<?= number_format($pending_dues) ?></h3><p>Pending Dues</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
            <div class="stat-info"><h3><?= $total_paid_txns ?></h3><p>Paid Transactions</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info"><h3><?= $total_due_txns ?></h3><p>Due / Overdue</p></div>
          </div>
        </div>

        <!-- 6-month revenue table -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Revenue — Last 6 Months</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Month</th><th>Revenue Collected</th></tr>
            </thead>
            <tbody>
              <?php foreach ($revenue_months as $rm): ?>
              <tr>
                <td class="date-display"><?= $rm['month'] ?></td>
                <td>₹<?= number_format($rm['amount']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Recent payments -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Recent Transactions</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Member</th><th>Amount</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent_payments as $p):
                $badge = $p['status'] == 'Paid' ? 'active' : ($p['status'] == 'Overdue' ? 'expired' : 'inactive');
              ?>
              <tr>
                <td><?= htmlspecialchars($p['full_name'] ?? '—') ?></td>
                <td>₹<?= number_format($p['amount']) ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
                <td class="date-display"><?= $p['payment_date'] ? date('d M Y', strtotime($p['payment_date'])) : '—' ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recent_payments)): ?>
              <tr><td colspan="4" class="text-center">No payment records found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── 5. EQUIPMENT REPORT ───────────────────────────────────────────── -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3><i class="fas fa-dumbbell"></i> Equipment Report</h3>
        <a href="equipment-list.php" class="date-display">View All <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-dumbbell"></i></div>
            <div class="stat-info"><h3><?= $total_eq ?></h3><p>Total Types</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
            <div class="stat-info"><h3><?= $working_eq ?></h3><p>Working</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-screwdriver-wrench"></i></div>
            <div class="stat-info"><h3><?= $maintenance_eq ?></h3><p>Maintenance</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-circle-xmark"></i></div>
            <div class="stat-info"><h3><?= $outoforder_eq ?></h3><p>Out of Order</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-layer-group"></i></div>
            <div class="stat-info"><h3><?= $total_units ?></h3><p>Total Units</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-boxes-stacked"></i></div>
            <div class="stat-info"><h3><?= $working_units ?></h3><p>Working Units</p></div>
          </div>
        </div>

        <!-- Equipment status table -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Status Breakdown</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
            </thead>
            <tbody>
              <?php
                $eq_rows = [
                  ['Working',      $working_eq,     'active'],
                  ['Maintenance',  $maintenance_eq, 'inactive'],
                  ['Out of Order', $outoforder_eq,  'expired'],
                ];
                foreach ($eq_rows as [$label, $count, $badge]):
                  $pct = $total_eq > 0 ? round(($count / $total_eq) * 100) : 0;
              ?>
              <tr>
                <td><span class="status-badge <?= $badge ?>"><?= $label ?></span></td>
                <td><?= $count ?></td>
                <td><?= $pct ?>%</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Equipment needing attention -->
        <div class="members-table-container mb-20">
          <div class="table-header">
            <h3>Needs Attention</h3>
          </div>
          <table class="members-table">
            <thead>
              <tr><th>Equipment</th><th>Total Units</th><th>Working Units</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($alert_equipment as $e):
                $badge = $e['status'] == 'Out of Order' ? 'expired' : 'inactive';
              ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><i class="fas fa-dumbbell"></i></div>
                    <div class="member-info"><span class="name"><?= htmlspecialchars($e['equipment_name']) ?></span></div>
                  </div>
                </td>
                <td><?= $e['quantity'] ?></td>
                <td><?= $e['working_units'] ?? 0 ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $e['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($alert_equipment)): ?>
              <tr><td colspan="4" class="text-center">All equipment is working fine.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>