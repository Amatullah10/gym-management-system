<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'my-payments';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

$active_tab = $_GET['tab'] ?? 'due';

// Stats
$total_due     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Due'"))['t'];
$total_overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Overdue'"))['t'];
$paid_year     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE member_id=$member_id AND status='Paid' AND YEAR(payment_date)=YEAR(NOW())"))['t'];

$due_count      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE member_id=$member_id AND status='Due'"))['c'];
$overdue_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE member_id=$member_id AND status='Overdue'"))['c'];
$reminder_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payment_reminders WHERE member_id=$member_id"))['c'];

// Fetch records per tab
$due_q      = mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id AND status='Due' ORDER BY payment_date DESC");
$overdue_q  = mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id AND status='Overdue' ORDER BY payment_date DESC");
$history_q  = mysqli_query($conn, "SELECT * FROM payments WHERE member_id=$member_id ORDER BY payment_date DESC");
$reminder_q = mysqli_query($conn, "SELECT * FROM payment_reminders WHERE member_id=$member_id ORDER BY sent_date DESC");

$due_payments = $overdue_payments = $history = $reminders = [];
while($r = mysqli_fetch_assoc($due_q))     $due_payments[] = $r;
while($r = mysqli_fetch_assoc($overdue_q)) $overdue_payments[] = $r;
while($r = mysqli_fetch_assoc($history_q)) $history[] = $r;
while($r = mysqli_fetch_assoc($reminder_q)) $reminders[] = $r;

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Payments - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <div class="page-header">
    <h1 class="page-title">My Payments</h1>
    <p class="page-subtitle">NextGen Fitness — Customer Portal</p>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
      <div class="stat-info"><p>Total Due</p><h3>₹<?= number_format($total_due) ?></h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
      <div class="stat-info"><p>Overdue</p><h3>₹<?= number_format($total_overdue) ?></h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
      <div class="stat-info"><p>Paid This Year</p><h3>₹<?= number_format($paid_year) ?></h3></div>
    </div>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php
    $tabs = [
      'due'     => ['Due Payments', $due_count],
      'overdue' => ['Overdue', $overdue_count],
      'reminders'=> ['Reminders', $reminder_count],
      'history' => ['Payment History', null],
    ];
    foreach($tabs as $key => [$label, $count]):
      $is_active = $active_tab === $key;
    ?>
    <a href="?tab=<?= $key ?>" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
      background:<?= $is_active ? 'var(--active-color)' : 'white' ?>;
      color:<?= $is_active ? 'white' : '#555' ?>;
      border:1px solid <?= $is_active ? 'var(--active-color)' : '#e0e0e0' ?>;">
      <?= $label ?>
      <?php if($count !== null): ?>
      <span style="background:<?= $is_active?'rgba(255,255,255,0.3)':'var(--active-color)' ?>;color:white;font-size:11px;padding:1px 7px;border-radius:20px;"><?= $count ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Due Payments Tab -->
  <?php if($active_tab === 'due'): ?>
  <div class="table-container">
    <div class="table-header"><h3>Due Payments</h3><p style="color:#999;font-size:13px;margin:4px 0 0;">Payments pending — please pay before the payment date</p></div>
    <?php if(empty($due_payments)): ?>
    <div style="padding:40px;text-align:center;color:#aaa;"><i class="fas fa-check-circle" style="font-size:36px;color:#2e7d32;margin-bottom:10px;display:block;"></i>No due payments! You are all clear.</div>
    <?php else: foreach($due_payments as $p): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 25px;border-bottom:1px solid #f5f5f5;background:#fffdf0;">
      <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;background:#fff3cd;border-radius:8px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-receipt" style="color:#f57c00;"></i>
        </div>
        <div>
          <div style="font-weight:600;"><?= htmlspecialchars($p['service']) ?> — <?= date('F Y', strtotime($p['payment_date'])) ?></div>
          <div style="font-size:12px;color:#999;">Pay by: <?= date('M d, Y', strtotime($p['payment_date'])) ?></div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:15px;">
        <span style="font-weight:700;font-size:16px;">₹<?= number_format($p['amount']) ?></span>
        <span style="background:#f57c00;color:white;font-size:12px;padding:4px 12px;border-radius:20px;">Due</span>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Overdue Tab -->
  <?php elseif($active_tab === 'overdue'): ?>
  <div class="table-container">
    <div class="table-header"><h3>Overdue Payments</h3></div>
    <?php if(empty($overdue_payments)): ?>
    <div style="padding:40px;text-align:center;color:#aaa;"><i class="fas fa-check-circle" style="font-size:36px;color:#2e7d32;margin-bottom:10px;display:block;"></i>No overdue payments!</div>
    <?php else: foreach($overdue_payments as $p):
      $days = max(0, round((strtotime(date('Y-m-d')) - strtotime($p['payment_date']))/86400));
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 25px;border-bottom:1px solid #f5f5f5;background:#fff5f5;">
      <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;background:#ffebee;border-radius:8px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-triangle-exclamation" style="color:#d32f2f;"></i>
        </div>
        <div>
          <div style="font-weight:600;"><?= htmlspecialchars($p['service']) ?></div>
          <div style="font-size:12px;color:#d32f2f;"><?= $days ?> days overdue</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:15px;">
        <span style="font-weight:700;font-size:16px;">₹<?= number_format($p['amount']) ?></span>
        <span style="background:#d32f2f;color:white;font-size:12px;padding:4px 12px;border-radius:20px;">Overdue</span>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Reminders Tab -->
  <?php elseif($active_tab === 'reminders'): ?>
  <div class="table-container">
    <div class="table-header"><h3>Payment Reminders</h3></div>
    <!-- Membership expiry reminder -->
    <?php if($member):
      $days_expiry = (int)round((strtotime($member['end_date']) - strtotime(date('Y-m-d')))/86400);
    ?>
    <div style="padding:18px 25px;border-bottom:1px solid #f5f5f5;background:<?= $days_expiry<=7?'#fff5f5':($days_expiry<=30?'#fffdf0':'#f5fff5') ?>;">
      <div style="font-weight:600;margin-bottom:4px;"><i class="fas fa-id-card" style="color:var(--active-color);margin-right:8px;"></i>Membership Expiry</div>
      <div style="font-size:13px;color:#666;"><?= htmlspecialchars($member['membership_type']) ?> — Expires <?= date('d M Y', strtotime($member['end_date'])) ?></div>
      <div style="font-size:12px;margin-top:4px;">
        <span style="color:<?= $days_expiry<=7?'#d32f2f':($days_expiry<=30?'#f57c00':'#2e7d32') ?>;font-weight:600;"><?= max(0,$days_expiry) ?> days remaining</span>
      </div>
    </div>
    <?php endif; ?>
    <?php if(empty($reminders)): ?>
    <div style="padding:30px;text-align:center;color:#aaa;">No reminders sent yet.</div>
    <?php else: foreach($reminders as $r): ?>
    <div style="padding:15px 25px;border-bottom:1px solid #f5f5f5;">
      <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($r['message']) ?></div>
      <div style="font-size:12px;color:#aaa;margin-top:3px;"><?= date('d M Y H:i', strtotime($r['sent_date'])) ?></div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Payment History Tab -->
  <?php elseif($active_tab === 'history'): ?>
  <div class="table-container">
    <div class="table-header"><h3>Payment History</h3></div>
    <table class="modern-table">
      <thead><tr><th>#</th><th>Service</th><th>Amount</th><th>Plan</th><th>Method</th><th>Date</th><th>Status</th><th>Receipt</th></tr></thead>
      <tbody>
        <?php if(empty($history)): ?>
        <tr><td colspan="7" style="text-align:center;padding:30px;color:#aaa;">No payment history.</td></tr>
        <?php else: foreach($history as $i=>$p):
          $badge = $p['status']==='Paid'?'active':($p['status']==='Overdue'?'expired':'pending');
        ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($p['service']) ?></strong></td>
          <td>₹<?= number_format($p['amount']) ?></td>
          <td><?= $p['plan'] ?></td>
          <td><?= $p['payment_method'] ?></td>
          <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
          <td><span class="status-badge <?= $badge ?>"><?= $p['status'] ?></span></td>
          <td>
            <?php if($p['status'] === 'Paid'): ?>
            <button onclick="printReceipt(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($member), ENT_QUOTES) ?>)"
              style="background:var(--active-color);color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
              <i class="fas fa-print"></i> Print
            </button>
            <?php else: ?>
            <span style="color:#ccc;font-size:12px;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div></div>

