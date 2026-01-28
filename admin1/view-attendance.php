<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in and has proper role
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Allow only admin, trainer, and receptionist to access this page
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../index.php");
    exit();
}

$page = 'view-attendance'; // For active sidebar highlighting

// Get filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
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
$types = "";

if ($date_filter) {
    $sql .= " AND a.attendance_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if ($status_filter) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
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
    while($row = mysqli_fetch_assoc($result)) {
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
    /* Modal Styles */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    
    .modal-overlay.active {
      display: flex;
    }
    
    .modal-content {
      background: white;
      border-radius: 12px;
      padding: 30px;
      max-width: 500px;
      width: 90%;
      position: relative;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    
    .modal-close {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #fee;
      color: var(--active-color);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      transition: all 0.3s;
    }
    
    .modal-close:hover {
      background: var(--active-color);
      color: white;
    }
    
    .modal-title {
      font-size: 22px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 25px;
    }
    
    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    .modal-field {
      margin-bottom: 0;
    }
    
    .modal-field label {
      font-size: 13px;
      color: #999;
      margin-bottom: 5px;
      display: block;
      font-weight: 500;
    }
    
    .modal-field .value {
      font-size: 16px;
      color: #1a1a1a;
      font-weight: 600;
    }
    
    .modal-field.full-width {
      grid-column: 1 / -1;
    }
    
    /* Status badge in modal */
    .modal-status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
    }
    
    .modal-status-badge.present {
      background: #2e7d32;
      color: white;
    }
    
    .modal-status-badge.absent {
      background: #d32f2f;
      color: white;
    }
    
    .modal-status-badge.late {
      background: #f57c00;
      color: white;
    }
    
    /* Eye button styling */
    .btn-action.view {
      background: #f3e5f5;
      color: #9c27b0;
    }
    
    .btn-action.view:hover {
      background: #9c27b0;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(156, 39, 176, 0.3);
    }
    
    /* Status badges for table */
    .status-badge.present {
      background: #2e7d32;
      color: white;
    }
    
    .status-badge.absent {
      background: #d32f2f;
      color: white;
    }
    
    .status-badge.late {
      background: #f57c00;
      color: white;
    }
    
    /* Date input styling */
    .date-input {
      padding: 12px 15px;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      font-size: 14px;
      color: #333;
      background: white;
      cursor: pointer;
      transition: all 0.3s;
      min-width: 180px;
    }
    
    .date-input:focus {
      outline: none;
      border-color: var(--active-color);
      box-shadow: 0 0 0 3px rgba(148, 22, 20, 0.1);
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
    
    <!-- Attendance Records Section -->
    <div style="margin-bottom: 20px;">
      <h2 style="font-size: 20px; font-weight: 600; color: #1a1a1a; margin: 0;">
        Attendance Records
      </h2>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="d-flex gap-3 mb-4">
      <div style="position: relative;">
        <i class="fa-solid fa-calendar" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        <input type="date" id="dateFilter" class="date-input" placeholder="Filter by date" style="padding-left: 45px;" value="<?= htmlspecialchars($date_filter) ?>">
      </div>
      
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by name or member ID...">
      </div>
      
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="Present" <?= $status_filter == 'Present' ? 'selected' : '' ?>>Present</option>
        <option value="Absent" <?= $status_filter == 'Absent' ? 'selected' : '' ?>>Absent</option>
        <option value="Late" <?= $status_filter == 'Late' ? 'selected' : '' ?>>Late</option>
      </select>
    </div>
    
    <!-- Attendance Records Table -->
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
              $memberId = 'MEM' . str_pad($record['member_id'], 3, '0', STR_PAD_LEFT);
              $formattedDate = date('M d, Y', strtotime($record['attendance_date']));
              
              // Determine if late (check-in after 8:00 AM for example)
              $status = $record['status'];
              if ($status == 'Present' && $record['check_in_time']) {
                $checkInHour = (int)date('H', strtotime($record['check_in_time']));
                if ($checkInHour >= 9) {
                  $status = 'Late';
                }
              }
            ?>
            <tr data-record='<?= json_encode($record) ?>' data-status="<?= strtolower($status) ?>">
              
              <!-- Date -->
              <td>
                <span style="color: #666;"><?= $formattedDate ?></span>
              </td>
              
              <!-- Member ID -->
              <td>
                <span style="font-weight: 600; color: #333;"><?= $memberId ?></span>
              </td>
              
              <!-- Name -->
              <td>
                <span style="color: #1a1a1a; font-weight: 500;"><?= htmlspecialchars($record['full_name']) ?></span>
              </td>
              
              <!-- Plan -->
              <td>
                <span style="color: #666;"><?= htmlspecialchars($record['membership_type']) ?></span>
              </td>
              
              <!-- Check In -->
              <td>
                <span style="color: #666;">
                  <?= $record['check_in_time'] ? date('h:i A', strtotime($record['check_in_time'])) : '-' ?>
                </span>
              </td>
              
              <!-- Check Out -->
              <td>
                <span style="color: #666;">
                  <?= $record['check_out_time'] ? date('h:i A', strtotime($record['check_out_time'])) : '-' ?>
                </span>
              </td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= strtolower($status) ?>">
                  <?= htmlspecialchars($status) ?>
                </span>
              </td>
              
              <!-- Actions -->
              <td>
                <button class="btn-action view" onclick="viewDetails(this)" title="View Details">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center" style="padding: 40px; color: #999;">No attendance records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>

<!-- Modal for Attendance Details -->
<div class="modal-overlay" id="attendanceModal">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal()">
      <i class="fa-solid fa-xmark"></i>
    </button>
    
    <h2 class="modal-title">Attendance Details</h2>
    
    <div class="modal-grid">
      <div class="modal-field">
        <label>Member ID</label>
        <div class="value" id="modalMemberId"></div>
      </div>
      
      <div class="modal-field">
        <label>Name</label>
        <div class="value" id="modalName"></div>
      </div>
      
      <div class="modal-field">
        <label>Date</label>
        <div class="value" id="modalDate"></div>
      </div>
      
      <div class="modal-field">
        <label>Plan</label>
        <div class="value" id="modalPlan"></div>
      </div>
      
      <div class="modal-field">
        <label>Check In</label>
        <div class="value" id="modalCheckIn"></div>
      </div>
      
      <div class="modal-field">
        <label>Check Out</label>
        <div class="value" id="modalCheckOut"></div>
      </div>
      
      <div class="modal-field full-width">
        <label>Status</label>
        <div id="modalStatus"></div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// View attendance details in modal
function viewDetails(button) {
  const row = button.closest('tr');
  const record = JSON.parse(row.getAttribute('data-record'));
  
  const memberId = 'MEM' + String(record.member_id).padStart(3, '0');
  const formattedDate = new Date(record.attendance_date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
  
  // Determine status (Late if check-in >= 9 AM)
  let status = record.status;
  if (status === 'Present' && record.check_in_time) {
    const checkInHour = parseInt(record.check_in_time.split(':')[0]);
    if (checkInHour >= 9) {
      status = 'Late';
    }
  }
  
  // Populate modal
  document.getElementById('modalMemberId').textContent = memberId;
  document.getElementById('modalName').textContent = record.full_name;
  document.getElementById('modalDate').textContent = formattedDate;
  document.getElementById('modalPlan').textContent = record.membership_type;
  document.getElementById('modalCheckIn').textContent = record.check_in_time 
    ? new Date('2000-01-01 ' + record.check_in_time).toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    : '-';
  document.getElementById('modalCheckOut').textContent = record.check_out_time 
    ? new Date('2000-01-01 ' + record.check_out_time).toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    : '-';
  
  document.getElementById('modalStatus').innerHTML = 
    `<span class="modal-status-badge ${status.toLowerCase()}">${status}</span>`;
  
  // Show modal
  document.getElementById('attendanceModal').classList.add('active');
}

// Close modal
function closeModal() {
  document.getElementById('attendanceModal').classList.remove('active');
}

// Close modal on overlay click
document.getElementById('attendanceModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});

// Date filter
document.getElementById('dateFilter').addEventListener('change', function() {
  applyFilters();
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
  applyFilters();
});

function applyFilters() {
  const date = document.getElementById('dateFilter').value;
  const status = document.getElementById('statusFilter').value;
  
  let url = 'view-attendance.php?';
  if (date) url += 'date=' + date + '&';
  if (status) url += 'status=' + status;
  
  window.location.href = url;
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
  const searchTerm = this.value.toLowerCase();
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-record]');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});

// Export to CSV
function exportToCSV() {
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-record]');
  
  if (rows.length === 0) {
    alert('No records to export');
    return;
  }
  
  let csv = 'Date,Member ID,Name,Plan,Check In,Check Out,Status\n';
  
  rows.forEach(row => {
    if (row.style.display !== 'none') {
      const cells = row.querySelectorAll('td');
      const rowData = [];
      
      // Get first 7 cells (exclude Actions column)
      for (let i = 0; i < 7; i++) {
        let text = cells[i].textContent.trim();
        // Escape quotes and wrap in quotes if contains comma
        text = text.replace(/"/g, '""');
        if (text.includes(',')) {
          text = '"' + text + '"';
        }
        rowData.push(text);
      }
      
      csv += rowData.join(',') + '\n';
    }
  });
  
  // Create download link
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'attendance_records_' + new Date().toISOString().split('T')[0] + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
}
</script>

</body>
</html>