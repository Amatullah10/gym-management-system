<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in as trainer
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$page = 'view-attendance'; // For active sidebar highlighting

// Get filter parameters
$date_filter   = isset($_GET['date'])   ? $_GET['date']   : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$sql = "SELECT 
    a.id,
    a.attendance_date,
    a.status,
    a.check_in_time,
    a.check_out_time,
    m.id as member_id,
    m.full_name,
    m.membership_type
FROM attendance a
INNER JOIN members m ON a.member_id = m.id
WHERE 1=1";

$params = [];
$types  = "";

if ($date_filter) {
    $sql    .= " AND a.attendance_date = ?";
    $params[] = $date_filter;
    $types   .= "s";
}

if ($status_filter) {
    $sql    .= " AND a.status = ?";
    $params[] = $status_filter;
    $types   .= "s";
}

$sql .= " ORDER BY a.attendance_date DESC, m.full_name ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$attendance_records = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $attendance_records[] = $row;
    }
}

$total_records = count($attendance_records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Attendance - Gym Management System</title>

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
    /* ── Modal Overlay ── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      z-index: 99999;
      align-items: center;
      justify-content: center;
    }

    .modal-overlay.active {
      display: flex;
    }

    /* ── Modal Card ── */
    .modal-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 36px 32px 32px;
      width: 540px;
      max-width: 95vw;
      position: relative;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
      animation: modalIn 0.22s ease;
    }

    @keyframes modalIn {
      from { opacity: 0; transform: translateY(-18px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0)    scale(1);     }
    }

    /* ── Close Button ── */
    .modal-close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #fce8e8;
      color: #c0392b;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      transition: background 0.2s, color 0.2s;
    }

    .modal-close-btn:hover {
      background: #c0392b;
      color: #fff;
    }

    /* ── Modal Header ── */
    .modal-header-title {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a1a;
      margin: 0 0 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid #f0f0f0;
    }

    /* ── Info Grid ── */
    .modal-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px 24px;
    }

    .modal-info-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .modal-info-item.full {
      grid-column: 1 / -1;
    }

    .modal-info-item .info-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #999;
    }

    .modal-info-item .info-value {
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
    }

    /* ── Status Badges (modal) ── */
    .badge-status {
      display: inline-block;
      padding: 5px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.3px;
    }

    .badge-status.present  { background: #2e7d32; color: #fff; }
    .badge-status.absent   { background: #d32f2f; color: #fff; }
    .badge-status.late     { background: #f57c00; color: #fff; }
    .badge-status.unmarked { background: #757575; color: #fff; }

    /* ── Status Badges (table) ── */
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .status-badge.present  { background: #2e7d32; color: #fff; }
    .status-badge.absent   { background: #d32f2f; color: #fff; }
    .status-badge.late     { background: #f57c00; color: #fff; }
    .status-badge.unmarked { background: #757575; color: #fff; }

    /* ── Eye / Action Button ── */
    .btn-action {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      transition: all 0.2s;
    }

    .btn-action.view {
      background: #f3e5f5;
      color: #9c27b0;
    }

    .btn-action.view:hover {
      background: #9c27b0;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(156, 39, 176, 0.3);
    }

    /* ── Date Input ── */
    .date-input {
      padding: 10px 15px 10px 42px;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      font-size: 14px;
      color: #333;
      background: #fff;
      cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s;
      min-width: 180px;
      height: 44px;
    }

    .date-input:focus {
      outline: none;
      border-color: var(--active-color, #941614);
      box-shadow: 0 0 0 3px rgba(148, 22, 20, 0.1);
    }

    /* Divider inside modal */
    .modal-divider {
      border: none;
      border-top: 1px solid #f0f0f0;
      margin: 20px 0 0;
    }
  </style>
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">View Attendance</h1>
          <p class="page-subtitle">Browse and search attendance records</p>
        </div>
        <button class="btn app-btn-primary" onclick="exportToCSV()">
          <i class="fa-solid fa-download"></i> Export CSV
        </button>
      </div>
    </div>

    <!-- Section Title -->
    <div style="margin-bottom: 20px;">
      <h2 style="font-size: 20px; font-weight: 600; color: #1a1a1a; margin: 0;">
        Attendance Records
      </h2>
    </div>

    <!-- Search & Filter Bar -->
    <div class="d-flex gap-3 mb-4 flex-wrap">
      <div style="position: relative;">
        <i class="fa-solid fa-calendar" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#999;pointer-events:none;"></i>
        <input type="date" id="dateFilter" class="date-input"
               value="<?= htmlspecialchars($date_filter) ?>">
      </div>

      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by name or member ID...">
      </div>

      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="Present" <?= $status_filter == 'Present' ? 'selected' : '' ?>>Present</option>
        <option value="Absent"  <?= $status_filter == 'Absent'  ? 'selected' : '' ?>>Absent</option>
        <option value="Late"    <?= $status_filter == 'Late'    ? 'selected' : '' ?>>Late</option>
      </select>
    </div>

    <!-- Attendance Table -->
    <div class="members-table-container">
      <table class="members-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Member ID</th>
            <th>Name</th>
            <th>Plan</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="attendanceTableBody">
          <?php if (count($attendance_records) > 0): ?>
            <?php foreach ($attendance_records as $record):
              $memberId      = 'MEM' . str_pad($record['member_id'], 3, '0', STR_PAD_LEFT);
              $formattedDate = date('M d, Y', strtotime($record['attendance_date']));

              // Determine display status
              $status = $record['status'];
              if ($status === 'Present' && $record['check_in_time']) {
                  $checkInHour = (int) date('H', strtotime($record['check_in_time']));
                  if ($checkInHour >= 9) {
                      $status = 'Late';
                  }
              }

              // Build clean JSON for the JS modal
              $modalData = json_encode([
                  'member_id'       => $record['member_id'],
                  'full_name'       => $record['full_name'],
                  'attendance_date' => $record['attendance_date'],
                  'membership_type' => $record['membership_type'],
                  'check_in_time'   => $record['check_in_time'],
                  'check_out_time'  => $record['check_out_time'],
                  'status'          => $status,
              ]);
            ?>
            <tr data-record='<?= htmlspecialchars($modalData, ENT_QUOTES) ?>'
                data-status="<?= strtolower($status) ?>">

              <td><span style="color:#666;"><?= $formattedDate ?></span></td>
              <td><span style="font-weight:600;color:#333;"><?= $memberId ?></span></td>
              <td><span style="font-weight:500;color:#1a1a1a;"><?= htmlspecialchars($record['full_name']) ?></span></td>
              <td><span style="color:#666;"><?= htmlspecialchars($record['membership_type']) ?></span></td>

              <td><span style="color:#666;">
                <?= $record['check_in_time']  ? date('h:i A', strtotime($record['check_in_time']))  : '—' ?>
              </span></td>

              <td><span style="color:#666;">
                <?= $record['check_out_time'] ? date('h:i A', strtotime($record['check_out_time'])) : '—' ?>
              </span></td>

              <td>
                <span class="status-badge <?= strtolower($status) ?>">
                  <?= htmlspecialchars($status) ?>
                </span>
              </td>

              <td>
                <button class="btn-action view" onclick="viewDetails(this)" title="View Details">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center"
                  style="padding:40px;color:#999;">
                No attendance records found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════
     ATTENDANCE DETAILS MODAL
══════════════════════════════════════ -->
<div class="modal-overlay" id="attendanceModal" onclick="handleOverlayClick(event)">
  <div class="modal-card" id="attendanceModalCard">

    <!-- Close -->
    <button class="modal-close-btn" onclick="closeModal()" title="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <!-- Title -->
    <h2 class="modal-header-title">
      <i class="fa-solid fa-clipboard-list" style="color:#941614;margin-right:8px;"></i>
      Attendance Details
    </h2>

    <!-- Info Grid -->
    <div class="modal-info-grid">

      <div class="modal-info-item">
        <span class="info-label">Member ID</span>
        <span class="info-value" id="modalMemberId">—</span>
      </div>

      <div class="modal-info-item">
        <span class="info-label">Name</span>
        <span class="info-value" id="modalName">—</span>
      </div>

      <div class="modal-info-item">
        <span class="info-label">Date</span>
        <span class="info-value" id="modalDate">—</span>
      </div>

      <div class="modal-info-item">
        <span class="info-label">Plan</span>
        <span class="info-value" id="modalPlan">—</span>
      </div>

      <div class="modal-info-item">
        <span class="info-label">Check In</span>
        <span class="info-value" id="modalCheckIn">—</span>
      </div>

      <div class="modal-info-item">
        <span class="info-label">Check Out</span>
        <span class="info-value" id="modalCheckOut">—</span>
      </div>

      <div class="modal-info-item full">
        <hr class="modal-divider">
        <span class="info-label" style="margin-top:4px;">Status</span>
        <div id="modalStatus"></div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ─── Helper: format time string "HH:MM:SS" → "hh:mm AM/PM" ─── */
function formatTime(timeStr) {
  if (!timeStr) return '—';
  const [h, m] = timeStr.split(':').map(Number);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hour = h % 12 || 12;
  return `${String(hour).padStart(2,'0')}:${String(m).padStart(2,'0')} ${ampm}`;
}

/* ─── Helper: format date "YYYY-MM-DD" → "Month DD, YYYY" ─── */
function formatDate(dateStr) {
  if (!dateStr) return '—';
  const [y, mo, d] = dateStr.split('-').map(Number);
  const months = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];
  return `${months[mo - 1]} ${String(d).padStart(2,'0')}, ${y}`;
}

/* ─── Open modal ─── */
function viewDetails(button) {
  const row    = button.closest('tr');
  const record = JSON.parse(row.getAttribute('data-record'));

  const memberId = 'MEM' + String(record.member_id).padStart(3, '0');

  document.getElementById('modalMemberId').textContent = memberId;
  document.getElementById('modalName').textContent     = record.full_name;
  document.getElementById('modalDate').textContent     = formatDate(record.attendance_date);
  document.getElementById('modalPlan').textContent     = record.membership_type;
  document.getElementById('modalCheckIn').textContent  = formatTime(record.check_in_time);
  document.getElementById('modalCheckOut').textContent = formatTime(record.check_out_time);

  const status = record.status || 'Unmarked';
  document.getElementById('modalStatus').innerHTML =
    `<span class="badge-status ${status.toLowerCase()}">${status}</span>`;

  document.getElementById('attendanceModal').classList.add('active');
}

/* ─── Close modal ─── */
function closeModal() {
  document.getElementById('attendanceModal').classList.remove('active');
}

/* ─── Close on overlay click (not on card click) ─── */
function handleOverlayClick(e) {
  if (e.target === document.getElementById('attendanceModal')) {
    closeModal();
  }
}

/* ─── Close on Escape key ─── */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});

/* ─── Date filter ─── */
document.getElementById('dateFilter').addEventListener('change', applyFilters);

/* ─── Status filter ─── */
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
  const date   = document.getElementById('dateFilter').value;
  const status = document.getElementById('statusFilter').value;
  let url = 'view-attendance.php?';
  if (date)   url += 'date='   + encodeURIComponent(date)   + '&';
  if (status) url += 'status=' + encodeURIComponent(status);
  window.location.href = url;
}

/* ─── Live search ─── */
document.getElementById('searchInput').addEventListener('keyup', function () {
  const term = this.value.toLowerCase();
  document.querySelectorAll('#attendanceTableBody tr[data-record]').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});

/* ─── Export CSV ─── */
function exportToCSV() {
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-record]');
  if (!rows.length) { alert('No records to export'); return; }

  let csv = 'Date,Member ID,Name,Plan,Check In,Check Out,Status\n';
  rows.forEach(row => {
    if (row.style.display === 'none') return;
    const cells = row.querySelectorAll('td');
    const data  = [];
    for (let i = 0; i < 7; i++) {
      let txt = cells[i].textContent.trim().replace(/"/g, '""');
      if (txt.includes(',')) txt = '"' + txt + '"';
      data.push(txt);
    }
    csv += data.join(',') + '\n';
  });

  const blob = new Blob([csv], { type: 'text/csv' });
  const url  = URL.createObjectURL(blob);
  const a    = Object.assign(document.createElement('a'), {
    href: url,
    download: 'attendance_records_' + new Date().toISOString().split('T')[0] + '.csv'
  });
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
</script>

</body>
</html>