<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'member-collections';
$page_title = 'Member Collections - Gym Management';

$current_month = date('Y-m');

// Handle Mark Paid
if (isset($_POST['mark_paid']) && !empty($_POST['payment_id'])) {
    $pid = (int)$_POST['payment_id'];
    mysqli_query($conn, "UPDATE payments SET status='Paid', payment_date=NOW() WHERE id=$pid");
    header("Location: member-collections.php");
    exit();
}

// Handle Send Reminder
if (isset($_POST['send_reminder']) && !empty($_POST['payment_id'])) {
    $reminder_sent = true;
    // TODO: add email/SMS logic here
}

// Summary stats
$collected_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'"))['t'];
$collected_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m')='$current_month' AND status='Paid'"))['t'];

// FIXED: Removed DATE_FORMAT filter — show ALL unpaid dues, not just current month
$due_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Due'"))['t'];
$due_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Due'"))['t'];

$overdue_amount   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='Overdue'"))['t'];
$overdue_count    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments WHERE status='Overdue'"))['t'];
$total_members    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'"))['t'];

// Filters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'All';
$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$where = "WHERE 1=1";
if ($filter_status === 'Paid')        { $where .= " AND p.status='Paid'"; }
elseif ($filter_status === 'Due')     { $where .= " AND p.status='Due'"; }    // FIXED: Removed DATE_FORMAT filter here too
elseif ($filter_status === 'Overdue') { $where .= " AND p.status='Overdue'"; }
if ($search !== '') {
    $where .= " AND (m.full_name LIKE '%$search%' OR m.id LIKE '%$search%')";
}

$payments = [];
$res = mysqli_query($conn, "
    SELECT p.*, m.full_name, m.email, m.id AS member_code,
           DATEDIFF(NOW(), p.payment_date) AS days_overdue
    FROM payments p
    JOIN members m ON p.member_id = m.id
    $where
    ORDER BY
      CASE p.status WHEN 'Overdue' THEN 1 WHEN 'Due' THEN 2 ELSE 3 END,
      p.payment_date ASC
");
while ($row = mysqli_fetch_assoc($res)) { $payments[] = $row; }
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Member Collections</h1>
        <p class="page-subtitle">NextGen Fitness — <?= date('F Y') ?></p>
      </div>
    </div>

    <?php if (!empty($reminder_sent)): ?>
    <div class="app-alert app-alert-success">
      <i class="fa-solid fa-circle-check"></i> Reminder sent successfully.
    </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($collected_amount ?? 0, 2) ?></h3>
          <p>Collected (Paid) — <?= $collected_count ?? 0 ?> members</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($due_amount ?? 0, 2) ?></h3>
          <p>Due — <?= $due_count ?? 0 ?> members</p>
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
    <div class="app-alert app-alert-error">
      <i class="fa-solid fa-circle-exclamation"></i>
      <strong><?= $overdue_count ?> members</strong> have overdue payments totalling
      <strong>₹<?= number_format($overdue_amount ?? 0, 2) ?></strong> — action required.
    </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <form method="GET" action="" class="flex gap-3 align-center mb-20">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Search by name or member ID..."
               value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="All"     <?= $filter_status === 'All'     ? 'selected' : '' ?>>All</option>
        <option value="Paid"    <?= $filter_status === 'Paid'    ? 'selected' : '' ?>>Paid</option>
        <option value="Due"     <?= $filter_status === 'Due'     ? 'selected' : '' ?>>Due</option>
        <option value="Overdue" <?= $filter_status === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
      </select>
      <button type="submit" class="btn app-btn-primary">Search</button>
      <?php if ($search !== '' || $filter_status !== 'All'): ?>
        <a href="member-collections.php" class="btn app-btn-secondary">Clear</a>
      <?php endif; ?>
    </form>

    <!-- Collections Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Payment Records</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Plan</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $p):
              if ($p['status'] === 'Paid')        { $badge = 'active'; }
              elseif ($p['status'] === 'Due')     { $badge = 'inactive'; }
              else                                { $badge = 'expired'; }
            ?>
            <tr>
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= strtoupper(substr($p['full_name'], 0, 1)) ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($p['full_name']) ?></span>
                    <span class="joined">
                      Member #<?= htmlspecialchars($p['member_code']) ?>
                      <?php if ($p['status'] === 'Overdue' && $p['days_overdue'] > 0): ?>
                        &nbsp;<span class="app-badge app-badge-danger"><?= $p['days_overdue'] ?>d overdue</span>
                      <?php endif; ?>
                    </span>
                    <span class="joined"><?= htmlspecialchars($p['email']) ?></span>
                  </div>
                </div>
              </td>
              <td><span class="plan-badge <?= strtolower($p['plan'] ?? 'basic') ?>"><?= htmlspecialchars($p['plan'] ?? '—') ?></span></td>
              <td><strong>₹<?= number_format($p['amount'], 2) ?></strong></td>
              <td class="date-display">
                <?= $p['payment_date'] ? date('d M Y', strtotime($p['payment_date'])) : '—' ?>
              </td>
              <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
              <td>
                <div class="action-buttons">
                  <?php if ($p['status'] === 'Paid'): ?>
                    <a href="receipt.php?id=<?= $p['id'] ?>" class="btn app-btn-secondary">
                      <i class="fa-solid fa-receipt"></i> Receipt
                    </a>
                  <?php else: ?>
                    <form method="POST" action="">
                      <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                      <button type="submit" name="mark_paid" class="btn app-btn-primary">
                        <i class="fa-solid fa-check"></i> Mark Paid
                      </button>
                    </form>
                    <form method="POST" action="">
                      <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                      <button type="submit" name="send_reminder" class="btn app-btn-secondary">
                        <i class="fa-solid fa-paper-plane"></i> Remind
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No payment records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>