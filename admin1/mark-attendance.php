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

$page = 'mark-attendance'; // For active sidebar highlighting

// Handle AJAX save request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    header('Content-Type: application/json');
    
    $attendance_date = mysqli_real_escape_string($conn, $_POST['attendance_date']);
    $attendance_data = json_decode($_POST['attendance_data'], true);
    
    if (empty($attendance_data)) {
        echo json_encode(['success' => false, 'message' => 'No attendance data provided']);
        exit();
    }
    
    $saved_count = 0;
    $error_occurred = false;
    
    foreach ($attendance_data as $member_id => $status) {
        // Check if attendance already exists for this member and date
        $check_sql = "SELECT id FROM attendance WHERE member_id = ? AND attendance_date = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "is", $member_id, $attendance_date);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_sql = "UPDATE attendance SET status = ?, check_in_time = CURRENT_TIME WHERE member_id = ? AND attendance_date = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sis", $status, $member_id, $attendance_date);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $saved_count++;
            } else {
                $error_occurred = true;
            }
        } else {
            // Insert new record
            $insert_sql = "INSERT INTO attendance (member_id, attendance_date, status, check_in_time) VALUES (?, ?, ?, CURRENT_TIME)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "iss", $member_id, $attendance_date, $status);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $saved_count++;
            } else {
                $error_occurred = true;
            }
        }
    }
    
    if ($error_occurred) {
        echo json_encode([
            'success' => false, 
            'message' => 'Some records failed to save. Please try again.'
        ]);
    } else {
        $formatted_date = date('F jS, Y', strtotime($attendance_date));
        echo json_encode([
            'success' => true, 
            'message' => "Successfully saved attendance for {$saved_count} members on {$formatted_date}.",
            'count' => $saved_count,
            'date' => $formatted_date
        ]);
    }
    exit();
}

// Automatically add new registered members to today's attendance
$today = date('Y-m-d');
$check_new_members = "SELECT m.id FROM members m 
                      LEFT JOIN attendance a ON m.id = a.member_id AND a.attendance_date = '$today'
                      WHERE m.membership_status = 'active' 
                      AND a.id IS NULL 
                      AND DATE(m.created_at) = '$today'";
$new_members_result = mysqli_query($conn, $check_new_members);
if (mysqli_num_rows($new_members_result) > 0) {
    while ($row = mysqli_fetch_assoc($new_members_result)) {
        // Auto-add new members with unmarked status
        $insert = "INSERT IGNORE INTO attendance (member_id, attendance_date, status) VALUES ({$row['id']}, '$today', 'Unmarked')";
        mysqli_query($conn, $insert);
    }
}

// Get today's date or selected date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all members with their attendance status for selected date
$sql = "SELECT 
    m.id,
    m.full_name,
    m.membership_type,
    COALESCE(a.status, 'Unmarked') as attendance_status
