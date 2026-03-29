<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'accountant') { header("Location: ../index.php"); exit(); }

$page       = 'salary-payments';
$page_title = 'Payslip - Gym Management';

if (empty($_GET['id'])) { header("Location: salary-payments.php"); exit(); }
$sid = (int)$_GET['id'];

$res = mysqli_query($conn, "
    SELECT sp.*, s.full_name, s.role AS staff_role, s.email, s.phone
    FROM salary_payments sp
    JOIN staff s ON sp.staff_id = s.id
    WHERE sp.id = $sid AND sp.status = 'Paid'
");
$slip = mysqli_fetch_assoc($res);
if (!$slip) { header("Location: salary-payments.php?error=Payslip+not+found"); exit(); }

$invoice    = 'PAY_' . str_pad($sid * 7 + 200000, 7, '0', STR_PAD_LEFT);
$deductions = ($slip['base_salary'] ?? 0) - ($slip['net_salary'] ?? 0);
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<style>
  @media print {
    .sidebar, .no-print { display: none !important; }
    .main-wrapper { margin: 0 !important; }
    body { background: white !important; }
  }
  .payslip-wrap { max-width: 720px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
  .payslip-header { background: #941614; color: white; padding: 30px 36px; display: flex; justify-content: space-between; align-items: flex-start; }
  .payslip-header h2 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .payslip-header p { font-size: 13px; opacity: 0.8; }
  .invoice-num { text-align: right; }
  .invoice-num h3 { font-size: 16px; font-weight: 700; }
  .payslip-body { padding: 30px 36px; }
  .payslip-section { margin-bottom: 24px; }
  .payslip-section h4 { font-size: 11px; font-weight: 700; color: #941614; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .info-item label { font-size: 11px; color: #888; display: block; margin-bottom: 2px; }
  .info-item span { font-size: 14px; font-weight: 600; color: #1a1a1a; }
  .salary-table { width: 100%; border-collapse: collapse; }
  .salary-table th { background: #f9fafb; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #666; padding: 10px 14px; text-align: left; }
  .salary-table td { padding: 12px 14px; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
  .salary-table tfoot td { font-weight: 700; font-size: 15px; background: #f9fafb; padding: 14px; }
  .net-salary { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-top: 16px; }
  .net-salary span { font-size: 14px; color: #166534; font-weight: 600; }
  .net-salary strong { font-size: 22px; color: #166534; font-weight: 700; }
  .payslip-footer { padding: 20px 36px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
  .payslip-footer p { font-size: 12px; color: #888; }
  .stamp { text-align: right; }
  .stamp .authorized { font-size: 12px; color: #888; margin-top: 4px; }
</style>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header flex justify-between align-center no-print">
      <div>
        <h1 class="page-title">Payslip</h1>
        <p class="page-subtitle">Salary slip — <?= htmlspecialchars($slip['full_name']) ?></p>
      </div>
      <div class="action-buttons">
        <a href="salary-payments.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn app-btn-primary"><i class="fa-solid fa-print"></i> Print</button>
      </div>
    </div>

    <div class="payslip-wrap">
      <div class="payslip-header">
        <div>
          <h2>NextGen Fitness</h2>
          <p>123 Main Street, Mumbai</p>
          <p>nextgenfitness1407@gmail.com</p>
          <p style="margin-top:12px;font-size:14px;font-weight:700;opacity:1;">PAYSLIP</p>
        </div>
        <div class="invoice-num">
          <h3><?= $invoice ?></h3>
          <p>Period: <?= $slip['period_month'] ? date('F Y', strtotime($slip['period_month'])) : '—' ?></p>
          <p>Paid On: <?= $slip['payment_date'] ? date('d M Y', strtotime($slip['payment_date'])) : '—' ?></p>
        </div>
      </div>

      <div class="payslip-body">
        <div class="payslip-section">
          <h4>Employee Details</h4>
          <div class="info-grid">
            <div class="info-item"><label>Name</label><span><?= htmlspecialchars($slip['full_name']) ?></span></div>
            <div class="info-item"><label>Role</label><span><?= ucfirst(htmlspecialchars($slip['staff_role'])) ?></span></div>
            <div class="info-item"><label>Email</label><span><?= htmlspecialchars($slip['email']) ?></span></div>
            <div class="info-item"><label>Phone</label><span><?= htmlspecialchars($slip['phone'] ?? '—') ?></span></div>
          </div>
        </div>

        <div class="payslip-section">
          <h4>Earnings &amp; Deductions</h4>
          <table class="salary-table">
            <thead>
              <tr><th>Description</th><th>Type</th><th style="text-align:right;">Amount</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Basic Salary</td>
                <td><span class="status-badge active" style="font-size:11px;">Earning</span></td>
                <td style="text-align:right;font-weight:700;">₹<?= number_format($slip['base_salary'] ?? 0, 2) ?></td>
              </tr>
              <?php if ($deductions > 0): ?>
              <tr>
                <td>Deductions</td>
                <td><span class="status-badge expired" style="font-size:11px;">Deduction</span></td>
                <td style="text-align:right;font-weight:700;color:#dc2626;">– ₹<?= number_format($deductions, 2) ?></td>
              </tr>
              <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2">Gross Salary</td>
                <td style="text-align:right;">₹<?= number_format($slip['base_salary'] ?? 0, 2) ?></td>
              </tr>
            </tfoot>
          </table>
          <div class="net-salary">
            <span>Net Salary (Take Home)</span>
            <strong>₹<?= number_format($slip['net_salary'] ?? 0, 2) ?></strong>
          </div>
        </div>

        <?php if (!empty($slip['notes'])): ?>
        <div class="payslip-section">
          <h4>Notes</h4>
          <p style="font-size:14px;color:#555;"><?= htmlspecialchars($slip['notes']) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <div class="payslip-footer">
        <p>This is a computer-generated payslip. No signature required.</p>
        <div class="stamp">
          <p style="font-weight:700;font-size:13px;">NextGen Fitness</p>
          <p class="authorized">Authorized Signatory</p>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>