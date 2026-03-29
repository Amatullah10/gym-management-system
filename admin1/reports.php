<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page       = 'reports';
$page_title = 'Reports - Gym Management';
$today      = date('Y-m-d');
$this_month = date('Y-m');

// ── Helper: safe query scalar ──────────────────────────────────────────────
function qval($conn, $sql) {
    $r = mysqli_query($conn, $sql);
    if (!$r) return 0;
    $row = mysqli_fetch_assoc($r);
    return $row ? array_values($row)[0] : 0;
}

// ── HANDLE EXCEL/PDF EXPORT ────────────────────────────────────────────────
$export_type   = $_GET['export']   ?? '';   // 'excel' or 'pdf'
$report_type   = $_GET['report']   ?? '';
$date_from     = $_GET['date_from'] ?? date('Y-m-01');
$date_to       = $_GET['date_to']   ?? $today;
$custom_search = trim($_GET['search'] ?? '');
$custom_table  = $_GET['table']     ?? 'members';
$custom_cols   = $_GET['cols']      ?? '';

// ── PRE-LOAD ALL REPORT DATA ───────────────────────────────────────────────

// 1. Membership
$mb_total    = qval($conn,"SELECT COUNT(*) FROM members");
$mb_active   = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_status='Active'");
$mb_expired  = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_status='Expired'");
$mb_inactive = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_status='Inactive'");
$mb_month    = qval($conn,"SELECT COUNT(*) FROM members WHERE DATE_FORMAT(created_at,'%Y-%m')='$this_month'");
$mb_premium  = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_type LIKE '%Premium%'");
$mb_standard = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_type LIKE '%Standard%'");
$mb_basic    = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_type LIKE '%Basic%'");

// 2. Revenue
$rev_total   = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid'");
$rev_month   = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND DATE_FORMAT(payment_date,'%Y-%m')='$this_month'");
$rev_due     = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN('Due','Overdue')");
$rev_txns    = qval($conn,"SELECT COUNT(*) FROM payments WHERE status='Paid'");
$rev_cash    = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND payment_method='Cash'");
$rev_upi     = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND payment_method='UPI'");
$rev_card    = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND payment_method='Card'");
$rev_online  = qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND payment_method='Online'");
$rev_6months = [];
for ($i=5;$i>=0;$i--) {
    $m  = date('Y-m', strtotime("-$i months"));
    $lbl= date('M Y', strtotime("-$i months"));
    $rev_6months[] = ['month'=>$lbl,'amount'=>(float)qval($conn,"SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND DATE_FORMAT(payment_date,'%Y-%m')='$m'")];
}

// 3. Attendance
$att_today   = qval($conn,"SELECT COUNT(*) FROM attendance WHERE attendance_date='$today' AND status='Present'");
$att_month   = qval($conn,"SELECT COUNT(*) FROM attendance WHERE DATE_FORMAT(attendance_date,'%Y-%m')='$this_month' AND status='Present'");
$att_absent  = qval($conn,"SELECT COUNT(*) FROM attendance WHERE DATE_FORMAT(attendance_date,'%Y-%m')='$this_month' AND status='Absent'");
$att_week    = [];
for ($i=6;$i>=0;$i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $att_week[] = ['day'=>date('D d', strtotime($d)),'count'=>(int)qval($conn,"SELECT COUNT(*) FROM attendance WHERE attendance_date='$d' AND status='Present'")];
}

// 4. Staff
$staff_total    = qval($conn,"SELECT COUNT(*) FROM staff");
$staff_active   = qval($conn,"SELECT COUNT(*) FROM staff WHERE status='Active'");
$staff_inactive = qval($conn,"SELECT COUNT(*) FROM staff WHERE status='Inactive'");
$staff_roles    = [];
$r=mysqli_query($conn,"SELECT role, COUNT(*) as c FROM staff GROUP BY role ORDER BY c DESC");
while($row=mysqli_fetch_assoc($r)) $staff_roles[]=$row;

