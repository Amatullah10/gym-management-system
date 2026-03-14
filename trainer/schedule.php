<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php"); exit();
}
$page = 'schedule';
$trainer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Week navigation
$week_offset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$week_start  = date('Y-m-d', strtotime("monday this week +{$week_offset} weeks"));
$week_end    = date('Y-m-d', strtotime("$week_start +6 days"));
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Sessions for the week
$week_q = mysqli_query($conn, "SELECT ts.*, m.full_name FROM trainer_sessions ts
    JOIN members m ON m.id=ts.member_id
    WHERE ts.trainer_email='$trainer_email' AND ts.session_date BETWEEN '$week_start' AND '$week_end'
    ORDER BY ts.session_date ASC, ts.start_time ASC");
$week_sessions = [];
while($r = mysqli_fetch_assoc($week_q)) {
    $week_sessions[$r['session_date']][] = $r;
}

// Sessions for selected date
$day_sessions = $week_sessions[$selected_date] ?? [];

// Upcoming sessions
$upcoming_q = mysqli_query($conn, "SELECT ts.*, m.full_name FROM trainer_sessions ts
    JOIN members m ON m.id=ts.member_id
    WHERE ts.trainer_email='$trainer_email' AND ts.session_date >= CURDATE() AND ts.status='Upcoming'
    ORDER BY ts.session_date ASC, ts.start_time ASC LIMIT 10");
$upcoming = [];
while($r = mysqli_fetch_assoc($upcoming_q)) $upcoming[] = $r;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $sid = (int)$_POST['session_id'];
    $st  = mysqli_real_escape_string($conn, $_POST['new_status']);
    mysqli_query($conn, "UPDATE trainer_sessions SET status='$st' WHERE id=$sid AND trainer_email='$trainer_email'");
    header("Location: schedule.php?week=$week_offset&date=$selected_date"); exit();
}

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h1 class="page-title">Schedule</h1>
        <p class="page-subtitle">Manage your training sessions and appointments</p>
      </div>
      <a href="new-session.php" class="btn app-btn-primary"><i class="fas fa-plus"></i> New Session</a>
    </div>

    <!-- Week Calendar -->
    <div class="table-container" style="padding:20px;margin-bottom:20px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <a href="?week=<?= $week_offset-1 ?>&date=<?= $week_start ?>" style="color:#555;text-decoration:none;padding:6px 10px;border:1px solid #eee;border-radius:8px;"><i class="fas fa-chevron-left"></i></a>
        <span style="font-weight:600;font-size:15px;"><?= date('F d', strtotime($week_start)) ?> – <?= date('F d, Y', strtotime($week_end)) ?></span>
        <a href="?week=<?= $week_offset+1 ?>&date=<?= $week_end ?>" style="color:#555;text-decoration:none;padding:6px 10px;border:1px solid #eee;border-radius:8px;"><i class="fas fa-chevron-right"></i></a>
      </div>
      <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;">
        <?php
        $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        for($i=0; $i<7; $i++):
          $day_date = date('Y-m-d', strtotime("$week_start +$i days"));
          $is_today    = $day_date === date('Y-m-d');
          $is_selected = $day_date === $selected_date;
          $has_sessions = !empty($week_sessions[$day_date]);
        ?>
        <a href="?week=<?= $week_offset ?>&date=<?= $day_date ?>" style="text-decoration:none;text-align:center;padding:12px 8px;border-radius:10px;border:1px solid <?= $is_selected ? 'var(--active-color)' : '#f0f0f0' ?>;background:<?= $is_selected ? 'var(--active-color)' : ($is_today ? '#fff5f5' : 'white') ?>;">
          <div style="font-size:12px;font-weight:500;color:<?= $is_selected ? 'white' : '#999' ?>;"><?= $days[$i] ?></div>
          <div style="font-size:18px;font-weight:700;color:<?= $is_selected ? 'white' : '#333' ?>;margin:4px 0;"><?= date('d', strtotime($day_date)) ?></div>
          <?php if($has_sessions): ?>
          <div style="display:flex;justify-content:center;gap:3px;">
            <?php foreach(array_slice($week_sessions[$day_date],0,3) as $_): ?>
            <div style="width:6px;height:6px;border-radius:50%;background:<?= $is_selected ? 'white' : 'var(--active-color)' ?>;"></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </a>
        <?php endfor; ?>
      </div>
    </div>

    <div class="row g-4">
      <!-- Day Sessions -->
      <div class="col-md-6">
        <div class="table-container">
          <div class="table-header">
            <h3><?= date('l, F d', strtotime($selected_date)) ?></h3>
          </div>
          <?php if(empty($day_sessions)): ?>
            <div style="padding:40px;text-align:center;color:#aaa;font-size:14px;">No sessions scheduled for this day</div>
          <?php else: ?>
          <table class="modern-table">
            <tbody>
              <?php foreach($day_sessions as $s): ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($s['full_name'],0,1)) ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($s['title']) ?></span>
                      <span class="meta"><?= htmlspecialchars($s['full_name']) ?> &bull; <?= date('H:i', strtotime($s['start_time'])) ?> &bull; <?= $s['duration'] ?>min</span>
                    </div>
                  </div>
                </td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="update_status" value="1">
                    <select name="new_status" onchange="this.form.submit()" style="font-size:12px;padding:4px 8px;border:1px solid #eee;border-radius:6px;">
                      <?php foreach(['Upcoming','Completed','Cancelled'] as $st): ?>
                      <option <?= $s['status']===$st?'selected':'' ?>><?= $st ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Upcoming Sessions -->
      <div class="col-md-6">
        <div class="table-container">
          <div class="table-header"><h3>Upcoming Sessions</h3></div>
          <?php if(empty($upcoming)): ?>
            <div style="padding:40px;text-align:center;color:#aaa;font-size:14px;">No upcoming sessions</div>
          <?php else: ?>
          <table class="modern-table">
            <tbody>
              <?php foreach($upcoming as $s): ?>
              <tr>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= strtoupper(substr($s['full_name'],0,1)) ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($s['title']) ?></span>
                      <span class="meta"><?= htmlspecialchars($s['full_name']) ?> &bull; <?= date('d M', strtotime($s['session_date'])) ?> at <?= date('H:i', strtotime($s['start_time'])) ?></span>
                    </div>
                  </div>
                </td>
                <td><span class="app-badge app-badge-success" style="font-size:11px;white-space:nowrap;"><?= $s['session_type'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>