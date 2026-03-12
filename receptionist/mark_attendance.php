<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'mark-attendance';
$success = '';
$error = '';
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $member_id = $_POST['member_id'];
    $action    = $_POST['action'];

    if ($action == 'checkin') {
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE member_id = '$member_id' AND attendance_date = '$today'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This member has already checked in today!";
        } else {
            $now    = date('H:i:s');
            $insert = mysqli_query($conn, "INSERT INTO attendance (member_id, attendance_date, status, check_in_time) VALUES ('$member_id', '$today', 'Present', '$now')");
            if ($insert) { $success = "Check-in recorded successfully!"; }
            else { $error = "Failed to record check-in. " . mysqli_error($conn); }
        }
    } elseif ($action == 'checkout') {
        $att = mysqli_query($conn, "SELECT id FROM attendance WHERE member_id = '$member_id' AND attendance_date = '$today' AND check_out_time IS NULL AND status = 'Present'");
        if (mysqli_num_rows($att) == 0) {
            $error = "No active check-in found for this member today!";
        } else {
            $att_row = mysqli_fetch_assoc($att);
            $now     = date('H:i:s');
            $update  = mysqli_query($conn, "UPDATE attendance SET check_out_time = '$now' WHERE id = '{$att_row['id']}'");
            if ($update) { $success = "Check-out recorded successfully!"; }
            else { $error = "Failed to record check-out. " . mysqli_error($conn); }
        }
    }
}

$members = [];
$res = mysqli_query($conn, "SELECT id, full_name, email FROM members ORDER BY full_name ASC");
while ($row = mysqli_fetch_assoc($res)) { $members[] = $row; }

$today_list = [];
$res2 = mysqli_query($conn, "SELECT a.*, m.full_name, m.email FROM attendance a JOIN members m ON a.member_id = m.id WHERE a.attendance_date = '$today' ORDER BY a.check_in_time DESC");
while ($row = mysqli_fetch_assoc($res2)) { $today_list[] = $row; }

$total_checkedin  = count($today_list);
$total_checkedout = count(array_filter($today_list, fn($a) => $a['check_out_time']));
$total_present    = $total_checkedin - $total_checkedout;
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Mark Attendance</h1>
        <p class="page-subtitle">Record member check-in and check-out — <?= date('d M Y') ?></p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-right-to-bracket"></i></div>
        <div class="stat-info"><h3><?= $total_checkedin ?></h3><p>Total Check-ins</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-person-walking"></i></div>
        <div class="stat-info"><h3><?= $total_present ?></h3><p>Still Inside</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-right-from-bracket"></i></div>
        <div class="stat-info"><h3><?= $total_checkedout ?></h3><p>Checked Out</p></div>
      </div>
    </div>

    <!-- Check In / Out Form -->
    <div class="form-container mb-20">
      <h3 style="font-size:18px; font-weight:600; margin-bottom:20px; margin-top:0;">Record Attendance</h3>
      <form method="POST">
        <div class="form-row">
          <div>
            <label>Select Member</label>
            <select name="member_id" required>
              <option value="">-- Select Member --</option>
              <?php foreach ($members as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="flex gap-3 mt-10">
          <button type="submit" name="action" value="checkin" class="btn app-btn-primary">
            <i class="fa-solid fa-right-to-bracket"></i> Check In
          </button>
          <button type="submit" name="action" value="checkout" class="btn app-btn-secondary">
            <i class="fa-solid fa-right-from-bracket"></i> Check Out
          </button>
        </div>
      </form>
    </div>

    <!-- Today's Attendance -->
    <div class="members-table-container">
      <div class="table-header flex justify-between align-center">
        <h3>
          Today's Attendance
          <span class="app-badge app-badge-warning" style="margin-left:8px;"><?= $total_checkedin ?> members</span>
        </h3>
        <a href="view_attendance.php" style="color:var(--active-color); text-decoration:none; font-size:14px;">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>

      <?php if (!empty($today_list)): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:15px; padding:20px;">
          <?php foreach ($today_list as $a):
            $check_in_time  = isset($a['check_in_time']) ? $a['check_in_time'] : null;
            $check_out_time = isset($a['check_out_time']) && $a['check_out_time'] ? $a['check_out_time'] : null;
            $duration  = '-';
            if ($check_out_time && $check_in_time) {
              $check_in_dt  = strtotime($a['attendance_date'] . ' ' . $check_in_time);
              $check_out_dt = strtotime($a['attendance_date'] . ' ' . $check_out_time);
              $diff     = $check_out_dt - $check_in_dt;
              $duration = floor($diff/3600).'h '.floor(($diff%3600)/60).'m';
            }
            $initial = strtoupper(substr($a['full_name'], 0, 1));
            $is_out  = !empty($check_out_time) ? true : false;
          ?>
          <div class="stat-card" style="flex-direction:column; align-items:stretch; gap:12px; border-left:4px solid <?= $is_out ? 'var(--success-color)' : 'var(--warning-color)' ?>;">

            <!-- Member Info -->
            <div class="member-cell">
              <div class="member-avatar"><?= $initial ?></div>
              <div class="member-info" style="flex:1;">
                <span class="name"><?= htmlspecialchars($a['full_name']) ?></span>
                <span class="joined"><?= htmlspecialchars($a['email']) ?></span>
              </div>
              <span class="status-badge <?= $is_out ? 'active' : 'pending' ?>" style="font-size:11px; white-space:nowrap;">
                <?= $is_out ? 'Completed' : 'Present' ?>
              </span>
            </div>

            <!-- Time Info -->
            <div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr; gap:8px; margin:0;">
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-right-to-bracket" style="color:var(--success-color);"></i> In</p>
                <p style="font-size:13px; font-weight:600; color:#1a1a1a; margin:0;">
                  <?= $check_in_time ? date('h:i A', strtotime($check_in_time)) : '—' ?>
                </p>
              </div>
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-right-from-bracket" style="color:var(--danger-color);"></i> Out</p>
                <p style="font-size:13px; font-weight:600; color:<?= $is_out ? '#1a1a1a' : '#ccc' ?>; margin:0;">
                  <?= $is_out ? date('h:i A', strtotime($check_out_time)) : '—' ?>
                </p>
              </div>
              <div class="app-card" style="padding:8px; text-align:center; box-shadow:none; background:#f8f9fa; border-radius:var(--radius-md);">
                <p style="font-size:10px; color:#aaa; margin-bottom:3px;"><i class="fa-solid fa-clock" style="color:#2196f3;"></i> Time</p>
                <p style="font-size:13px; font-weight:600; color:#1a1a1a; margin:0;"><?= $duration ?></p>
              </div>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="text-center" style="padding:50px 20px;">
          <i class="fa-solid fa-calendar-xmark" style="font-size:45px; margin-bottom:15px; display:block; color:#ddd;"></i>
          <p style="font-size:15px; font-weight:500; color:#aaa;">No attendance recorded today yet.</p>
          <p style="font-size:13px; color:#ccc;">Select a member above and click Check In to get started.</p>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>