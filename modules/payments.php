<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only admin, receptionist, and accountant
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist' && $_SESSION['role'] != 'accountant') {
    header("Location: ../index.php");
    exit();
}

$page = 'payments'; // For active sidebar highlighting

// Calculate statistics
// This Month
$this_month_query = "SELECT COALESCE(SUM(amount), 0) as total 
                     FROM payments 
                     WHERE MONTH(payment_date) = MONTH(CURDATE()) 
                     AND YEAR(payment_date) = YEAR(CURDATE())
                     AND status = 'Paid'";
$this_month_result = mysqli_query($conn, $this_month_query);
$this_month = mysqli_fetch_assoc($this_month_result)['total'];

// Total Collected
$collected_query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'Paid'";
$collected_result = mysqli_query($conn, $collected_query);
$collected = mysqli_fetch_assoc($collected_result)['total'];

// Pending = Due + Overdue payments
$pending_query = "SELECT COALESCE(SUM(amount), 0) as pending FROM payments WHERE status IN ('Due', 'Overdue')";
$pending_result = mysqli_query($conn, $pending_query);
$pending = mysqli_fetch_assoc($pending_result)['pending'];

// Fetch all members with payment details
$sql = "SELECT 
    m.id,
    m.full_name,
    m.email,
    m.phone,
    m.membership_type,
    m.membership_status,
    COALESCE(
        (SELECT payment_date FROM payments WHERE member_id = m.id ORDER BY payment_date DESC LIMIT 1),
        'Never'
    ) as last_payment_date,
    COALESCE(
        (SELECT SUM(amount) FROM payments WHERE member_id = m.id AND status = 'Paid'),
        0
    ) as total_paid,
    CASE 
        WHEN m.membership_type LIKE '%Basic%' THEN 799
        WHEN m.membership_type LIKE '%Standard%' THEN 999
        WHEN m.membership_type LIKE '%Premium%' THEN 1299
        ELSE 0
    END as membership_fee,
    COALESCE(
        (SELECT service FROM payments WHERE member_id = m.id ORDER BY payment_date DESC LIMIT 1),
        'Fitness'
    ) as service_type,
    COALESCE(
        (SELECT plan FROM payments WHERE member_id = m.id ORDER BY payment_date DESC LIMIT 1),
        'Monthly'
    ) as plan_type
FROM members m
ORDER BY m.created_at DESC";

$result = mysqli_query($conn, $sql);
$members = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

