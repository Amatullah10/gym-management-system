<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'payment-list';
$page_title = 'Payment List - Gym Management';

$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$plan_filter   = isset($_GET['plan'])   ? mysqli_real_escape_string($conn, $_GET['plan'])   : '';

// Whitelist status and plan to prevent injection
$allowed_statuses = ['Paid', 'Due', 'Overdue'];
$allowed_plans    = ['Monthly', 'Quarterly', 'Yearly'];
if (!in_array($status_filter, $allowed_statuses)) { $status_filter = ''; }
if (!in_array($plan_filter, $allowed_plans))       { $plan_filter = ''; }

$where = "WHERE 1=1";
if ($search)        { $where .= " AND (m.full_name LIKE '%$search%' OR m.email LIKE '%$search%')"; }
if ($status_filter) { $where .= " AND p.status = '$status_filter'"; }
if ($plan_filter)   { $where .= " AND p.plan = '$plan_filter'"; }

$payments = [];
$res = mysqli_query($conn, "SELECT p.*, m.full_name, m.email FROM payments p JOIN members m ON p.member_id = m.id $where ORDER BY p.payment_date DESC");
while ($row = mysqli_fetch_assoc($res)) { $payments[] = $row; }

$total_paid    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments p JOIN members m ON p.member_id = m.id $where AND p.status='Paid'"))['t'];
$total_due     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments p JOIN members m ON p.member_id = m.id $where AND p.status='Due'"))['t'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM payments p JOIN members m ON p.member_id = m.id $where AND p.status='Overdue'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Payment List</h1>
        <p class="page-subtitle">All payment records</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info"><h3><?= $total_paid ?></h3><p>Paid</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info"><h3><?= $total_due ?></h3><p>Due</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info"><h3><?= $total_overdue ?></h3><p>Overdue</p></div>
      </div>
    </div>

    <div class="form-container mb-20">
      <h3 class="section-subtitle">Search & Filter</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>Search Member</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name or email...">
          </div>
          <div>
            <label>Status</label>
            <select name="status">
              <option value="">All Status</option>
              <option value="Paid"    <?= $status_filter == 'Paid'    ? 'selected' : '' ?>>Paid</option>
              <option value="Due"     <?= $status_filter == 'Due'     ? 'selected' : '' ?>>Due</option>
              <option value="Overdue" <?= $status_filter == 'Overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
          </div>
          <div>
            <label>Plan</label>
            <select name="plan">
              <option value="">All Plans</option>
              <option value="Monthly"   <?= $plan_filter == 'Monthly'   ? 'selected' : '' ?>>Monthly</option>
              <option value="Quarterly" <?= $plan_filter == 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
              <option value="Yearly"    <?= $plan_filter == 'Yearly'    ? 'selected' : '' ?>>Yearly</option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
          <a href="payment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
          <a href="record-payment.php" class="btn app-btn-primary"><i class="fa-solid fa-plus"></i> Add Payment</a>
        </div>
      </form>
    </div>

    <div class="members-table-container">
      <div class="table-header">
        <h3>All Payments (<?= count($payments) ?> found)</h3>
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