<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'payment-report';
$page_title = 'Payment Report - Gym Management';

$from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : date('Y-m-01');
$to_date   = isset($_GET['to_date'])   ? mysqli_real_escape_string($conn, $_GET['to_date'])   : date('Y-m-d');

// Overall income stats
$total_income  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Paid' AND DATE(payment_date) BETWEEN '$from_date' AND '$to_date'"))['t'];
$total_due     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due' AND DATE(payment_date) BETWEEN '$from_date' AND '$to_date'"))['t'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Overdue' AND DATE(payment_date) BETWEEN '$from_date' AND '$to_date'"))['t'];
$total_count   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE DATE(payment_date) BETWEEN '$from_date' AND '$to_date'"))['t'];

// Monthly breakdown
$monthly = [];
$res = mysqli_query($conn, "SELECT DATE_FORMAT(payment_date, '%b %Y') as month, SUM(amount) as total, COUNT(*) as count FROM payments WHERE status='Paid' AND DATE(payment_date) BETWEEN '$from_date' AND '$to_date' GROUP BY DATE_FORMAT(payment_date, '%Y-%m') ORDER BY DATE_FORMAT(payment_date, '%Y-%m') ASC");
while ($row = mysqli_fetch_assoc($res)) { $monthly[] = $row; }

// Payment method breakdown
$methods = [];
$res2 = mysqli_query($conn, "SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM payments WHERE status='Paid' AND DATE(payment_date) BETWEEN '$from_date' AND '$to_date' GROUP BY payment_method");
while ($row = mysqli_fetch_assoc($res2)) { $methods[] = $row; }

// All payments in range
$payments = [];
$res3 = mysqli_query($conn, "SELECT p.*, m.full_name FROM payments p JOIN members m ON p.member_id = m.id WHERE DATE(p.payment_date) BETWEEN '$from_date' AND '$to_date' ORDER BY p.payment_date DESC");
while ($row = mysqli_fetch_assoc($res3)) { $payments[] = $row; }
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Payment Report</h1>
        <p class="page-subtitle">Income and payment summary — <?= date('d M Y', strtotime($from_date)) ?> to <?= date('d M Y', strtotime($to_date)) ?></p>
      </div>
    </div>

    <div class="form-container mb-20">
      <h3 class="section-subtitle">Filter by Date Range</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= $from_date ?>">
          </div>
          <div>
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= $to_date ?>">
          </div>
        </div>
        <div class="flex gap-3 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-filter"></i> Apply Filter</button>
          <a href="payment-report.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
        </div>
      </form>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_income ?? 0, 2) ?></h3><p>Total Income (Paid)</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_due ?? 0, 2) ?></h3><p>Due Amount</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_overdue ?? 0, 2) ?></h3><p>Overdue Amount</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-receipt"></i></div>
        <div class="stat-info"><h3><?= $total_count ?></h3><p>Total Transactions</p></div>
      </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="members-table-container mb-20">
      <div class="table-header">
        <h3>Monthly Breakdown</h3>
      </div>
      <div>
        <table class="members-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Month</th>
              <th>Transactions</th>
              <th>Total Collected</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($monthly)): ?>
              <?php foreach ($monthly as $i => $m): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $m['month'] ?></td>
                <td><?= $m['count'] ?></td>
                <td>₹<?= number_format($m['total'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center">No data for selected range.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="members-table-container mb-20">
      <div class="table-header">
        <h3>Payment Method Breakdown</h3>
      </div>
      <div>
        <table class="members-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Method</th>
              <th>Transactions</th>
              <th>Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($methods)): ?>
              <?php foreach ($methods as $i => $m): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($m['payment_method']) ?></td>
                <td><?= $m['count'] ?></td>
                <td>₹<?= number_format($m['total'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center">No data for selected range.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Full Payment List -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Payments in Range (<?= count($payments) ?>)</h3>
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
            <?php if (!empty($payments)): ?>
              <?php foreach ($payments as $i => $p):
                if ($p['status'] == 'Paid')         { $badge = 'active'; }
                elseif ($p['status'] == 'Due')       { $badge = 'inactive'; }
                else                                 { $badge = 'expired'; }
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($p['full_name'], 0, 1)) ?></div>
                    <div class="member-info"><span class="name"><?= htmlspecialchars($p['full_name']) ?></span></div>
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
              <tr><td colspan="8" class="text-center">No payments found.</td></tr>
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