// 5. Equipment
$eq_total  = qval($conn,"SELECT COUNT(*) FROM equipment");
$eq_work   = qval($conn,"SELECT COUNT(*) FROM equipment WHERE status='Working'");
$eq_maint  = qval($conn,"SELECT COUNT(*) FROM equipment WHERE status='Maintenance'");
$eq_out    = qval($conn,"SELECT COUNT(*) FROM equipment WHERE status='Out of Order'");
$eq_units  = qval($conn,"SELECT COALESCE(SUM(quantity),0) FROM equipment");

// 6. Trainer assignments
$ta_active = qval($conn,"SELECT COUNT(*) FROM trainer_assignments WHERE status='Active'");
$ta_total  = qval($conn,"SELECT COUNT(*) FROM trainer_assignments");

// 7. Member expiry (due this month)
$expiry_soon = qval($conn,"SELECT COUNT(*) FROM members WHERE membership_status='Expired'");
$exp_list = [];
$r2=mysqli_query($conn,"SELECT full_name, membership_type, membership_status, phone FROM members WHERE membership_status IN('Expired','Inactive') ORDER BY full_name ASC LIMIT 10");
while($row=mysqli_fetch_assoc($r2)) $exp_list[]=$row;

// 8. Top paying members
$top_members=[];
$r3=mysqli_query($conn,"SELECT m.full_name, m.membership_type, COALESCE(SUM(p.amount),0) as total_paid, COUNT(p.id) as txns FROM members m LEFT JOIN payments p ON p.member_id=m.id AND p.status='Paid' GROUP BY m.id ORDER BY total_paid DESC LIMIT 8");
while($row=mysqli_fetch_assoc($r3)) $top_members[]=$row;

// 9. Daily attendance (last 30 days)
$daily_att=[];
$r4=mysqli_query($conn,"SELECT attendance_date, COUNT(*) as present FROM attendance WHERE status='Present' AND attendance_date >= DATE_SUB('$today', INTERVAL 30 DAY) GROUP BY attendance_date ORDER BY attendance_date ASC");
while($row=mysqli_fetch_assoc($r4)) $daily_att[]=$row;

// 10. Payment method breakdown
$pay_methods=[];
$r5=mysqli_query($conn,"SELECT payment_method, COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM payments WHERE status='Paid' GROUP BY payment_method");
while($row=mysqli_fetch_assoc($r5)) $pay_methods[]=$row;

