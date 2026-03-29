<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','receptionist','accountant'])) {
    header("Location: ../index.php"); exit();
}

$member_id = (int)($_GET['member_id'] ?? 0);
if (!$member_id) { header("Location: payments.php"); exit(); }

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE id=$member_id"));
if (!$member) { header("Location: payments.php"); exit(); }

// Determine amount based on membership type
$amount = 799;
if (strpos($member['membership_type'], 'Premium') !== false) $amount = 1299;
elseif (strpos($member['membership_type'], 'Standard') !== false) $amount = 999;

// BUG FIX #3: Removed GET amount override — prevented amount tampering via URL

$plan = explode(' - ', $member['membership_type'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment — NextGen Fitness</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

    .gateway-wrap { display: flex; gap: 0; max-width: 850px; width: 100%; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }

    /* LEFT PANEL */
    .left-panel { background: #941614; color: white; padding: 40px 30px; width: 300px; flex-shrink: 0; display: flex; flex-direction: column; }
    .gym-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
    .gym-logo img { width: 50px; height: 50px; object-fit: contain; border-radius: 8px; }
    .gym-logo h2 { font-size: 16px; font-weight: 700; line-height: 1.3; }
    .amount-section { margin-bottom: 30px; }
    .amount-label { font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    .amount-value { font-size: 38px; font-weight: 700; }
    .amount-sub { font-size: 13px; opacity: 0.75; margin-top: 4px; }
    .member-info { background: rgba(255,255,255,0.15); border-radius: 10px; padding: 15px; margin-bottom: 20px; }
    .member-info .label { font-size: 11px; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; }
    .member-info .value { font-size: 14px; font-weight: 600; margin-top: 2px; }
    .secure-badge { margin-top: auto; display: flex; align-items: center; gap: 8px; font-size: 12px; opacity: 0.7; }

    /* RIGHT PANEL */
    .right-panel { flex: 1; padding: 40px; }
    .right-panel h3 { font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }
    .right-panel p { font-size: 13px; color: #999; margin-bottom: 25px; }

    /* Payment Method Tabs */
    .method-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
    .method-tab { flex: 1; padding: 14px 10px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; text-align: center; transition: all 0.2s; background: white; }
    .method-tab:hover { border-color: #941614; }
    .method-tab.active { border-color: #941614; background: #fff5f5; }
    .method-tab i { font-size: 20px; color: #941614; display: block; margin-bottom: 6px; }
    .method-tab span { font-size: 13px; font-weight: 600; color: #333; }

    /* Forms */
    .method-content { display: none; }
    .method-content.active { display: block; }
    label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; }
    input[type="text"], input[type="hidden"] { width: 100%; padding: 13px 15px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: 'Inter', sans-serif; outline: none; transition: border-color 0.2s; }
    input[type="text"]:focus { border-color: #941614; }
    .input-hint { font-size: 12px; color: #aaa; margin-top: 5px; }

    .pay-btn { width: 100%; padding: 15px; background: #941614; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 20px; font-family: 'Inter', sans-serif; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .pay-btn:hover { background: #b01917; }
    .pay-btn:disabled { background: #ccc; cursor: not-allowed; }

    .cash-info { background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 10px; padding: 18px; margin-bottom: 15px; }
    .cash-info i { color: #16a34a; font-size: 20px; margin-bottom: 8px; display: block; }
    .cash-info p { color: #166534; font-size: 14px; line-height: 1.6; }

    /* Processing Overlay */
    #processingOverlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; gap: 20px; }
    #processingOverlay.show { display: flex; }
    .spinner { width: 60px; height: 60px; border: 5px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .processing-text { color: white; font-size: 16px; font-weight: 600; }

    /* Success Screen */
    #successScreen { display: none; position: fixed; inset: 0; background: white; z-index: 9998; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 40px; }
    #successScreen.show { display: flex; }
    .success-icon { width: 90px; height: 90px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; animation: popIn 0.5s ease; }
    @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }
    .success-icon i { font-size: 45px; color: #16a34a; }
    #successScreen h2 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
    #successScreen p { font-size: 14px; color: #666; margin-bottom: 5px; }
    .txn-id { font-size: 13px; color: #999; background: #f5f5f5; padding: 8px 16px; border-radius: 8px; display: inline-block; margin: 10px 0 25px; font-family: monospace; }
    .redirect-text { font-size: 13px; color: #aaa; }

    @media (max-width: 640px) {
      .gateway-wrap { flex-direction: column; }
      .left-panel { width: 100%; padding: 25px; }
      .right-panel { padding: 25px; }
    }
  </style>
</head>
<body>

<div class="gateway-wrap">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="gym-logo">
      <img src="../assets/logo.png" alt="NextGen Fitness">
      <h2>NextGen<br>Fitness</h2>
    </div>

    <div class="amount-section">
      <div class="amount-label">Amount to Pay</div>
      <div class="amount-value">₹<?= number_format($amount) ?></div>
      <div class="amount-sub"><?= htmlspecialchars($plan) ?> Plan — Monthly</div>
    </div>

    <div class="member-info">
      <div class="label">Member</div>
      <div class="value"><?= htmlspecialchars($member['full_name']) ?></div>
    </div>
    <div class="member-info">
      <div class="label">Membership</div>
      <div class="value"><?= htmlspecialchars($member['membership_type']) ?></div>
    </div>

    <div class="secure-badge">
      <i class="fas fa-shield-halved"></i>
      <span>Secured by NextGen Fitness</span>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <h3>Choose Payment Method</h3>
    <p>Select how you'd like to complete this payment</p>

    <!-- Method Tabs -->
    <div class="method-tabs">
      <div class="method-tab active" onclick="switchMethod(event,'upi')">
        <i class="fas fa-mobile-screen-button"></i>
        <span>UPI</span>
      </div>
      <div class="method-tab" onclick="switchMethod(event,'cash')">
        <i class="fas fa-money-bill-wave"></i>
        <span>Cash</span>
      </div>
    </div>

    <!-- UPI Form -->
    <div class="method-content active" id="upi-content">
      <label>Enter UPI ID</label>
      <input type="text" id="upiId" placeholder="e.g. name@upi or 9876543210@ybl">
      <div class="input-hint"><i class="fas fa-info-circle"></i> Enter any UPI ID format — PhonePe, GPay, Paytm supported</div>
      <button class="pay-btn" onclick="processUPI()">
        <i class="fas fa-lock"></i> Pay ₹<?= number_format($amount) ?>
      </button>
    </div>

    <!-- Cash Form -->
    <div class="method-content" id="cash-content">
      <div class="cash-info">
        <i class="fas fa-circle-info"></i>
        <p>Collect <strong>₹<?= number_format($amount) ?></strong> in cash from <strong><?= htmlspecialchars($member['full_name']) ?></strong> and confirm below to record the payment.</p>
      </div>
      <button class="pay-btn" onclick="processCash()">
        <i class="fas fa-check-circle"></i> Confirm Cash Payment — ₹<?= number_format($amount) ?>
      </button>
    </div>

    <!-- Hidden form to submit to process-payment.php -->
    <form id="finalForm" method="POST" action="process-payment.php">
      <input type="hidden" name="member_id" value="<?= $member_id ?>">
      <input type="hidden" name="amount" value="<?= $amount ?>">
      <input type="hidden" name="service" value="Membership Fee">
      <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">
      <input type="hidden" name="payment_date" value="<?= date('Y-m-d') ?>">
      <input type="hidden" name="notes" id="finalNotes" value="">
      <input type="hidden" name="payment_method" id="finalMethod" value="">
      <input type="hidden" name="transaction_id" id="finalTxnId" value="">
    </form>
  </div>
</div>

<!-- Processing Overlay -->
<div id="processingOverlay">
  <div class="spinner"></div>
  <div class="processing-text">Processing Payment...</div>
</div>

<!-- Success Screen -->
<div id="successScreen">
  <div class="success-icon">
    <i class="fas fa-check"></i>
  </div>
  <h2>Payment Successful!</h2>
  <p>₹<?= number_format($amount) ?> paid by <strong><?= htmlspecialchars($member['full_name']) ?></strong></p>
  <div class="txn-id" id="txnDisplay"></div>
  <div class="redirect-text">Redirecting to receipt<span id="dots">...</span></div>
</div>

<script>
function switchMethod(e, method) { // BUG FIX #4
  document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.method-content').forEach(c => c.classList.remove('active'));
  e.currentTarget.classList.add('active');
  document.getElementById(method + '-content').classList.add('active');
}

function generateTxnId(prefix) {
  return prefix + Math.random().toString(36).substr(2, 8).toUpperCase() + Date.now().toString().slice(-4);
}

function processUPI() {
  const upiId = document.getElementById('upiId').value.trim();
  if (!upiId) { alert('Please enter your UPI ID'); return; }
  if (!upiId.includes('@')) { alert('Please enter a valid UPI ID (e.g. name@upi)'); return; }

  // Show processing
  document.getElementById('processingOverlay').classList.add('show');

  setTimeout(() => {
    document.getElementById('processingOverlay').classList.remove('show');
    const txnId = generateTxnId('UPI');
    showSuccess(txnId, 'UPI', upiId);
  }, 2500);
}

function processCash() {
  document.getElementById('processingOverlay').classList.add('show');
  setTimeout(() => {
    document.getElementById('processingOverlay').classList.remove('show');
    const txnId = generateTxnId('CASH');
    showSuccess(txnId, 'Cash', 'Cash collected at counter');
  }, 1000);
}

function showSuccess(txnId, method, notes) {
  document.getElementById('txnDisplay').textContent = 'Transaction ID: ' + txnId;
  document.getElementById('successScreen').classList.add('show');

  // Set form values
  document.getElementById('finalMethod').value = method;
  document.getElementById('finalTxnId').value = txnId;
  document.getElementById('finalNotes').value = notes;

  // Animate dots
  let dots = 0;
  setInterval(() => {
    dots = (dots + 1) % 4;
    document.getElementById('dots').textContent = '.'.repeat(dots);
  }, 500);

  // Submit form after 2.5 seconds
  setTimeout(() => {
    document.getElementById('finalForm').submit();
  }, 2500);
}
</script>
</body>
</html>