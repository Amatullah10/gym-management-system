<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'expenses';
$page_title = 'Expenses - Gym Management';

$current_month = date('Y-m');

// Handle Approve / Reject
if (isset($_POST['action']) && !empty($_POST['expense_id'])) {
    $eid    = (int)$_POST['expense_id'];
    $action = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';
    mysqli_query($conn, "UPDATE expenses SET status='$action' WHERE id=$eid");
    header("Location: expenses.php");
    exit();
}

// Handle Add Expense
if (isset($_POST['add_expense'])) {
    $category     = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description  = mysqli_real_escape_string($conn, trim($_POST['description']));
    $amount       = (float)$_POST['amount'];
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    mysqli_query($conn, "INSERT INTO expenses (category, description, amount, expense_date, status)
                         VALUES ('$category','$description','$amount','$expense_date','Pending')");
    header("Location: expenses.php");
    exit();
}

// Summary stats — current month
$approved_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Approved'"))['t'];
$approved_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Approved'"))['t'];
$pending_amount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Pending'"))['t'];
$pending_count   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Pending'"))['t'];
$rejected_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Rejected'"))['t'];
$rejected_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$current_month' AND status='Rejected'"))['t'];

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'All';

$where = "WHERE 1=1";
if ($filter_status === 'Approved')     { $where .= " AND status='Approved'"; }
elseif ($filter_status === 'Pending')  { $where .= " AND status='Pending'"; }
elseif ($filter_status === 'Rejected') { $where .= " AND status='Rejected'"; }

$expenses = [];
$res = mysqli_query($conn, "SELECT * FROM expenses $where ORDER BY expense_date DESC");
while ($row = mysqli_fetch_assoc($res)) { $expenses[] = $row; }
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header flex justify-between align-center">
      <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">NextGen Fitness — <?= date('F Y') ?></p>
      </div>
      <button class="btn app-btn-primary" onclick="toggleExpenseForm()">
        <i class="fa-solid fa-plus"></i> Add Expense
      </button>
    </div>

    <!-- Add Expense Form (hidden by default) -->
    <div id="addExpenseForm" class="hidden">
      <div class="form-container mb-20">
        <h3>Add New Expense</h3>
        <form method="POST" action="">
          <div class="form-row">
            <div>
              <label>Category</label>
              <select name="category" required>
                <option value="">Select Category</option>
                <option value="Equipment">Equipment</option>
                <option value="Utilities">Utilities</option>
                <option value="Marketing">Marketing</option>
                <option value="Supplies">Supplies</option>
                <option value="Rent">Rent</option>
                <option value="Insurance">Insurance</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label>Amount (₹)</label>
              <input type="number" name="amount" step="0.01" min="0" placeholder="0.00" required>
            </div>
          </div>
          <div class="form-row">
            <div>
              <label>Description</label>
              <input type="text" name="description" placeholder="Brief description" required>
            </div>
            <div>
              <label>Date</label>
              <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
            </div>
          </div>
          <div class="flex gap-3 mt-10">
            <button type="submit" name="add_expense" class="btn app-btn-primary">
              <i class="fa-solid fa-plus"></i> Save Expense
            </button>
            <button type="button" class="btn app-btn-secondary" onclick="toggleExpenseForm()">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($approved_amount ?? 0, 2) ?></h3>
          <p>Approved (<?= $approved_count ?? 0 ?>)</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($pending_amount ?? 0, 2) ?></h3>
          <p>Pending (<?= $pending_count ?? 0 ?>)</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon danger"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-info">
          <h3>₹<?= number_format($rejected_amount ?? 0, 2) ?></h3>
          <p>Rejected (<?= $rejected_count ?? 0 ?>)</p>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-20">
      <?php foreach (['All', 'Approved', 'Pending', 'Rejected'] as $s): ?>
        <a href="?status=<?= $s ?>"
           class="btn <?= ($filter_status === $s) ? 'app-btn-primary' : 'app-btn-secondary' ?>">
          <?= $s ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Expenses Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Expense Records</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($expenses)): ?>
            <?php foreach ($expenses as $e):
              if ($e['status'] === 'Approved')     { $badge = 'active'; }
              elseif ($e['status'] === 'Pending')  { $badge = 'inactive'; }
              else                                 { $badge = 'expired'; }
            ?>
            <tr>
              <td>E<?= str_pad($e['id'], 3, '0', STR_PAD_LEFT) ?></td>
              <td><span class="app-badge app-badge-warning"><?= htmlspecialchars($e['category']) ?></span></td>
              <td><?= htmlspecialchars($e['description']) ?></td>
              <td><strong>₹<?= number_format($e['amount'], 2) ?></strong></td>
              <td class="date-display"><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
              <td><span class="status-badge <?= $badge ?>"><?= $e['status'] ?></span></td>
              <td>
                <div class="action-buttons">
                  <?php if ($e['status'] === 'Pending'): ?>
                    <form method="POST" action="">
                      <input type="hidden" name="expense_id" value="<?= $e['id'] ?>">
                      <button type="submit" name="action" value="approve" class="btn app-btn-primary">
                        <i class="fa-solid fa-check"></i> Approve
                      </button>
                    </form>
                    <form method="POST" action="">
                      <input type="hidden" name="expense_id" value="<?= $e['id'] ?>">
                      <button type="submit" name="action" value="reject" class="btn app-btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Reject
                      </button>
                    </form>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No expense records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleExpenseForm() {
  document.getElementById('addExpenseForm').classList.toggle('hidden');
}
</script>
</body>
</html>