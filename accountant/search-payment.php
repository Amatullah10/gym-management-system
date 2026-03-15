<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'search-payment';
$page_title = 'Search Payment - Gym Management';

$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$date_filter   = isset($_GET['date'])   ? mysqli_real_escape_string($conn, $_GET['date'])   : '';

// Whitelist dropdowns
$allowed_statuses = ['Paid', 'Due', 'Overdue'];
$allowed_methods  = ['Cash', 'Card', 'UPI', 'Online'];
$status_filter = isset($_GET['status']) && in_array($_GET['status'], $allowed_statuses) ? $_GET['status'] : '';
$method_filter = isset($_GET['method']) && in_array($_GET['method'], $allowed_methods)  ? $_GET['method'] : '';

$where    = "WHERE 1=1";
if ($search)        { $where .= " AND (m.full_name LIKE '%$search%' OR m.email LIKE '%$search%' OR p.transaction_id LIKE '%$search%')"; }
if ($status_filter) { $where .= " AND p.status = '$status_filter'"; }
if ($date_filter)   { $where .= " AND DATE(p.payment_date) = '$date_filter'"; }
if ($method_filter) { $where .= " AND p.payment_method = '$method_filter'"; }

$payments = [];
$searched = isset($_GET['search']) || isset($_GET['status']) || isset($_GET['date']) || isset($_GET['method']);

if ($searched) {
    $res = mysqli_query($conn, "SELECT p.*, m.full_name, m.email, m.phone FROM payments p JOIN members m ON p.member_id = m.id $where ORDER BY p.payment_date DESC");
    while ($row = mysqli_fetch_assoc($res)) { $payments[] = $row; }
}
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Search Payment</h1>
        <p class="page-subtitle">Search payments by member, date, method or status</p>
      </div>
    </div>

    <div class="form-container mb-20">
      <h3 class="section-subtitle">Search Filters</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Member name, email or transaction ID...">
          </div>
          <div>
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
          </div>
        </div>
        <div class="form-row">
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
            <label>Payment Method</label>
            <select name="method">
              <option value="">All Methods</option>
              <option value="Cash"   <?= $method_filter == 'Cash'   ? 'selected' : '' ?>>Cash</option>
              <option value="Card"   <?= $method_filter == 'Card'   ? 'selected' : '' ?>>Card</option>
              <option value="UPI"    <?= $method_filter == 'UPI'    ? 'selected' : '' ?>>UPI</option>
              <option value="Online" <?= $method_filter == 'Online' ? 'selected' : '' ?>>Online</option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
          <a href="search-payment.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
        </div>
      </form>
    </div>

    <?php if ($searched): ?>
    <div class="members-table-container">
      <div class="table-header">
        <h3>Search Results (<?= count($payments) ?> found)</h3>
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
                <td><?= htmlspecialchars($p['phone']) ?></td>
                <td><?= htmlspecialchars($p['service']) ?></td>
                <td><?= htmlspecialchars($p['plan']) ?></td>
                <td>₹<?= number_format($p['amount'], 2) ?></td>
                <td><?= htmlspecialchars($p['payment_method']) ?></td>
                <td class="date-display"><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="9" class="text-center">No payments found matching your search.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="members-table-container">
      <div class="table-header">
        <h3>Search Results</h3>
      </div>
      <div class="text-center">
        <p class="page-subtitle">Use the filters above to search for payments.</p>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>