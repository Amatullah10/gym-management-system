<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SESSION['role'] != 'customer') {
    header("Location: ../index.php");
    exit();
}

$page = 'due-payments';

$email = $_SESSION['email'];
$member_query = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_query);
$member_id = $member['id'] ?? 0;

$records = [];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE member_id = '$member_id' AND status = 'due' ORDER BY due_date ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
}

$total_due = array_sum(array_column($records, 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Due Payments - FitnessPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .main-wrapper { margin-top: 0 !important; padding-top: 0 !important; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">Due Payments</h1>
      <p class="page-subtitle">Payments that are pending</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="stat-info"><h3><?= count($records) ?></h3><p>Due Payments</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="stat-info"><h3>₹<?= number_format($total_due, 2) ?></h3><p>Total Due Amount</p></div>
      </div>
    </div>

    <!-- Due Payments Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>Due Payments</h3>
      </div>
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Payment Method</th>
            <th>Notes</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($records)): ?>
            <?php foreach ($records as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><strong>₹<?= number_format($r['amount'], 2) ?></strong></td>
                <td><?= $r['due_date'] ?></td>
                <td><?= $r['payment_method'] ?: '-' ?></td>
                <td><?= $r['notes'] ?: '-' ?></td>
                <td><span class="status-badge pending">DUE</span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center" style="color:#aaa; padding:30px;">🎉 No due payments! You are all clear.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>