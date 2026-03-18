<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'dashboard';
$page_title = 'Accountant Dashboard - Gym Management';

$today = date('Y-m-d');

// Safe query helper — returns 0 instead of crashing if a query fails
function safe_query_value($conn, $sql) {
    $res = @mysqli_query($conn, $sql);
    if (!$res) return 0;
    $row = mysqli_fetch_assoc($res);
    return $row['t'] ?? 0;
}

// Total income
$total_income = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Paid'");

// Today's payments
$today_income = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE DATE(payment_date)='$today' AND status='Paid'");
$today_count  = safe_query_value($conn, "SELECT COUNT(*) as t FROM payments WHERE DATE(payment_date)='$today'");

// Due payments
$due_count  = safe_query_value($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Due'");
$due_amount = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due'");

// Overdue payments
$overdue_count  = safe_query_value($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Overdue'");
$overdue_amount = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Overdue'");

// Recent transactions
$recent = [];
$res = @mysqli_query($conn, "SELECT p.*, m.full_name, m.email FROM payments p JOIN members m ON p.member_id = m.id ORDER BY p.payment_date DESC LIMIT 10");
if ($res) { while ($row = mysqli_fetch_assoc($res)) { $recent[] = $row; } }

// Finance summary queries
$current_month    = date('Y-m');
$monthly_revenue  = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'");
$total_payroll    = safe_query_value($conn, "SELECT SUM(net_salary) as t FROM salary_payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month'");
$payroll_staff    = safe_query_value($conn, "SELECT COUNT(DISTINCT staff_id) as t FROM salary_payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month'");
$op_expenses      = safe_query_value($conn, "SELECT SUM(amount) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Approved'");
$pending_expenses = safe_query_value($conn, "SELECT COUNT(*) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Pending'");
$net_profit       = $monthly_revenue - $total_payroll - $op_expenses;

// Member collections summary queries
$collected_amount      = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'");
$collected_count       = safe_query_value($conn, "SELECT COUNT(*) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'");
$due_this_month_amount = safe_query_value($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due'");
$due_this_month_count  = safe_query_value($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Due'");
$total_members         = safe_query_value($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'");
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Accountant Dashboard</h1>
        <p class="page-subtitle">Financial overview — <?= date('d M Y') ?></p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_income ?? 0, 2) ?></h3><p>Total Income</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-calendar-day"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($today_income ?? 0, 2) ?></h3><p>Today's Collection (<?= $today_count ?> payments)</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= $due_count ?></h3><p>Due Payments (₹<?= number_format($due_amount ?? 0, 2) ?>)</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3><?= $overdue_count ?></h3><p>Overdue Payments (₹<?= number_format($overdue_amount ?? 0, 2) ?>)</p></div>
      </div>
    </div>

    <!-- Finance Summary -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3>Finance Summary — <?= date('F Y') ?></h3>
      </div>
      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($monthly_revenue ?? 0, 2) ?></h3>
              <p>Monthly Revenue</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($total_payroll ?? 0, 2) ?></h3>
              <p>Total Payroll (<?= $payroll_staff ?? 0 ?> staff)</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-dollar-sign"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($op_expenses ?? 0, 2) ?></h3>
              <p>Operating Expenses (<?= $pending_expenses ?? 0 ?> pending)</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon <?= ($net_profit >= 0) ? 'green' : 'danger' ?>"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($net_profit, 2) ?></h3>
              <p>Net Profit (after payroll &amp; expenses)</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Member Collections Overview -->
    <div class="members-table-container mb-20">
      <div class="table-header flex justify-between align-center">
        <h3>Member Collections Overview</h3>
        <a href="member-collections.php" class="page-subtitle">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="main-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($collected_amount ?? 0, 2) ?></h3>
              <p>Collected — <?= $collected_count ?? 0 ?> members</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($due_this_month_amount ?? 0, 2) ?></h3>
              <p>Due This Month — <?= $due_this_month_count ?? 0 ?> members</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-info">
              <h3>₹<?= number_format($overdue_amount ?? 0, 2) ?></h3>
              <p>Overdue — <?= $overdue_count ?? 0 ?> members</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
              <h3><?= $total_members ?? 0 ?></h3>
              <p>Total Active Members</p>
            </div>
          </div>
        </div>
        <?php if (($overdue_count ?? 0) > 0): ?>
        <div class="app-alert app-alert-error mt-10">
          <i class="fa-solid fa-circle-exclamation"></i>
          <strong><?= $overdue_count ?> members</strong> have overdue payments totalling
          <strong>₹<?= number_format($overdue_amount ?? 0, 2) ?></strong> — action required.
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="members-table-container">
      <div class="table-header flex justify-between align-center">
        <h3>Recent Transactions</h3>
        <a href="payment-list.php" class="page-subtitle">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div>
        <table class="members-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Member</th>
              <th>Service</th>
              <th>Plan</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($recent)): ?>
              <?php foreach ($recent as $i => $p):
                if ($p['status'] == 'Paid')         { $badge = 'active'; }
                elseif ($p['status'] == 'Due')       { $badge = 'inactive'; }
                else                                 { $badge = 'expired'; }
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($p['full_name'], 0, 1)) ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($p['full_name']) ?></span>
                      <span class="joined"><?= htmlspecialchars($p['email']) ?></span>
                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($p['service']) ?></td>
                <td><?= htmlspecialchars($p['plan']) ?></td>
                <td>₹<?= number_format($p['amount'], 2) ?></td>
                <td><?= htmlspecialchars($p['payment_method']) ?></td>
                <td class="date-display"><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center">No transactions found.</td></tr>
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