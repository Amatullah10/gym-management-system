<?php
session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['admin', 'accountant'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../dbcon.php';

$page = 'payment-list';

// --- Stats ---
$this_month = date('Y-m');

$q_month = mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m') = '$this_month'");
$month_total = mysqli_fetch_assoc($q_month)['total'];

$q_collected = mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE status='Paid' AND DATE_FORMAT(payment_date,'%Y-%m') = '$this_month'");
$collected = mysqli_fetch_assoc($q_collected)['total'];

$pending = $month_total - $collected;

// --- Fetch all members with their last payment ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where  = $search ? "WHERE m.full_name LIKE '%$search%'" : '';

$sql = "SELECT m.id, m.full_name, m.membership_type,
               p.amount, p.payment_date, p.service, p.plan, p.status
        FROM members m
        LEFT JOIN payments p ON p.id = (
            SELECT id FROM payments WHERE member_id = m.id ORDER BY payment_date DESC LIMIT 1
        )
        $where
        ORDER BY m.full_name ASC";
$result = mysqli_query($conn, $sql);
$members = [];
while ($row = mysqli_fetch_assoc($result)) $members[] = $row;

// --- Alert / Reminder POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remind_member_id'])) {
    $mid = (int)$_POST['remind_member_id'];
    $msg = "Payment reminder sent for member ID $mid.";
    $mname = mysqli_real_escape_string($conn, $_POST['remind_member_name']);
    $note  = "Payment reminder sent to $mname by " . $_SESSION['email'];
    mysqli_query($conn, "INSERT INTO payment_reminders (member_id, type, message, sent_by) VALUES ($mid, 'General', '$note', '{$_SESSION['email']}')");
    $alert_msg = "Reminder sent for <strong>$mname</strong>!";
}

$role = $_SESSION['role'];
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <?php if (isset($alert_msg)): ?>
      <div class="app-alert app-alert-success"><?= $alert_msg ?></div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
      <i class="fas fa-credit-card" style="color:var(--active-color);font-size:24px;"></i>
      <div>
        <h2 class="page-title" style="margin:0;">Payments</h2>
        <p class="page-subtitle">Manage member payments and subscriptions</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:25px;">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
          <p>This Month</p>
          <h3>₹<?= number_format($month_total) ?></h3>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
          <p>Collected</p>
          <h3>₹<?= number_format($collected) ?></h3>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
          <p>Pending</p>
          <h3>₹<?= number_format($pending) ?></h3>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="search-container">
      <form method="GET">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search member by name..." value="<?= htmlspecialchars($search) ?>">
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="table-container">
      <div class="table-header">
        <h3>Member Payments</h3>
      </div>
      <table class="modern-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Last Payment Date</th>
            <th>Amount</th>
            <th>Chosen Service</th>
            <th>Plan</th>
            <th>Action</th>
            <th>Remind</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($members)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#999;">No members found.</td></tr>
          <?php else: ?>
          <?php foreach ($members as $i => $m): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= htmlspecialchars($m['full_name']) ?></strong></td>
            <td><?= $m['payment_date'] ? date('Y-m-d', strtotime($m['payment_date'])) : '<span style="color:#bbb;">No payment yet</span>' ?></td>
            <td><?= $m['amount'] ? '₹' . number_format($m['amount']) : '—' ?></td>
            <td><?= htmlspecialchars($m['service'] ?? '—') ?></td>
            <td><?= htmlspecialchars($m['plan'] ?? '—') ?></td>
            <td>
              <a href="payment-form.php?member_id=<?= $m['id'] ?>" class="btn" style="background:#2e7d32;color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-rupee-sign"></i> Make Payment
              </a>
            </td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="remind_member_id" value="<?= $m['id'] ?>">
                <input type="hidden" name="remind_member_name" value="<?= htmlspecialchars($m['full_name']) ?>">
                <button type="submit" class="btn" style="background:#d32f2f;color:#fff;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                  <i class="fas fa-bell"></i> Alert
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>