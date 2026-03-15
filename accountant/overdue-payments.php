<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page = 'overdue-payments';
$page_title = 'Overdue Payments - Gym Management';

$overdue_payments = [];
$res = mysqli_query($conn, "SELECT p.*, m.full_name, m.email, m.phone FROM payments p JOIN members m ON p.member_id = m.id WHERE p.status = 'Overdue' ORDER BY p.payment_date ASC");
while ($row = mysqli_fetch_assoc($res)) { $overdue_payments[] = $row; }

$total_overdue = count($overdue_payments);
$total_amount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Overdue'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Overdue Payments</h1>
        <p class="page-subtitle">Members with overdue payments — immediate action required</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3><?= $total_overdue ?></h3><p>Total Overdue Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_amount ?? 0, 2) ?></h3><p>Total Overdue Amount</p></div>
      </div>
    </div>

    <div class="members-table-container">
      <div class="table-header">
        <h3>Overdue Payments (<?= $total_overdue ?>)</h3>
      </div>
      <div>
        <table class="members-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Member</th>
              <th>Phone</th>
              <th>Service</th>
              <th>Plan</th>
              <th>Amount Overdue</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($overdue_payments)): ?>
              <?php foreach ($overdue_payments as $i => $p): ?>
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
                <td><?= htmlspecialchars($p['phone']) ?></td>
                <td><?= htmlspecialchars($p['service']) ?></td>
                <td><?= htmlspecialchars($p['plan']) ?></td>
                <td>₹<?= number_format($p['amount'], 2) ?></td>
                <td class="date-display"><span class="status-badge expired"><?= date('d M Y', strtotime($p['payment_date'])) ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No overdue payments found.</td></tr>
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