<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'salary-payments';
$page_title = 'Salary Payments - Gym Management';

$current_month = date('Y-m');

// Handle Pay Now
if (isset($_POST['pay_now']) && !empty($_POST['salary_id'])) {
    $sid = (int)$_POST['salary_id'];
    mysqli_query($conn, "UPDATE salary_payments SET status='Paid', payment_date=NOW() WHERE id=$sid");
    header("Location: salary-payments.php");
    exit();
}

// Summary stats
$total_paid_amount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as t FROM salary_payments WHERE status='Paid'"))['t'];
$total_paid_staff   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT staff_id) as t FROM salary_payments WHERE status='Paid'"))['t'];
$pending_amount     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as t FROM salary_payments WHERE status='Pending'"))['t'];
$pending_staff      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT staff_id) as t FROM salary_payments WHERE status='Pending'"))['t'];
$processing_amount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as t FROM salary_payments WHERE status='Processing'"))['t'];
$processing_staff   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT staff_id) as t FROM salary_payments WHERE status='Processing'"))['t'];

// Filters
$filter_period = isset($_GET['period']) ? $_GET['period'] : 'All';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'All';
$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$where = "WHERE 1=1";
if ($filter_period === 'current') {
    $where .= " AND DATE_FORMAT(sp.period_month,'%Y-%m')='$current_month'";
} elseif ($filter_period === 'last') {
    $last_month = date('Y-m', strtotime('first day of last month'));
    $where .= " AND DATE_FORMAT(sp.period_month,'%Y-%m')='$last_month'";
}
if ($filter_status === 'Paid')           { $where .= " AND sp.status='Paid'"; }
elseif ($filter_status === 'Pending')    { $where .= " AND sp.status='Pending'"; }
elseif ($filter_status === 'Processing') { $where .= " AND sp.status='Processing'"; }
if ($search !== '') { $where .= " AND s.full_name LIKE '%$search%'"; }

$salaries = [];
$res = mysqli_query($conn, "
    SELECT sp.*, s.full_name, s.role AS staff_role
    FROM salary_payments sp
    JOIN staff s ON sp.staff_id = s.id
    $where
    ORDER BY
      CASE sp.status WHEN 'Pending' THEN 1 WHEN 'Processing' THEN 2 ELSE 3 END,
      sp.period_month DESC
");
while ($row = mysqli_fetch_assoc($res)) { $salaries[] = $row; }
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Salary Payments</h1>
        <p class="page-subtitle">NextGen Fitness — <?= date('F Y') ?></p>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($total_paid_amount ?? 0, 2) ?></h3>
          <p>Total Paid — <?= $total_paid_staff ?? 0 ?> staff</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($pending_amount ?? 0, 2) ?></h3>
          <p>Pending Payment — <?= $pending_staff ?? 0 ?> staff</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-spinner"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($processing_amount ?? 0, 2) ?></h3>
          <p>Processing — <?= $processing_staff ?? 0 ?> staff</p>
        </div>
      </div>
    </div>

    <!-- Search & Filters -->
    <form method="GET" action="" class="flex gap-3 align-center mb-20">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Search staff..."
               value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="period" class="filter-select" onchange="this.form.submit()">
        <option value="All"     <?= $filter_period === 'All'     ? 'selected' : '' ?>>All Periods</option>
        <option value="current" <?= $filter_period === 'current' ? 'selected' : '' ?>>This Month</option>
        <option value="last"    <?= $filter_period === 'last'    ? 'selected' : '' ?>>Last Month</option>
      </select>
      <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="All"        <?= $filter_status === 'All'        ? 'selected' : '' ?>>All Status</option>
        <option value="Paid"       <?= $filter_status === 'Paid'       ? 'selected' : '' ?>>Paid</option>
        <option value="Pending"    <?= $filter_status === 'Pending'    ? 'selected' : '' ?>>Pending</option>
        <option value="Processing" <?= $filter_status === 'Processing' ? 'selected' : '' ?>>Processing</option>
      </select>
      <button type="submit" class="btn app-btn-primary">Search</button>
      <?php if ($search !== '' || $filter_period !== 'All' || $filter_status !== 'All'): ?>
        <a href="salary-payments.php" class="btn app-btn-secondary">Clear</a>
      <?php endif; ?>
    </form>

    <!-- Salary Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Staff Salary Records</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>Staff Member</th>
            <th>Period</th>
            <th>Base Salary</th>
            <th>Net Salary</th>
            <th>Status</th>
            <th>Paid On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($salaries)): ?>
            <?php foreach ($salaries as $s):
              if ($s['status'] === 'Paid')            { $badge = 'active'; }
              elseif ($s['status'] === 'Processing')  { $badge = 'inactive'; }
              else                                    { $badge = 'expired'; }
            ?>
            <tr>
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= strtoupper(substr($s['full_name'], 0, 1)) ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($s['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($s['staff_role']) ?></span>
                  </div>
                </div>
              </td>
              <td class="date-display"><?= $s['period_month'] ? date('M Y', strtotime($s['period_month'])) : '—' ?></td>
              <td>₹<?= number_format($s['base_salary'] ?? 0, 2) ?></td>
              <td><strong>₹<?= number_format($s['net_salary'] ?? 0, 2) ?></strong></td>
              <td><span class="status-badge <?= $badge ?>"><?= $s['status'] ?></span></td>
              <td class="date-display"><?= $s['payment_date'] ? date('d M Y', strtotime($s['payment_date'])) : '—' ?></td>
              <td>
                <div class="action-buttons">
                  <?php if ($s['status'] === 'Paid'): ?>
                    <a href="payslip.php?id=<?= $s['id'] ?>" class="btn app-btn-secondary">
                      <i class="fa-solid fa-file-invoice"></i> Payslip
                    </a>
                  <?php else: ?>
                    <form method="POST" action="">
                      <input type="hidden" name="salary_id" value="<?= $s['id'] ?>">
                      <button type="submit" name="pay_now" class="btn app-btn-primary">
                        <i class="fa-solid fa-dollar-sign"></i> Pay Now
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No salary records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>