// ── EXPORT HANDLER ─────────────────────────────────────────────────────────
if ($export_type && $report_type) {

    $reports_map = [
        'membership'   => ["SELECT m.id, m.full_name, m.email, m.phone, m.gender, m.membership_type, m.membership_status, DATE(m.created_at) as joined FROM members m ORDER BY m.created_at DESC", 'Membership Report'],
        'revenue'      => ["SELECT m.full_name, p.amount, p.plan, p.payment_method, p.status, p.transaction_id, DATE(p.payment_date) as payment_date FROM payments p JOIN members m ON m.id=p.member_id ORDER BY p.payment_date DESC", 'Revenue Report'],
        'attendance'   => ["SELECT m.full_name, a.attendance_date, a.status, a.check_in_time, a.check_out_time FROM attendance a JOIN members m ON m.id=a.member_id ORDER BY a.attendance_date DESC LIMIT 500", 'Attendance Report'],
        'staff'        => ["SELECT full_name, role, email, phone, status, join_date, salary FROM staff ORDER BY role, full_name", 'Staff Report'],
        'equipment'    => ["SELECT equipment_name, quantity, status, purchase_date, purchase_amount, description FROM equipment ORDER BY status, equipment_name", 'Equipment Report'],
        'expiry'       => ["SELECT full_name, email, phone, membership_type, membership_status FROM members WHERE membership_status IN('Expired','Inactive') ORDER BY full_name", 'Expired Members Report'],
        'top_members'  => ["SELECT m.full_name, m.membership_type, COALESCE(SUM(p.amount),0) as total_paid, COUNT(p.id) as transactions FROM members m LEFT JOIN payments p ON p.member_id=m.id AND p.status='Paid' GROUP BY m.id ORDER BY total_paid DESC LIMIT 50", 'Top Paying Members'],
        'trainer'      => ["SELECT m.full_name as member, s.full_name as trainer, ta.assigned_date, ta.status FROM trainer_assignments ta JOIN members m ON m.id=ta.member_id JOIN staff s ON s.id=ta.trainer_id ORDER BY ta.status, ta.assigned_date DESC", 'Trainer Assignments'],
        'payment_due'  => ["SELECT m.full_name, m.email, m.phone, p.amount, p.status, DATE(p.payment_date) as due_date FROM payments p JOIN members m ON m.id=p.member_id WHERE p.status IN('Due','Overdue') ORDER BY p.payment_date ASC", 'Due & Overdue Payments'],
        'daily_att'    => ["SELECT attendance_date, COUNT(*) as present_count FROM attendance WHERE status='Present' GROUP BY attendance_date ORDER BY attendance_date DESC LIMIT 60", 'Daily Attendance Summary'],
        'custom'       => ["", 'Custom Report'],
    ];

    // Build custom query
    if ($report_type === 'custom') {
        $allowed_tables = ['members','payments','attendance','staff','equipment','trainer_assignments'];
        $ct = in_array($custom_table, $allowed_tables) ? $custom_table : 'members';
        $where_custom = $custom_search ? " WHERE CONCAT_WS(' ', " . implode(', ', array_fill(0, 5, '""')) . ") LIKE '%$custom_search%'" : "";
        $reports_map['custom'][0] = "SELECT * FROM `$ct` ORDER BY id DESC LIMIT 500";
        $reports_map['custom'][1] = 'Custom: ' . ucfirst($ct);
    }

    if (isset($reports_map[$report_type])) {
        [$sql, $title] = $reports_map[$report_type];
        $res_exp = mysqli_query($conn, $sql);
        $rows_exp = [];
        $cols_exp = [];
        if ($res_exp && mysqli_num_rows($res_exp) > 0) {
            while ($row = mysqli_fetch_assoc($res_exp)) {
                if (empty($cols_exp)) $cols_exp = array_keys($row);
                $rows_exp[] = $row;
            }
        }

        if ($export_type === 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . str_replace(' ','_',$title) . '_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<h2>' . htmlspecialchars($title) . '</h2>';
            echo '<p>Generated: ' . date('d M Y, h:i A') . ' | Total Records: ' . count($rows_exp) . '</p>';
            echo '<table border="1" cellpadding="5" cellspacing="0">';
            echo '<thead style="background:#941614;color:white;"><tr>';
            foreach ($cols_exp as $col) echo '<th>' . htmlspecialchars(str_replace('_',' ',ucfirst($col))) . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($rows_exp as $row) {
                echo '<tr>';
                foreach ($row as $val) echo '<td>' . htmlspecialchars($val ?? '') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
            exit();
        }

        if ($export_type === 'pdf') {
            header('Content-Type: text/html; charset=UTF-8');
            $gym_name = qval($conn,"SELECT setting_value FROM gym_settings WHERE setting_key='gym_name'") ?: 'NextGen Fitness GYM';
            $logo_full = __DIR__ . '/../assets/logo.png';
            $logo_b64  = file_exists($logo_full) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo_full)) : '';
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
            <style>
              body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
              .header { display:flex; align-items:center; gap:16px; border-bottom:3px solid #941614; padding-bottom:12px; margin-bottom:16px; }
              .header img { width:60px; height:60px; border-radius:50%; }
              .header h1 { margin:0; font-size:20px; color:#941614; }
              .header p  { margin:2px 0; font-size:11px; color:#666; }
              h2 { color:#941614; font-size:15px; margin:0 0 4px; }
              .meta { color:#888; font-size:11px; margin-bottom:14px; }
              table { width:100%; border-collapse:collapse; margin-top:8px; }
              th { background:#941614; color:white; padding:7px 8px; text-align:left; font-size:11px; }
              td { padding:6px 8px; border-bottom:1px solid #eee; font-size:11px; }
              tr:nth-child(even) td { background:#fafafa; }
              .footer { margin-top:20px; text-align:center; font-size:10px; color:#aaa; border-top:1px solid #eee; padding-top:8px; }
              @media print { @page { margin:15mm; } }
            </style>
            <script>window.onload = function(){ window.print(); }</script>
            </head><body>';
            echo '<div class="header">';
            if ($logo_b64) echo '<img src="'.$logo_b64.'">';
            echo '<div><h1>'.htmlspecialchars($gym_name).'</h1><p>'.htmlspecialchars($title).'</p><p>Generated: '.date('d M Y, h:i A').'</p></div></div>';
            echo '<table><thead><tr>';
            foreach ($cols_exp as $col) echo '<th>' . htmlspecialchars(str_replace('_',' ',ucfirst($col))) . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($rows_exp as $row) {
                echo '<tr>';
                foreach ($row as $val) echo '<td>' . htmlspecialchars($val ?? '') . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '<div class="footer">'.htmlspecialchars($gym_name).' &bull; Total Records: '.count($rows_exp).' &bull; '.date('d M Y').'</div>';
            echo '</body></html>';
            exit();
        }
    }
}

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    :root { --red:#941614; }

    /* Report Cards Grid */
    .reports-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }
    .report-card {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      border: 1px solid #f0f0f0;
      transition: box-shadow 0.2s, transform 0.2s;
      cursor: pointer;
    }
    .report-card:hover { box-shadow: 0 6px 24px rgba(148,22,20,0.10); transform: translateY(-2px); }
    .report-card-icon {
      width: 54px; height: 54px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; margin-bottom: 14px;
    }
    .report-card h4 { margin: 0 0 5px; font-size: 15px; font-weight: 700; color: #1a1a1a; }
    .report-card p  { margin: 0; font-size: 12px; color: #888; line-height: 1.5; }
    .report-card-actions { display: flex; gap: 8px; margin-top: 16px; }
    .btn-excel {
      flex: 1; padding: 8px 0; border-radius: 8px; border: none; cursor: pointer;
      font-size: 12px; font-weight: 600; background: #e8f5e9; color: #2e7d32;
      display: flex; align-items: center; justify-content: center; gap: 6px;
      transition: background 0.2s;
    }
    .btn-excel:hover { background: #2e7d32; color: #fff; }
    .btn-pdf {
      flex: 1; padding: 8px 0; border-radius: 8px; border: none; cursor: pointer;
      font-size: 12px; font-weight: 600; background: #fde8e8; color: var(--red);
      display: flex; align-items: center; justify-content: center; gap: 6px;
      transition: background 0.2s;
    }
    .btn-pdf:hover { background: var(--red); color: #fff; }

    /* Icon colour themes */
    .ic-red    { background: #fde8e8; color: var(--red); }
    .ic-green  { background: #e8f5e9; color: #2e7d32; }
    .ic-blue   { background: #e3f2fd; color: #1565c0; }
    .ic-orange { background: #fff3e0; color: #e65100; }
    .ic-purple { background: #f3e5f5; color: #6a1b9a; }
    .ic-teal   { background: #e0f2f1; color: #00695c; }
    .ic-yellow { background: #fffde7; color: #f57f17; }
    .ic-pink   { background: #fce4ec; color: #ad1457; }
    .ic-indigo { background: #e8eaf6; color: #283593; }
    .ic-cyan   { background: #e0f7fa; color: #00838f; }

    /* Summary stats strip */
    .summary-strip {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 14px;
      margin-bottom: 28px;
    }
    .summary-chip {
      background: #fff; border-radius: 12px; padding: 16px 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid var(--red);
    }
    .summary-chip .val { font-size: 22px; font-weight: 700; color: #1a1a1a; }
    .summary-chip .lbl { font-size: 11px; color: #888; margin-top: 2px; }

    /* Custom search panel */
    .custom-panel {
      background: #fff; border-radius: 16px; padding: 24px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 28px;
      border: 2px dashed #fde8e8;
    }
    .custom-panel h3 { margin: 0 0 4px; font-size: 16px; font-weight: 700; color: var(--red); }
    .custom-panel p  { margin: 0 0 18px; font-size: 13px; color: #888; }

    /* Section heading */
    .section-heading {
      font-size: 18px; font-weight: 700; color: #1a1a1a;
      margin: 28px 0 16px; display: flex; align-items: center; gap: 10px;
    }
    .section-heading span {
      width: 4px; height: 22px; background: var(--red); border-radius: 4px; display: inline-block;
    }
  </style>
</head>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:24px;">
      <div>
        <h1 class="page-title"><i class="fas fa-chart-bar" style="color:var(--red);margin-right:8px;"></i>Reports</h1>
        <p class="page-subtitle">View gym analytics and download reports</p>
      </div>
    </div>

    <!-- Summary Strip -->
    <div class="summary-strip">
      <div class="summary-chip">
        <div class="val"><?= $mb_total ?></div>
        <div class="lbl">Total Members</div>
      </div>
      <div class="summary-chip">
        <div class="val">₹<?= number_format($rev_total,0) ?></div>
        <div class="lbl">Total Revenue</div>
      </div>
      <div class="summary-chip">
        <div class="val"><?= $att_today ?></div>
        <div class="lbl">Present Today</div>
      </div>
      <div class="summary-chip">
        <div class="val"><?= $staff_active ?></div>
        <div class="lbl">Active Staff</div>
      </div>
      <div class="summary-chip">
        <div class="val"><?= $eq_total ?></div>
        <div class="lbl">Equipment</div>
      </div>
      <div class="summary-chip">
        <div class="val">₹<?= number_format($rev_due,0) ?></div>
        <div class="lbl">Pending Dues</div>
      </div>
    </div>

    <!-- ─── SECTION: Pre-built Reports ──────────────────────────────────── -->
    <div class="section-heading"><span></span> Pre-Built Reports</div>

    <div class="reports-grid">

      <!-- 1. Membership Report -->
      <div class="report-card">
        <div class="report-card-icon ic-red"><i class="fas fa-users"></i></div>
        <h4>Membership Report</h4>
        <p>Active, expired & new member stats. Total: <strong><?= $mb_total ?></strong> | Active: <strong><?= $mb_active ?></strong> | Expired: <strong><?= $mb_expired ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('membership','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('membership','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 2. Revenue Report -->
      <div class="report-card">
        <div class="report-card-icon ic-green"><i class="fas fa-indian-rupee-sign"></i></div>
        <h4>Revenue Report</h4>
        <p>All payments with method & status. This month: <strong>₹<?= number_format($rev_month,0) ?></strong> | Total: <strong>₹<?= number_format($rev_total,0) ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('revenue','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('revenue','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 3. Attendance Report -->
      <div class="report-card">
        <div class="report-card-icon ic-blue"><i class="fas fa-calendar-check"></i></div>
        <h4>Attendance Report</h4>
        <p>Daily check-in records. Today: <strong><?= $att_today ?></strong> present | This month: <strong><?= $att_month ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('attendance','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('attendance','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 4. Equipment Report -->
      <div class="report-card">
        <div class="report-card-icon ic-yellow"><i class="fas fa-dumbbell"></i></div>
        <h4>Equipment Report</h4>
        <p>Equipment status & maintenance logs. Working: <strong><?= $eq_work ?></strong> | Maintenance: <strong><?= $eq_maint ?></strong> | Out: <strong><?= $eq_out ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('equipment','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('equipment','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 5. Staff Report -->
      <div class="report-card">
        <div class="report-card-icon ic-purple"><i class="fas fa-user-tie"></i></div>
        <h4>Staff Report</h4>
        <p>All staff with roles, salary & status. Total: <strong><?= $staff_total ?></strong> | Active: <strong><?= $staff_active ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('staff','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('staff','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 6. Expired Members Report -->
      <div class="report-card">
        <div class="report-card-icon ic-orange"><i class="fas fa-user-clock"></i></div>
        <h4>Expired / Inactive Members</h4>
        <p>Members whose membership has lapsed. Count: <strong><?= $expiry_soon ?></strong> expired members need follow-up.</p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('expiry','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('expiry','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 7. Due & Overdue Payments -->
      <div class="report-card">
        <div class="report-card-icon ic-pink"><i class="fas fa-exclamation-circle"></i></div>
        <h4>Due & Overdue Payments</h4>
        <p>Pending collections. Amount due: <strong>₹<?= number_format($rev_due,0) ?></strong> across unpaid members.</p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('payment_due','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('payment_due','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 8. Top Paying Members -->
      <div class="report-card">
        <div class="report-card-icon ic-teal"><i class="fas fa-trophy"></i></div>
        <h4>Top Paying Members</h4>
        <p>Members ranked by total payments made. Pulls top 50 members by revenue contribution.</p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('top_members','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('top_members','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 9. Trainer Assignments -->
      <div class="report-card">
        <div class="report-card-icon ic-indigo"><i class="fas fa-person-running"></i></div>
        <h4>Trainer Assignments</h4>
        <p>Which trainer is assigned to which member. Active assignments: <strong><?= $ta_active ?></strong> | Total: <strong><?= $ta_total ?></strong></p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('trainer','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('trainer','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <!-- 10. Daily Attendance Summary -->
      <div class="report-card">
        <div class="report-card-icon ic-cyan"><i class="fas fa-chart-line"></i></div>
        <h4>Daily Attendance Summary</h4>
        <p>Day-wise attendance count for the last 60 days. Useful for trend analysis.</p>
        <div class="report-card-actions">
          <button class="btn-excel" onclick="exportReport('daily_att','excel')"><i class="fas fa-file-excel"></i> Excel</button>
          <button class="btn-pdf"   onclick="exportReport('daily_att','pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
      </div>

    </div><!-- /reports-grid -->

    <!-- ─── SECTION: Custom Report Builder ──────────────────────────────── -->
    <div class="section-heading"><span></span> Custom Report Builder</div>

    <div class="custom-panel">
      <h3><i class="fas fa-sliders" style="margin-right:8px;"></i>Build Your Own Report</h3>
      <p>Choose any table, add a search keyword, and download as Excel or PDF instantly.</p>

      <form id="customForm" style="display:grid;grid-template-columns:1fr 1fr 2fr auto auto;gap:14px;align-items:end;">
        <div>
          <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px;">Table / Data Source</label>
          <select name="table" id="customTable" style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;">
            <option value="members">Members</option>
            <option value="payments">Payments</option>
            <option value="attendance">Attendance</option>
            <option value="staff">Staff</option>
            <option value="equipment">Equipment</option>
            <option value="trainer_assignments">Trainer Assignments</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px;">Search Keyword <span style="font-weight:400;color:#aaa;">(optional)</span></label>
          <input type="text" name="search" id="customSearch" placeholder="e.g. Active, Premium, Cash..."
                 style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px;">Date Range <span style="font-weight:400;color:#aaa;">(where applicable)</span></label>
          <div style="display:flex;gap:8px;">
            <input type="date" name="date_from" value="<?= date('Y-m-01') ?>"
                   style="flex:1;padding:10px 12px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;">
            <input type="date" name="date_to" value="<?= $today ?>"
                   style="flex:1;padding:10px 12px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;">
          </div>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px;">&nbsp;</label>
          <button type="button" onclick="exportCustom('excel')"
                  style="padding:10px 18px;background:#e8f5e9;color:#2e7d32;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
            <i class="fas fa-file-excel"></i> Excel
          </button>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px;">&nbsp;</label>
          <button type="button" onclick="exportCustom('pdf')"
                  style="padding:10px 18px;background:#fde8e8;color:#941614;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
        </div>
      </form>
    </div>

    <!-- ─── SECTION: Quick Stats Previews ───────────────────────────────── -->
    <div class="section-heading"><span></span> Quick Previews</div>

    <div class="row g-4 mb-4">

      <!-- Top Members table -->
      <div class="col-md-6">
        <div class="members-table-container" style="padding:0;">
          <div class="table-header" style="padding:16px 20px;">
            <h3 style="margin:0;font-size:14px;font-weight:700;">🏆 Top Paying Members</h3>
          </div>
          <table class="members-table" style="font-size:13px;">
            <thead><tr><th>#</th><th>Name</th><th>Plan</th><th>Total Paid</th></tr></thead>
            <tbody>
              <?php foreach(array_slice($top_members,0,6) as $i=>$m): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($m['full_name']) ?></td>
                <td><span class="status-badge active" style="font-size:10px;"><?= htmlspecialchars($m['membership_type']) ?></span></td>
                <td style="font-weight:700;color:#2e7d32;">₹<?= number_format($m['total_paid'],0) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($top_members)): ?><tr><td colspan="4" style="text-align:center;color:#aaa;padding:20px;">No data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 7-day attendance -->
      <div class="col-md-6">
        <div class="members-table-container" style="padding:0;">
          <div class="table-header" style="padding:16px 20px;">
            <h3 style="margin:0;font-size:14px;font-weight:700;">📅 Last 7 Days Attendance</h3>
          </div>
          <table class="members-table" style="font-size:13px;">
            <thead><tr><th>Day</th><th>Present</th><th>Visual</th></tr></thead>
            <tbody>
              <?php $max_att = max(array_column($att_week,'count') ?: [1]); ?>
              <?php foreach($att_week as $d): ?>
              <tr>
                <td><?= $d['day'] ?></td>
                <td style="font-weight:700;"><?= $d['count'] ?></td>
                <td>
                  <div style="background:#f0f0f0;border-radius:4px;height:10px;width:100%;overflow:hidden;">
                    <div style="background:#941614;height:10px;border-radius:4px;width:<?= $max_att>0 ? round($d['count']/$max_att*100) : 0 ?>%;"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Revenue 6 months -->
      <div class="col-md-6">
        <div class="members-table-container" style="padding:0;">
          <div class="table-header" style="padding:16px 20px;">
            <h3 style="margin:0;font-size:14px;font-weight:700;">💰 Revenue — Last 6 Months</h3>
          </div>
          <table class="members-table" style="font-size:13px;">
            <thead><tr><th>Month</th><th>Revenue</th><th>Visual</th></tr></thead>
            <tbody>
              <?php $max_rev = max(array_column($rev_6months,'amount') ?: [1]); ?>
              <?php foreach($rev_6months as $rm): ?>
              <tr>
                <td><?= $rm['month'] ?></td>
                <td style="font-weight:700;color:#2e7d32;">₹<?= number_format($rm['amount'],0) ?></td>
                <td>
                  <div style="background:#f0f0f0;border-radius:4px;height:10px;width:100%;overflow:hidden;">
                    <div style="background:#2e7d32;height:10px;border-radius:4px;width:<?= $max_rev>0 ? round($rm['amount']/$max_rev*100) : 0 ?>%;"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payment methods -->
      <div class="col-md-6">
        <div class="members-table-container" style="padding:0;">
          <div class="table-header" style="padding:16px 20px;">
            <h3 style="margin:0;font-size:14px;font-weight:700;">💳 Payment Method Breakdown</h3>
          </div>
          <table class="members-table" style="font-size:13px;">
            <thead><tr><th>Method</th><th>Transactions</th><th>Total Amount</th></tr></thead>
            <tbody>
              <?php foreach($pay_methods as $pm): ?>
              <tr>
                <td><strong><?= htmlspecialchars($pm['payment_method']) ?></strong></td>
                <td><?= $pm['cnt'] ?></td>
                <td style="font-weight:700;color:#1565c0;">₹<?= number_format($pm['total'],0) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($pay_methods)): ?><tr><td colspan="3" style="text-align:center;color:#aaa;padding:20px;">No data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function exportReport(type, format) {
  var url = 'reports.php?export=' + format + '&report=' + type;
  if (format === 'pdf') {
    window.open(url, '_blank');
  } else {
    window.location.href = url;
  }
}

function exportCustom(format) {
  var table  = document.getElementById('customTable').value;
  var search = document.getElementById('customSearch').value;
  var df     = document.querySelector('input[name="date_from"]').value;
  var dt     = document.querySelector('input[name="date_to"]').value;
  var url    = 'reports.php?export=' + format + '&report=custom&table=' + encodeURIComponent(table)
             + '&search=' + encodeURIComponent(search)
             + '&date_from=' + df + '&date_to=' + dt;
  if (format === 'pdf') {
    window.open(url, '_blank');
  } else {
    window.location.href = url;
  }
}
</script>