<!-- Receipt Modal -->
<div id="receiptModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div id="receiptContent" style="background:#fff;border-radius:16px;padding:40px;width:100%;max-width:520px;margin:20px;max-height:90vh;overflow-y:auto;">
    <!-- Filled by JS -->
  </div>
</div>

<style>
@media print {
  body > * { display: none !important; }
  #printArea { display: block !important; }
}
#printArea { display: none; }
</style>
<div id="printArea"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printReceipt(p, member) {
  const planMonths = { 'Monthly': 1, 'Quarterly': 3, 'Yearly': 12 };
  const months = planMonths[p.plan] || 1;
  const payDate = new Date(p.payment_date);
  const validDate = new Date(payDate);
  validDate.setMonth(validDate.getMonth() + months);
  const validStr = validDate.toLocaleDateString('en-IN', { day:'2-digit', month:'long', year:'numeric' });
  const payStr   = payDate.toLocaleDateString('en-IN', { day:'2-digit', month:'long', year:'numeric' });
  const invoice  = 'GMS_' + String(parseInt(p.id) * 9 + 1000000).padStart(7,'0');

  const html = `
    <div style="text-align:center;margin-bottom:20px;">
      <div style="width:60px;height:60px;border-radius:50%;border:2px solid #941614;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
        <i class="fas fa-dumbbell" style="font-size:24px;color:#941614;"></i>
      </div>
      <h2 style="margin:0;font-size:20px;font-weight:700;">Payment Receipt</h2>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin-bottom:16px;">
      <div>
        <div>Invoice #${invoice}</div>
        <div>NextGen Fitness GYM</div>
        <div>123 Main Street, Mumbai</div>
      </div>
      <div style="text-align:right;">Date: ${payStr}</div>
    </div>
    <hr style="border:none;border-top:1px solid #eee;margin:12px 0;">
    <div style="text-align:center;margin-bottom:16px;">
      <div style="font-size:15px;font-weight:700;">Member: ${member.full_name}</div>
      <div style="color:#941614;font-size:13px;margin-top:3px;">Paid On: ${payStr}</div>
    </div>
    <hr style="border:none;border-top:1px solid #eee;margin:12px 0;">
    <table style="width:100%;font-size:13px;border-collapse:collapse;">
      <thead>
        <tr style="color:#999;">
          <th style="text-align:left;padding:6px 0;">Service Taken</th>
          <th style="text-align:right;padding:6px 0;">Valid Upto</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:8px 0;border-top:1px solid #f0f0f0;">${p.service || 'Fitness'}</td>
          <td style="padding:8px 0;border-top:1px solid #f0f0f0;text-align:right;">${p.plan} <strong>(${validStr})</strong></td>
        </tr>
        <tr>
          <td style="padding:8px 0;border-top:1px solid #f0f0f0;">Charge Per Month</td>
          <td style="padding:8px 0;border-top:1px solid #f0f0f0;text-align:right;">₹${parseInt(p.amount).toLocaleString('en-IN')}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td style="padding:12px 0;border-top:2px solid #222;font-weight:700;">Total Amount</td>
          <td style="padding:12px 0;border-top:2px solid #222;text-align:right;font-weight:700;">₹${parseInt(p.amount).toLocaleString('en-IN')}</td>
        </tr>
      </tfoot>
    </table>
    <p style="text-align:center;color:#aaa;font-size:12px;font-style:italic;margin-top:16px;">
      Thank you for your payment. We appreciate your promptness!
    </p>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:20px;">
      <button onclick="doPrint()" style="background:#941614;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
        <i class="fas fa-print"></i> Print
      </button>
      <button onclick="closeReceipt()" style="background:#f0f0f0;color:#555;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
        Close
      </button>
    </div>`;

  document.getElementById('receiptContent').innerHTML = html;
  document.getElementById('receiptModal').style.display = 'flex';
  // Store for print
  document.getElementById('printArea').innerHTML = '<div style="padding:30px;max-width:500px;margin:0 auto;">' + html + '</div>';
}

function doPrint() {
  window.print();
}

function closeReceipt() {
  document.getElementById('receiptModal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('receiptModal').addEventListener('click', function(e) {
  if (e.target === this) closeReceipt();
});
</script>
</body></html>