FROM members m
LEFT JOIN attendance a ON m.id = a.member_id AND a.attendance_date = ?
WHERE m.membership_status = 'active'
ORDER BY m.full_name ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $selected_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$members = [];
$stats = [
    'total' => 0,
    'present' => 0,
    'absent' => 0,
    'unmarked' => 0
];

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
        $stats['total']++;
        
        if ($row['attendance_status'] == 'Present') {
            $stats['present']++;
        } elseif ($row['attendance_status'] == 'Absent') {
            $stats['absent']++;
        } else {
            $stats['unmarked']++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mark Attendance - Gym Management System</title>
  
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
    /* Toast Notification Styles */
    .toast-notification {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: white;
      border-radius: var(--radius-lg);
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      padding: 20px 24px;
      min-width: 350px;
      max-width: 450px;
      z-index: 9999;
      display: none;
      border-left: 4px solid var(--success-color);
      animation: slideIn 0.3s ease-out;
    }
    
    .toast-notification.show {
      display: block;
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
    
    .toast-notification.hiding {
      animation: slideOut 0.3s ease-out forwards;
    }
    
    .toast-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }
    
    .toast-title {
      font-size: 16px;
      font-weight: 700;
      color: #1a1a1a;
      margin: 0;
    }
    
    .toast-close {
      background: none;
      border: none;
      font-size: 20px;
      color: #999;
      cursor: pointer;
      padding: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.3s;
    }
    
    .toast-close:hover {
      color: #333;
    }
    
    .toast-message {
      font-size: 14px;
      color: #666;
      line-height: 1.5;
      margin: 0;
    }
    
    /* Attendance action buttons */
    .attendance-actions {
      display: flex;
      gap: 8px;
    }
    
    .btn-mark-present,
    .btn-mark-absent {
      width: 38px;
      height: 38px;
      border-radius: var(--radius-md);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 16px;
    }
    
    .btn-mark-present {
      background: #e8f5e9;
      color: var(--success-color);
    }
    
    .btn-mark-present:hover,
    .btn-mark-present.active {
      background: var(--success-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(46, 125, 50, 0.3);
    }
    
    .btn-mark-absent {
      background: #ffebee;
      color: var(--danger-color);
    }
    
    .btn-mark-absent:hover,
    .btn-mark-absent.active {
      background: var(--danger-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(211, 47, 47, 0.3);
    }
    
    .save-btn-container {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      padding: 20px 25px;
      background: white;
      border-top: 1px solid #f0f0f0;
    }
    
    /* Status badges - extending common.css */
    .status-badge.present {
      background: var(--success-color);
      color: white;
    }
    
    .status-badge.absent {
      background: var(--danger-color);
      color: white;
    }
    
    .status-badge.unmarked {
      background: #757575;
      color: white;
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
      <div>
        <h1 class="page-title">Mark Attendance</h1>
        <p class="page-subtitle">Record daily attendance for gym members</p>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-info">
          <h3 id="totalMembers"><?= $stats['total'] ?></h3>
          <p>Total Members</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fa-solid fa-check"></i>
        </div>
        <div class="stat-info">
          <h3 id="presentCount"><?= $stats['present'] ?></h3>
          <p>Present</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon danger">
          <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="stat-info">
          <h3 id="absentCount"><?= $stats['absent'] ?></h3>
          <p>Absent</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange">
          <i class="fa-solid fa-user-group"></i>
        </div>
        <div class="stat-info">
          <h3 id="unmarkedCount"><?= $stats['unmarked'] ?></h3>
          <p>Unmarked</p>
        </div>
      </div>
    </div>
    
    <!-- Attendance Section Title -->
    <div style="margin-bottom: 20px;">
      <h2 style="font-size: 20px; font-weight: 600; color: #1a1a1a; margin: 0;">
        Attendance for <?= date('l, F jS, Y', strtotime($selected_date)) ?>
      </h2>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="d-flex gap-3 mb-4">
      <div style="position: relative;">
        <i class="fa-solid fa-calendar" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        <input type="date" id="dateFilter" class="filter-select" value="<?= $selected_date ?>" style="padding-left: 45px;">
      </div>
      
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by member name or ID...">
      </div>
      
      <select class="filter-select" id="planFilter">
        <option value="">All Plans</option>
        <option>Basic - 799/month</option>
        <option>Standard - 999/month</option>
        <option>Premium - 1299/month</option>
      </select>
      
      <button class="btn app-btn-secondary" id="markAllPresent">
        <i class="fa-solid fa-check-double"></i> Mark All Present
      </button>
    </div>
    
    <!-- Attendance Table -->
    <div class="members-table-container">
      <table class="members-table">
        <thead>
          <tr>
            <th>Member ID</th>
            <th>Name</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="attendanceTableBody">
          <?php if (count($members) > 0): ?>
            <?php foreach ($members as $index => $member): 
              $memberId = 'MEM' . str_pad($member['id'], 3, '0', STR_PAD_LEFT);
            ?>
            <tr data-member-id="<?= $member['id'] ?>" data-plan="<?= htmlspecialchars($member['membership_type']) ?>">
              
              <!-- Member ID -->
              <td>
                <span style="font-weight: 600; color: #333;"><?= $memberId ?></span>
              </td>
              
              <!-- Name -->
              <td>
                <span style="color: #1a1a1a; font-weight: 500;"><?= htmlspecialchars($member['full_name']) ?></span>
              </td>
              
              <!-- Plan -->
              <td>
                <span style="color: #666; font-weight: 500;"><?= htmlspecialchars($member['membership_type']) ?></span>
              </td>
              
              <!-- Status -->
              <td>
                <span class="status-badge <?= strtolower($member['attendance_status']) ?>" data-status="<?= $member['attendance_status'] ?>">
                  <?= htmlspecialchars($member['attendance_status']) ?>
                </span>
              </td>
              
              <!-- Actions -->
              <td>
                <div class="attendance-actions">
                  <button class="btn-mark-present <?= $member['attendance_status'] == 'Present' ? 'active' : '' ?>" 
                          onclick="markAttendance(<?= $member['id'] ?>, 'Present')" 
                          title="Mark Present">
                    <i class="fa-solid fa-check"></i>
                  </button>
                  <button class="btn-mark-absent <?= $member['attendance_status'] == 'Absent' ? 'active' : '' ?>" 
                          onclick="markAttendance(<?= $member['id'] ?>, 'Absent')" 
                          title="Mark Absent">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center" style="padding: 40px; color: #999;">No active members found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      
      <!-- Save Button -->
      <div class="save-btn-container">
        <button class="btn app-btn-secondary" onclick="window.location.href='view-attendance.php'">
          <i class="fa-solid fa-eye"></i> View Records
        </button>
        <button class="btn app-btn-primary" id="saveAttendance" style="padding: 12px 32px;">
          <i class="fa-solid fa-floppy-disk"></i> Save Attendance
        </button>
      </div>
    </div>
    
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-notification" id="toastNotification">
  <div class="toast-header">
    <h3 class="toast-title" id="toastTitle">Attendance Saved</h3>
    <button class="toast-close" onclick="hideToast()">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
  <p class="toast-message" id="toastMessage"></p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Store attendance data
let attendanceData = {};

// Initialize attendance data from current page
document.addEventListener('DOMContentLoaded', function() {
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-member-id]');
  rows.forEach(row => {
    const memberId = row.getAttribute('data-member-id');
    const statusBadge = row.querySelector('.status-badge');
    const currentStatus = statusBadge.getAttribute('data-status');
    
    if (currentStatus !== 'Unmarked') {
      attendanceData[memberId] = currentStatus;
    }
  });
});

// Mark attendance function
function markAttendance(memberId, status) {
  const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
  const statusBadge = row.querySelector('.status-badge');
  const presentBtn = row.querySelector('.btn-mark-present');
  const absentBtn = row.querySelector('.btn-mark-absent');
  
  // Update UI
  statusBadge.textContent = status;
  statusBadge.setAttribute('data-status', status);
  statusBadge.className = 'status-badge ' + status.toLowerCase();
  
  // Update button states
  presentBtn.classList.remove('active');
  absentBtn.classList.remove('active');
  
  if (status === 'Present') {
    presentBtn.classList.add('active');
  } else {
    absentBtn.classList.add('active');
  }
  
  // Store in attendance data
  attendanceData[memberId] = status;
  
  // Update stats
  updateStats();
}

// Mark all present
document.getElementById('markAllPresent').addEventListener('click', function() {
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-member-id]');
  rows.forEach(row => {
    const memberId = row.getAttribute('data-member-id');
    markAttendance(memberId, 'Present');
  });
});

// Update statistics
function updateStats() {
  let present = 0, absent = 0, unmarked = 0;
  
  const badges = document.querySelectorAll('.status-badge');
  badges.forEach(badge => {
    const status = badge.getAttribute('data-status');
    if (status === 'Present') present++;
    else if (status === 'Absent') absent++;
    else unmarked++;
  });
  
  document.getElementById('presentCount').textContent = present;
  document.getElementById('absentCount').textContent = absent;
  document.getElementById('unmarkedCount').textContent = unmarked;
}

// Show toast notification
function showToast(title, message) {
  const toast = document.getElementById('toastNotification');
  document.getElementById('toastTitle').textContent = title;
  document.getElementById('toastMessage').textContent = message;
  
  toast.classList.remove('hiding');
  toast.classList.add('show');
  
  // Auto hide after 5 seconds
  setTimeout(() => {
    hideToast();
  }, 5000);
}

// Hide toast notification
function hideToast() {
  const toast = document.getElementById('toastNotification');
  toast.classList.add('hiding');
  
  setTimeout(() => {
    toast.classList.remove('show', 'hiding');
  }, 300);
}

// Save attendance
document.getElementById('saveAttendance').addEventListener('click', function() {
  const selectedDate = document.getElementById('dateFilter').value;
  
  if (Object.keys(attendanceData).length === 0) {
    showToast('No Changes', 'Please mark attendance for at least one member before saving.');
    return;
  }
  
  const saveBtn = this;
  const originalHTML = saveBtn.innerHTML;
  saveBtn.disabled = true;
  saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
  
  // Prepare data
  const formData = new FormData();
  formData.append('save_attendance', '1');
  formData.append('attendance_date', selectedDate);
  formData.append('attendance_data', JSON.stringify(attendanceData));
  
  // Send AJAX request
  fetch('mark-attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    saveBtn.disabled = false;
    saveBtn.innerHTML = originalHTML;
    
    if (data.success) {
      showToast('Attendance Saved', data.message);
      // Optionally clear the attendance data after successful save
      // attendanceData = {};
    } else {
      showToast('Error', data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    saveBtn.disabled = false;
    saveBtn.innerHTML = originalHTML;
    showToast('Error', 'An error occurred while saving attendance. Please try again.');
  });
});

// Date filter
document.getElementById('dateFilter').addEventListener('change', function() {
  const selectedDate = this.value;
  window.location.href = 'mark-attendance.php?date=' + selectedDate;
});

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
  const searchTerm = this.value.toLowerCase();
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-member-id]');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
  
  updateVisibleStats();
});

// Plan filter
document.getElementById('planFilter').addEventListener('change', function() {
  const planFilter = this.value;
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-member-id]');
  
  rows.forEach(row => {
    const plan = row.getAttribute('data-plan');
    row.style.display = !planFilter || plan === planFilter ? '' : 'none';
  });
  
  updateVisibleStats();
});

// Update stats for visible rows only
function updateVisibleStats() {
  let present = 0, absent = 0, unmarked = 0, total = 0;
  
  const rows = document.querySelectorAll('#attendanceTableBody tr[data-member-id]');
  rows.forEach(row => {
    if (row.style.display !== 'none') {
      total++;
      const badge = row.querySelector('.status-badge');
      const status = badge.getAttribute('data-status');
      if (status === 'Present') present++;
      else if (status === 'Absent') absent++;
      else unmarked++;
    }
  });
  
  document.getElementById('totalMembers').textContent = total;
  document.getElementById('presentCount').textContent = present;
  document.getElementById('absentCount').textContent = absent;
  document.getElementById('unmarkedCount').textContent = unmarked;
}
</script>

</body>
</html>