<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    header("Location: ../index.php"); exit();
}
$page = 'assigned-members';

$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$member_id) { header("Location: assigned-member.php"); exit(); }

$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT m.*, wp.goal, wp.current_plan, wp.progress, wp.status as workout_status
    FROM members m LEFT JOIN workout_plans wp ON m.id=wp.member_id WHERE m.id=$member_id"));
if (!$member) { header("Location: assigned-member.php"); exit(); }

// Progress history
$progress_q = mysqli_query($conn, "SELECT * FROM progress_reports WHERE member_id=$member_id ORDER BY date DESC");
$progress_history = [];
while($r = mysqli_fetch_assoc($progress_q)) $progress_history[] = $r;

// Latest progress for stats
$latest_progress = $progress_history[0] ?? null;

// Attendance
$att_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id"))['c'];
$att_present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE member_id=$member_id AND status='Present'"))['c'];
$att_pct = $att_total > 0 ? round(($att_present / $att_total) * 100) : 0;

// Sessions / workouts
$sessions_q = mysqli_query($conn, "SELECT * FROM trainer_sessions WHERE member_id=$member_id ORDER BY session_date DESC LIMIT 10");
$sessions = [];
while($r = mysqli_fetch_assoc($sessions_q)) $sessions[] = $r;

// Handle add progress form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_progress'])) {
    $weight = (float)$_POST['weight'];
    $height = (float)$_POST['height'];
    $bmi    = ($height > 0) ? round($weight / (($height/100) * ($height/100)), 1) : (float)$_POST['bmi'];
    $notes  = mysqli_real_escape_string($conn, $_POST['notes']);
    $date   = mysqli_real_escape_string($conn, $_POST['date']);
    $by     = mysqli_real_escape_string($conn, $_SESSION['email']);
    mysqli_query($conn, "INSERT INTO progress_reports (member_id, date, weight, height, bmi, notes, recorded_by)
        VALUES ($member_id, '$date', $weight, $height, $bmi, '$notes', '$by')");
    header("Location: view-member-details.php?id=$member_id&tab=progress&success=1"); exit();
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <?php if(isset($_GET['success'])): ?>
      <div class="app-alert app-alert-success">Progress entry saved successfully!</div>
    <?php endif; ?>

    <!-- Back -->
    <a href="assigned-member.php" style="display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:14px;margin-bottom:20px;">
      <i class="fas fa-arrow-left"></i> Back to Members
    </a>

    <!-- Member Header Card -->
    <div class="table-container" style="padding:20px;margin-bottom:20px;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:15px;">
        <div class="member-cell">
          <div class="member-avatar" style="width:60px;height:60px;font-size:22px;"><?= strtoupper(substr($member['full_name'],0,1)) ?></div>
          <div class="member-info">
            <span class="name" style="font-size:20px;"><?= htmlspecialchars($member['full_name']) ?></span>
            <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:4px;">
              <span style="font-size:13px;color:#666;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($member['email']) ?></span>
              <span style="font-size:13px;color:#666;"><i class="fas fa-phone"></i> <?= htmlspecialchars($member['phone']) ?></span>
              <span style="font-size:13px;color:#666;"><i class="fas fa-calendar"></i> Joined <?= date('d/m/Y', strtotime($member['created_at'])) ?></span>
            </div>
            <div style="margin-top:6px;font-size:13px;color:#666;">
              <i class="fas fa-bullseye" style="color:var(--active-color);"></i>
              Goal: <?= htmlspecialchars($member['goal'] ?? 'Not Set') ?> &bull; <?= htmlspecialchars($member['current_plan'] ?? 'No Plan') ?>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:10px;">
          <a href="edit-workout-plan.php?id=<?= $member['id'] ?>" class="btn app-btn-secondary" style="padding:8px 16px;font-size:13px;">Edit Plan</a>
          <a href="schedule.php?member_id=<?= $member['id'] ?>" class="btn app-btn-primary" style="padding:8px 16px;font-size:13px;">Schedule Session</a>
        </div>
      </div>

      <!-- Quick Stats -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:20px;">
        <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;">
          <div style="font-size:13px;color:#999;margin-bottom:4px;"><i class="fas fa-weight" style="color:var(--active-color);"></i> Weight</div>
          <div style="font-size:20px;font-weight:700;"><?= $latest_progress ? $latest_progress['weight'].' kg' : '—' ?></div>
        </div>
        <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;">
          <div style="font-size:13px;color:#999;margin-bottom:4px;"><i class="fas fa-ruler-vertical" style="color:var(--active-color);"></i> Height</div>
          <div style="font-size:20px;font-weight:700;"><?= $latest_progress ? $latest_progress['height'].' cm' : '—' ?></div>
        </div>
        <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;">
          <div style="font-size:13px;color:#999;margin-bottom:4px;"><i class="fas fa-percent" style="color:var(--active-color);"></i> BMI</div>
          <div style="font-size:20px;font-weight:700;"><?= $latest_progress ? $latest_progress['bmi'] : '—' ?></div>
        </div>
        <div style="text-align:center;padding:15px;background:#fafafa;border-radius:10px;border:1px solid #f0f0f0;">
          <div style="font-size:13px;color:#999;margin-bottom:4px;"><i class="fas fa-calendar-check" style="color:var(--success-color);"></i> Attendance</div>
          <div style="font-size:20px;font-weight:700;"><?= $att_pct ?>%</div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div style="display:flex;gap:0;border-bottom:2px solid #f0f0f0;margin-bottom:20px;">
      <?php
      $tabs = ['overview'=>'Overview','progress'=>'Progress','workouts'=>'Workouts','notes'=>'Notes'];
      foreach($tabs as $key=>$label):
        $active = $active_tab === $key;
      ?>
      <a href="?id=<?= $member_id ?>&tab=<?= $key ?>" style="padding:12px 20px;font-size:14px;font-weight:600;text-decoration:none;
        color:<?= $active ? 'var(--active-color)' : '#999' ?>;
        border-bottom:<?= $active ? '2px solid var(--active-color)' : '2px solid transparent' ?>;
        margin-bottom:-2px;">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- OVERVIEW TAB -->
    <?php if($active_tab === 'overview'): ?>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="table-container" style="padding:20px;">
          <h3 style="margin:0 0 15px;font-size:16px;font-weight:600;">Overall Progress</h3>
          <div style="font-size:13px;color:#999;margin-bottom:8px;">Goal Completion</div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <div style="flex:1;height:10px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
              <div style="height:100%;background:var(--active-color);width:<?= $member['progress'] ?? 0 ?>%;"></div>
            </div>
            <span style="font-weight:700;"><?= $member['progress'] ?? 0 ?>%</span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div>
              <div style="font-size:12px;color:#999;">Membership Status</div>
              <span class="status-badge <?= strtolower($member['membership_status']) ?>"><?= $member['membership_status'] ?></span>
            </div>
            <div>
              <div style="font-size:12px;color:#999;">Fitness Level</div>
              <span style="font-weight:600;font-size:14px;"><?= $member['fitness_level'] ?></span>
            </div>
            <div>
              <div style="font-size:12px;color:#999;">Plan Expires</div>
              <span style="font-size:13px;color:#666;"><?= date('d M Y', strtotime($member['end_date'])) ?></span>
            </div>
            <div>
              <div style="font-size:12px;color:#999;">Membership</div>
              <span style="font-size:13px;color:#666;"><?= htmlspecialchars($member['membership_type']) ?></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="table-container" style="padding:0;">
          <div class="table-header"><h3>Recent Workouts</h3></div>
          <?php if(empty($sessions)): ?>
            <div style="padding:30px;text-align:center;color:#aaa;font-size:14px;">No sessions yet</div>
          <?php else: ?>
          <table class="modern-table">
            <tbody>
              <?php foreach(array_slice($sessions,0,5) as $s): ?>
              <tr>
                <td>
                  <div style="font-weight:600;font-size:14px;"><?= htmlspecialchars($s['title']) ?></div>
                  <div style="font-size:12px;color:#999;"><?= date('d/m/Y', strtotime($s['session_date'])) ?> &bull; <?= $s['duration'] ?> min</div>
                </td>
                <td><span class="status-badge <?= strtolower($s['status']) === 'completed' ? 'active' : (strtolower($s['status']) === 'cancelled' ? 'expired' : 'pending') ?>" style="font-size:11px;"><?= $s['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- PROGRESS TAB -->
    <?php elseif($active_tab === 'progress'): ?>
    <div class="row g-4">
      <div class="col-md-5">
        <div class="form-container">
          <h3 style="margin:0 0 20px;font-size:16px;font-weight:600;"><i class="fas fa-plus-circle" style="color:var(--active-color);"></i> Add Progress Entry</h3>
          <form method="POST" action="view-member-details.php?id=<?= $member_id ?>&tab=progress">
            <input type="hidden" name="add_progress" value="1">
            <div class="form-row">
              <div>
                <label>Date *</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
              </div>
            </div>
            <div class="form-row">
              <div>
                <label>Weight (kg) *</label>
                <input type="number" name="weight" step="0.1" placeholder="e.g. 72.5" required>
              </div>
              <div>
                <label>Height (cm) *</label>
                <input type="number" name="height" step="0.1" placeholder="e.g. 175">
              </div>
            </div>
            <div class="form-row">
              <div>
                <label>BMI <span style="font-weight:400;color:#aaa;">(auto-calculated)</span></label>
                <input type="number" name="bmi" step="0.1" placeholder="Auto or manual">
              </div>
            </div>
            <div>
              <label>Notes</label>
              <textarea name="notes" placeholder="Any observations or notes..."></textarea>
            </div>
            <button type="submit" class="btn app-btn-primary w-100 mt-3">Save Progress</button>
          </form>
        </div>
      </div>
      <div class="col-md-7">
        <div class="table-container">
          <div class="table-header"><h3>Progress History</h3></div>
          <?php if(empty($progress_history)): ?>
            <div style="padding:30px;text-align:center;color:#aaa;font-size:14px;">No progress records yet. Add the first entry.</div>
          <?php else: ?>
          <table class="modern-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Weight</th>
                <th>Height</th>
                <th>BMI</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($progress_history as $p): ?>
              <tr>
                <td><?= date('d M Y', strtotime($p['date'])) ?></td>
                <td><?= $p['weight'] ? $p['weight'].' kg' : '—' ?></td>
                <td><?= $p['height'] ? $p['height'].' cm' : '—' ?></td>
                <td><?= $p['bmi'] ?: '—' ?></td>
                <td style="font-size:12px;color:#666;max-width:150px;"><?= htmlspecialchars($p['notes'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- WORKOUTS TAB -->
    <?php elseif($active_tab === 'workouts'): ?>
    <div class="row g-4">
      <div class="col-md-8">
        <div class="table-container">
          <div class="table-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3>Session History</h3>
            <a href="schedule.php" class="btn app-btn-primary" style="padding:8px 16px;font-size:13px;">+ New Session</a>
          </div>
          <?php if(empty($sessions)): ?>
            <div style="padding:30px;text-align:center;color:#aaa;font-size:14px;">No sessions recorded yet.</div>
          <?php else: ?>
          <table class="modern-table">
            <thead>
              <tr><th>Title</th><th>Type</th><th>Date</th><th>Duration</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach($sessions as $s): ?>
              <tr>
                <td><span style="font-weight:600;"><?= htmlspecialchars($s['title']) ?></span></td>
                <td><span style="font-size:12px;color:#666;"><?= $s['session_type'] ?></span></td>
                <td><?= date('d M Y', strtotime($s['session_date'])) ?></td>
                <td><?= $s['duration'] ?> min</td>
                <td><span class="status-badge <?= strtolower($s['status']) === 'completed' ? 'active' : (strtolower($s['status']) === 'cancelled' ? 'expired' : 'pending') ?>" style="font-size:11px;"><?= $s['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="table-container" style="padding:20px;">
          <h3 style="margin:0 0 15px;font-size:16px;font-weight:600;">Current Workout Plan</h3>
          <div style="margin-bottom:12px;">
            <div style="font-size:12px;color:#999;margin-bottom:4px;">Plan</div>
            <div style="font-weight:600;"><?= htmlspecialchars($member['current_plan'] ?? 'Not Assigned') ?></div>
          </div>
          <div style="margin-bottom:12px;">
            <div style="font-size:12px;color:#999;margin-bottom:4px;">Goal</div>
            <div style="font-weight:600;"><?= htmlspecialchars($member['goal'] ?? 'Not Set') ?></div>
          </div>
          <div style="margin-bottom:15px;">
            <div style="font-size:12px;color:#999;margin-bottom:6px;">Progress</div>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="flex:1;height:8px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
                <div style="height:100%;background:var(--active-color);width:<?= $member['progress'] ?? 0 ?>%;"></div>
              </div>
              <span style="font-weight:700;"><?= $member['progress'] ?? 0 ?>%</span>
            </div>
          </div>
          <a href="edit-workout-plan.php?id=<?= $member_id ?>" class="btn app-btn-primary w-100" style="font-size:13px;">Edit Plan</a>
        </div>
      </div>
    </div>

    <!-- NOTES TAB -->
    <?php elseif($active_tab === 'notes'): ?>
    <div class="table-container" style="padding:20px;">
      <h3 style="margin:0 0 15px;font-size:16px;font-weight:600;">Progress Notes</h3>
      <?php if(empty($progress_history)): ?>
        <div style="padding:20px;text-align:center;color:#aaa;font-size:14px;">No notes yet. Add progress entries from the Progress tab.</div>
      <?php else: ?>
        <?php foreach($progress_history as $p): if(empty($p['notes'])) continue; ?>
        <div style="padding:15px;border:1px solid #f0f0f0;border-radius:10px;margin-bottom:12px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="font-weight:600;font-size:13px;"><?= date('d M Y', strtotime($p['date'])) ?></span>
            <span style="font-size:12px;color:#999;">by <?= htmlspecialchars($p['recorded_by']) ?></span>
          </div>
          <div style="font-size:14px;color:#555;"><?= nl2br(htmlspecialchars($p['notes'])) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>