// Success/Error messages
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments - Gym Management</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    /* ── Toast Notifications ── */
    .app-toast {
      position: fixed;
      top: 80px;           /* just below the 70px fixed header */
      right: 24px;
      z-index: 9999;       /* above everything including the header */
      min-width: 320px;
      max-width: 480px;
      padding: 14px 18px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 6px 24px rgba(0,0,0,0.15);
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideIn 0.35s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(60px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    .app-toast-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .app-toast-error   { background: #fee2e2; color: #7f1d1d; border-left: 4px solid #ef4444; }
    .app-toast-warning { background: #fef9c3; color: #713f12; border-left: 4px solid #f59e0b; }

    /* ── Compact Table Overrides ── */
    .members-table-container { overflow-x: auto; }
    .members-table { width: 100%; table-layout: fixed; font-size: 13px; border-collapse: collapse; }
    .members-table thead th {
      padding: 10px 8px;
      font-size: 11px;
      letter-spacing: 0.4px;
      white-space: nowrap;
      background: #f8f9fa;
    }
    .members-table tbody td { padding: 10px 8px; vertical-align: middle; }

    /* Column widths — lock them so nothing overflows */
    .members-table th:nth-child(1), .members-table td:nth-child(1) { width: 36px; text-align:center; }
    .members-table th:nth-child(2), .members-table td:nth-child(2) { width: 210px; }
    .members-table th:nth-child(3), .members-table td:nth-child(3) { width: 110px; }
    .members-table th:nth-child(4), .members-table td:nth-child(4) { width: 90px; }
    .members-table th:nth-child(5), .members-table td:nth-child(5) { width: 90px; }
    .members-table th:nth-child(6), .members-table td:nth-child(6) { width: 80px; }
    .members-table th:nth-child(7), .members-table td:nth-child(7) { width: 60px; text-align:center; }
    .members-table th:nth-child(8), .members-table td:nth-child(8) { width: 140px; }
    .members-table th:nth-child(9), .members-table td:nth-child(9) { width: 100px; }

    /* Smaller member avatar */
    .members-table .member-avatar { width: 34px; height: 34px; font-size: 13px; flex-shrink: 0; }
    .members-table .member-cell   { display: flex; align-items: center; gap: 8px; }
    .members-table .member-info .name   { font-size: 13px; font-weight: 600; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 155px; }
    .members-table .member-info .joined { font-size: 11px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 155px; }

    /* Compact action buttons */
    .members-table .btn { padding: 6px 10px !important; font-size: 12px !important; white-space: nowrap; }
  </style>
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    
    <!-- Toast Notifications (fixed position, always visible above header) -->
    <?php if ($success_message): ?>
      <div class="app-toast app-toast-success" id="appToast">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;float:right;font-size:16px;cursor:pointer;color:inherit;margin-left:12px;">&#x2715;</button>
      </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
      <div class="app-toast app-toast-error" id="appToast">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_message) ?>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;float:right;font-size:16px;cursor:pointer;color:inherit;margin-left:12px;">&#x2715;</button>
      </div>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title"><i class="fa-solid fa-credit-card"></i> Payments</h1>
        <p class="page-subtitle">Manage member payments and subscriptions</p>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fa-solid fa-indian-rupee-sign"></i>
        </div>
        <div class="stat-info">
          <h3>₹<?= number_format($this_month, 0) ?></h3>
          <p>This Month</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon active">
          <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <div class="stat-info">
          <h3>₹<?= number_format($collected, 0) ?></h3>
          <p>Collected</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-info">
          <h3>₹<?= number_format($pending, 0) ?></h3>
          <p>Pending</p>
        </div>
      </div>
    </div>
    
    <!-- Search Box -->
    <div class="search-box mb-4" style="max-width: 500px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="searchInput" placeholder="Search member by name...">
    </div>
    
    <!-- Member Payments Table -->
    <div class="members-table-container">
      <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Member Payments</h3>
      </div>
      
      <table class="members-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Last Payment Date</th>
            <th>Amount</th>
            <th>Chosen Service</th>
            <th>Plan</th>
            <th>History</th>
            <th>Action</th>
            <th>Remind</th>
          </tr>
        </thead>
        <tbody id="paymentsTableBody">
          <?php if (count($members) > 0): ?>
            <?php 
            $counter = 1;
            foreach ($members as $member): 
              $initial = strtoupper(substr($member['full_name'], 0, 1));
            ?>
            <tr>
              <!-- Serial Number -->
              <td><?= $counter++ ?></td>
              
              <!-- Member -->
              <td>
                <div class="member-cell">
                  <div class="member-avatar"><?= $initial ?></div>
                  <div class="member-info">
                    <span class="name"><?= htmlspecialchars($member['full_name']) ?></span>
                    <span class="joined"><?= htmlspecialchars($member['email']) ?></span>
                  </div>
                </div>
              </td>
              
              <!-- Last Payment Date -->
              <td>
                <span style="color: #666; font-size: 14px;">
                  <?= $member['last_payment_date'] == 'Never' ? 'Never' : date('Y-m-d', strtotime($member['last_payment_date'])) ?>
                </span>
              </td>
              
              <!-- Amount -->
              <td>
                <span style="font-weight: 600; color: #333;">
                  ₹<?= number_format($member['total_paid'], 0) ?>
                </span>
              </td>
              
              <!-- Chosen Service -->
              <td>
                <span style="color: #666; font-size: 14px;">
                  <?= htmlspecialchars($member['service_type']) ?>
                </span>
              </td>
              
              <!-- Plan -->
              <td>
                <span style="color: #666; font-size: 14px;">
                  <?= htmlspecialchars($member['plan_type']) ?>
                </span>
              </td>
              
              <!-- History - View Payment History -->
              <td>
                <a href="payment-history.php?id=<?= $member['id'] ?>" class="btn-action view" title="View Payment History">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                </a>
              </td>
              
              <!-- Action - Make Payment -->
              <td>
                <a href="payment-gateway.php?member_id=<?= $member['id'] ?>&amount=<?= $member['membership_fee'] ?>" class="btn" style="padding: 8px 16px; font-size: 14px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                  <i class="fa-solid fa-indian-rupee-sign"></i> Make Payment
                </a>
              </td>
              
              <!-- Remind - Alert -->
              <td>
                <button class="btn app-btn-secondary" style="padding: 8px 16px; font-size: 14px; background: var(--danger-color); border-color: var(--danger-color);" onclick="sendReminder(<?= $member['id'] ?>)">
                  <i class="fa-solid fa-bell"></i> Alert
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No members found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Make Payment Modal -->
<div class="modal fade" id="makePaymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Make Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="paymentForm" method="POST" action="process-payment.php">
        <div class="modal-body" id="paymentFormContent">
          <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn app-btn-primary">Submit Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Make Payment
function makePayment(memberId) {
  // Load member data
  fetch('get-payment-data.php?id=' + memberId)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const member = data.member;
        document.getElementById('paymentFormContent').innerHTML = `
          <input type="hidden" name="member_id" value="${member.id}">
          
          <div class="section">
            <h3>Member Information</h3>
            <div class="member-cell mb-3">
              <div class="member-avatar">${member.full_name.charAt(0).toUpperCase()}</div>
              <div class="member-info">
                <span class="name">${member.full_name}</span>
                <span class="joined">${member.email}</span>
              </div>
            </div>
            
            <div class="form-row">
              <div>
                <label>Membership Type</label>
                <input type="text" value="${member.membership_type}" readonly style="background: #f5f5f5;">
              </div>
              <div>
                <label>Membership Fee</label>
                <input type="text" value="₹${member.membership_fee}" readonly style="background: #f5f5f5;">
              </div>
            </div>
          </div>
          
          <div class="section">
            <h3>Payment Details</h3>
            
            <div class="form-row">
              <div>
                <label>Amount *</label>
                <input type="number" name="amount" step="0.01" required>
              </div>
              <div>
                <label>Payment Date *</label>
                <input type="date" name="payment_date" value="${new Date().toISOString().split('T')[0]}" required>
              </div>
            </div>
            
            <div class="form-row">
              <div>
                <label>Payment Method *</label>
                <select name="payment_method" required>
                  <option value="">Select Method</option>
                  <option>Cash</option>
                  <option>Card</option>
                  <option>UPI</option>
                  <option>Cheque</option>
                  <option>Online</option>
                </select>
              </div>
              <div>
                <label>Receipt Number</label>
                <input type="text" name="receipt_number">
              </div>
            </div>
            
            <div class="form-row">
              <div>
                <label>Service Type *</label>
                <select name="service_type" required>
                  <option>Fitness</option>
                  <option>Fitness + Cardio</option>
                  <option>Yoga</option>
                  <option>Zumba</option>
                  <option>CrossFit</option>
                </select>
              </div>
              <div>
                <label>Plan Type *</label>
                <select name="plan_type" required>
                  <option>Monthly</option>
                  <option>Quarterly</option>
                  <option>Half-Yearly</option>
                  <option>Yearly</option>
                </select>
              </div>
            </div>
            
            <div class="form-row">
              <div>
                <label>Payment For Month</label>
                <input type="text" name="payment_for_month" placeholder="e.g., March 2026">
              </div>
            </div>
            
            <label>Notes</label>
            <textarea name="notes" rows="3"></textarea>
          </div>
        `;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('makePaymentModal'));
        modal.show();
      }
    });
}

// Send Reminder
function sendReminder(memberId) {
  if (confirm('Send payment reminder to this member?')) {
    fetch('send-reminder.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'member_id=' + memberId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
      } else {
        alert('Error: ' + data.message);
      }
    });
  }
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
  const searchTerm = this.value.toLowerCase();
  const rows = document.querySelectorAll('#paymentsTableBody tr');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});

// Auto-hide toast notifications after 5 seconds
setTimeout(function() {
  document.querySelectorAll('.app-toast').forEach(toast => {
    toast.style.transition = 'opacity 0.5s, transform 0.5s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(60px)';
    setTimeout(() => toast.remove(), 500);
  });
}, 5000);
</script>

</body>
</html>