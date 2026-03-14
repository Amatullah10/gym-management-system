<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php"); exit();
}
$page = 'schedule';
$trainer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Get members for dropdown
$members_q = mysqli_query($conn, "SELECT id, full_name FROM members WHERE membership_status='Active' ORDER BY full_name ASC");
$members = [];
while($r = mysqli_fetch_assoc($members_q)) $members[] = $r;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id   = (int)$_POST['member_id'];
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $type        = mysqli_real_escape_string($conn, $_POST['session_type']);
    $date        = mysqli_real_escape_string($conn, $_POST['session_date']);
    $time        = mysqli_real_escape_string($conn, $_POST['start_time']);
    $duration    = (int)$_POST['duration'];
    $location    = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
    $notes       = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

    if (!$member_id || !$title || !$date || !$time) {
        $error = 'Please fill in all required fields.';
    } else {
        $ins = mysqli_query($conn, "INSERT INTO trainer_sessions (member_id, trainer_email, title, session_type, session_date, start_time, duration, location, notes)
            VALUES ($member_id, '$trainer_email', '$title', '$type', '$date', '$time', $duration, '$location', '$notes')");
        if ($ins) {
            header("Location: schedule.php?date=$date"); exit();
        } else {
            $error = 'Failed to create session: ' . mysqli_error($conn);
        }
    }
}

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <a href="schedule.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Schedule
    </a>

    <div class="page-header">
      <h1 class="page-title">New Session</h1>
      <p class="page-subtitle">Schedule a new training session with a member</p>
    </div>

    <?php if($error): ?>
      <div class="app-alert app-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container" style="max-width:800px;">
      <form method="POST">

        <div class="section">
          <h3>Session Details</h3>
          <div class="form-row">
            <div>
              <label>Member *</label>
              <select name="member_id" required>
                <option value="">Select a member</option>
                <?php foreach($members as $m): ?>
                <option value="<?= $m['id'] ?>" <?= (isset($_GET['member_id']) && $_GET['member_id']==$m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Session Type *</label>
              <select name="session_type" required>
                <option>Training Session</option>
                <option>Consultation</option>
                <option>Assessment</option>
              </select>
            </div>
          </div>
          <div>
            <label>Session Title *</label>
            <input type="text" name="title" placeholder="e.g. Upper Body Strength" required>
          </div>
        </div>

        <div class="section">
          <h3>Date &amp; Time</h3>
          <div class="form-row">
            <div>
              <label>Date *</label>
              <input type="date" name="session_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
              <label>Start Time *</label>
              <input type="time" name="start_time" required>
            </div>
            <div>
              <label>Duration</label>
              <select name="duration">
                <option value="30">30 minutes</option>
                <option value="45">45 minutes</option>
                <option value="60" selected>60 minutes</option>
                <option value="90">90 minutes</option>
                <option value="120">120 minutes</option>
              </select>
            </div>
          </div>
        </div>

        <div class="section">
          <h3>Additional Info</h3>
          <div>
            <label>Location</label>
            <input type="text" name="location" placeholder="e.g. Gym Floor A, Studio B">
          </div>
          <div style="margin-top:15px;">
            <label>Notes</label>
            <textarea name="notes" placeholder="Add any notes for this session..."></textarea>
          </div>
        </div>

        <div style="display:flex;gap:15px;">
          <a href="schedule.php" class="btn app-btn-secondary" style="flex:1;text-align:center;">Cancel</a>
          <button type="submit" class="btn app-btn-primary" style="flex:1;"><i class="fas fa-save"></i> Create Session</button>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>