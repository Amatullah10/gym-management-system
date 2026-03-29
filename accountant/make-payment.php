<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'make-payment';
$page_title = 'Make Payment - Gym Management';

$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';

$where = "WHERE m.membership_status != 'Deleted'";
if ($search)                              { $where .= " AND (m.full_name LIKE '%$search%' OR m.email LIKE '%$search%' OR m.phone LIKE '%$search%')"; }
if ($status_filter === 'Active')          { $where .= " AND m.membership_status = 'Active'"; }
elseif ($status_filter === 'Inactive')    { $where .= " AND m.membership_status = 'Inactive'"; }
elseif ($status_filter === 'Expired')     { $where .= " AND m.membership_status = 'Expired'"; }

$members = [];
$res = mysqli_query($conn, "
    SELECT m.*,
        COALESCE((SELECT payment_date FROM payments WHERE member_id=m.id ORDER BY payment_date DESC LIMIT 1),'—') AS last_payment,
        COALESCE((SELECT SUM(amount)   FROM payments WHERE member_id=m.id AND status='Paid'), 0) AS total_paid,
        COALESCE((SELECT status        FROM payments WHERE member_id=m.id ORDER BY payment_date DESC LIMIT 1),'—') AS last_status
    FROM members m
    $where
    ORDER BY m.full_name ASC
");
while ($row = mysqli_fetch_assoc($res)) { $members[] = $row; }

$total_members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'"))['t'];
$total_due     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT member_id) as t FROM payments WHERE status='Due'"))['t'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT member_id) as t FROM payments WHERE status='Overdue'"))['t'];

$error   = isset($_GET['error'])   ? htmlspecialchars($_GET['error'])   : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Make Payment</h1>
        <p class="page-subtitle">Select a member and proceed to the payment gateway</p>
      </div>
      <a href="record-payment.php" class="btn app-btn-secondary">
        <i class="fa-solid fa-pen-to-square"></i> Manual Record
      </a>
    </div>

    <?php if ($error): ?>
    <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info"><h3><?= $total_members ?></h3><p>Active Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= $total_due ?></h3><p>Due Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3><?= $total_overdue ?></h3><p>Overdue Payments</p></div>
      </div>
    </div>

    <form method="GET" class="flex gap-3 align-center mb-20">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="All"      <?= $status_filter==='All'      ? 'selected':'' ?>>All Members</option>
        <option value="Active"   <?= $status_filter==='Active'   ? 'selected':'' ?>>Active</option>
        <option value="Inactive" <?= $status_filter==='Inactive' ? 'selected':'' ?>>Inactive</option>
        <option value="Expired"  <?= $status_filter==='Expired'  ? 'selected':'' ?>>Expired</option>
      </select>
      <button type="submit" class="btn app-btn-primary">Search</button>
      <?php if ($search || $status_filter !== 'All'): ?>
        <a href="make-payment.php" class="btn app-btn-secondary">Clear</a>
      <?php endif; ?>
    </form>

    <div class="members-table-container">
      <div class="table-header">
        <h3>Members — <?= count($members) ?> records</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Membership</th>
            <th>Status</th>
            <th>Fee</th>
            <th>Last Payment</th>
            <th>Total Paid</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($members)): ?>
            <?php foreach ($members as $m):
              $badge = 'inactive';
              if ($m['membership_status'] === 'Active')    $badge = 'active';
              elseif ($m['membership_status'] === 'Expired') $badge = 'expired';
              $fee = 799;
              if (strpos($m['membership_type'],'Premium') !== false)  $fee = 1299;
              elseif (strpos($m['membership_type'],'Standard') !== false) $fee = 999;
              $last_paid_badge = '';
              if ($m['last_status'] === 'Overdue')      $last_paid_badge = 'expired';
              elseif ($m['last_status'] === 'Due')      $last_paid_badge = 'inactive';
              elseif ($m['last_status'] === 'Paid')     $last_paid_badge = 'active';
            ?>
            <tr>
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= strtoupper(substr($m['full_name'],0,1)) ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($m['email']) ?></span>
                    <span class="joined"><?= htmlspecialchars($m['phone']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($m['membership_type']) ?></td>
              <td><span class="status-badge <?= $badge ?>"><?= $m['membership_status'] ?></span></td>
              <td><strong>₹<?= number_format($fee) ?></strong></td>
              <td class="date-display">
                <?= $m['last_payment'] !== '—' ? date('d M Y', strtotime($m['last_payment'])) : '—' ?>
                <?php if ($last_paid_badge): ?>
                  <br><span class="status-badge <?= $last_paid_badge ?>" style="font-size:10px;"><?= $m['last_status'] ?></span>
                <?php endif; ?>
              </td>
              <td>₹<?= number_format($m['total_paid'], 2) ?></td>
              <td>
                <div class="action-buttons">
                  <a href="payment-gateway.php?member_id=<?= $m['id'] ?>" class="btn app-btn-primary">
                    <i class="fa-solid fa-credit-card"></i> Pay Now
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No members found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>