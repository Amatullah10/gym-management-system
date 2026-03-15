<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page = 'due-payments';
$page_title = 'Due Payments - Gym Management';

$due_payments = [];
$res = mysqli_query($conn, "SELECT p.*, m.full_name, m.email, m.phone FROM payments p JOIN members m ON p.member_id = m.id WHERE p.status = 'Due' ORDER BY p.payment_date ASC");
while ($row = mysqli_fetch_assoc($res)) { $due_payments[] = $row; }

$total_due    = count($due_payments);
$total_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Due Payments</h1>
        <p class="page-subtitle">Members with pending due payments</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= $total_due ?></h3><p>Total Due Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_amount ?? 0, 2) ?></h3><p>Total Due Amount</p></div>
      </div>
    </div>

    <div class="members-table-container">
      <div class="table-header">
        <h3>Due Payments (<?= $total_due ?>)</h3>
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
              <th>Amount Due</th>
              <th>Due Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($due_payments)): ?>
              <?php foreach ($due_payments as $i => $p): ?>
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
                <td class="date-display"><span class="status-badge inactive"><?= date('d M Y', strtotime($p['payment_date'])) ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No due payments found.</td></tr>
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