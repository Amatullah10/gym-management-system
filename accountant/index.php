<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page = 'dashboard';
$page_title = 'Accountant Dashboard - Gym Management';

$today = date('Y-m-d');

// Total income
$total_income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Paid'"))['t'];

// Today's payments
$today_income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE DATE(payment_date)='$today' AND status='Paid'"))['t'];
$today_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE DATE(payment_date)='$today'"))['t'];

// Due payments
$due_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Due'"))['t'];
$due_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due'"))['t'];

// Overdue payments
$overdue_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Overdue'"))['t'];
$overdue_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Overdue'"))['t'];

// Recent transactions
$recent = [];
$res = mysqli_query($conn, "SELECT p.*, m.full_name, m.email FROM payments p JOIN members m ON p.member_id = m.id ORDER BY p.payment_date DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($res)) { $recent[] = $row; }
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

    <div class="members-table-container">
      <div class="table-header flex justify-between align-center">
        <h3>Recent Transactions</h3>
        <a href="payment_list.php" class="page-subtitle">View All <i class="fa-solid fa-arrow-right"></i></a>
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
                if ($p['status'] == 'Paid')     { $badge = 'active'; }
                elseif ($p['status'] == 'Due')  { $badge = 'inactive'; }
                else                            { $badge = 'expired'; }
              ?>
              <tr>
                <td><?= $i+1 ?></td>
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