<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'checkin';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));
$member_id = $member['id'] ?? 0;

// Gym coordinates
define('GYM_LAT', 8.9529887);
define('GYM_LNG', 72.8159492);
define('GYM_RADIUS', 100); // meters

$today = date('Y-m-d');
$already_checked = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendance WHERE member_id=$member_id AND attendance_date='$today' AND status='Present'"));

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_checked) {
    $lat = (float)$_POST['lat'];
    $lng = (float)$_POST['lng'];

    // Haversine formula to calculate distance
    $earth_radius = 6371000; // meters
    $dlat = deg2rad($lat - GYM_LAT);
    $dlng = deg2rad($lng - GYM_LNG);
    $a = sin($dlat/2)*sin($dlat/2) + cos(deg2rad(GYM_LAT))*cos(deg2rad($lat))*sin($dlng/2)*sin($dlng/2);
    $c = 2*atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c;

    if ($distance <= GYM_RADIUS) {
        // Check if record exists for today
        $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM attendance WHERE member_id=$member_id AND attendance_date='$today'"));
        if ($existing) {
            mysqli_query($conn, "UPDATE attendance SET status='Present', check_in_time=CURTIME(), marked_by='$email' WHERE member_id=$member_id AND attendance_date='$today'");
        } else {
            mysqli_query($conn, "INSERT INTO attendance (member_id, attendance_date, status, check_in_time, marked_by) VALUES ($member_id, '$today', 'Present', CURTIME(), '$email')");
        }
        $already_checked = true;
        $success = "✅ Check-in successful! Welcome to NextGen Fitness.";
    } else {
        $distance_m = round($distance);
        $error = "❌ You are {$distance_m}m away from the gym. You must be within ".GYM_RADIUS."m to check in.";
    }
}

// Attendance history
$records = [];
$res = mysqli_query($conn, "SELECT * FROM attendance WHERE member_id=$member_id ORDER BY attendance_date DESC LIMIT 30");
while($r = mysqli_fetch_assoc($res)) $records[] = $r;

$att_total   = count($records);
$att_present = count(array_filter($records, fn($r) => $r['status']==='Present'));
$att_pct     = $att_total > 0 ? round(($att_present/$att_total)*100) : 0;

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check In - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <div class="page-header">
    <h1 class="page-title">Gym Check-In</h1>
    <p class="page-subtitle">Check in using your location</p>
  </div>

  <?php if($success): ?><div class="app-alert app-alert-success"><?= $success ?></div><?php endif; ?>
  <?php if($error): ?><div class="app-alert app-alert-error"><?= $error ?></div><?php endif; ?>

  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
      <div class="stat-info"><p>Present Days</p><h3><?= $att_present ?></h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-chart-line"></i></div>
      <div class="stat-info"><p>Attendance Rate</p><h3><?= $att_pct ?>%</h3></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon <?= $already_checked ? 'green' : 'orange' ?>">
        <i class="fas fa-<?= $already_checked ? 'check-circle' : 'clock' ?>"></i>
      </div>
      <div class="stat-info"><p>Today</p><h3><?= $already_checked ? 'Checked In' : 'Not Yet' ?></h3></div>
    </div>
  </div>

  <!-- Check-In Card -->
  <div class="table-container" style="padding:30px;margin-bottom:20px;text-align:center;max-width:500px;margin-left:auto;margin-right:auto;">
    <div style="font-size:13px;color:#999;margin-bottom:5px;"><?= date('l, F d, Y') ?></div>
    <h3 style="margin:0 0 20px;font-size:18px;font-weight:700;">
      <?= $already_checked ? '✅ You are checked in for today!' : 'Check In to NextGen Fitness' ?>
    </h3>

    <?php if(!$already_checked): ?>
    <div id="locationStatus" style="padding:15px;background:#f5f5f5;border-radius:10px;margin-bottom:20px;font-size:14px;color:#666;">
      <i class="fas fa-location-dot" style="margin-right:8px;"></i>Getting your location...
    </div>
    <form method="POST" id="checkinForm">
      <input type="hidden" name="lat" id="latInput">
      <input type="hidden" name="lng" id="lngInput">
      <button type="submit" id="checkinBtn" class="btn app-btn-primary" style="padding:14px 40px;font-size:15px;" disabled>
        <i class="fas fa-location-dot"></i> Check In Now
      </button>
    </form>
    <p style="font-size:12px;color:#aaa;margin-top:15px;">You must be within <?= GYM_RADIUS ?>m of the gym to check in.</p>
    <?php else: ?>
    <div style="font-size:48px;margin:20px 0;">🎉</div>
    <div style="font-size:14px;color:#2e7d32;font-weight:600;">Great job showing up today!</div>
    <?php if($already_checked && isset($already_checked['check_in_time'])): ?>
    <div style="font-size:13px;color:#aaa;margin-top:8px;">Checked in at <?= $already_checked['check_in_time'] ?></div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Attendance History -->
  <div class="table-container">
    <div class="table-header"><h3>Attendance History</h3></div>
    <table class="modern-table">
      <thead><tr><th>Date</th><th>Status</th><th>Check-in Time</th></tr></thead>
      <tbody>
        <?php if(empty($records)): ?>
        <tr><td colspan="3" style="text-align:center;padding:30px;color:#aaa;">No attendance records yet.</td></tr>
        <?php else: foreach($records as $r): ?>
        <tr>
          <td><?= date('d M Y', strtotime($r['attendance_date'])) ?></td>
          <td><span class="status-badge <?= $r['status']==='Present'?'active':($r['status']==='Absent'?'expired':'pending') ?>"><?= $r['status'] ?></span></td>
          <td><?= $r['check_in_time'] ?? '—' ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

</div></div>

<script>
// Get GPS location on page load
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            document.getElementById('locationStatus').innerHTML = '<i class="fas fa-check-circle" style="color:#2e7d32;margin-right:8px;"></i>Location detected successfully!';
            document.getElementById('locationStatus').style.background = '#e8f5e9';
            document.getElementById('checkinBtn').disabled = false;
        },
        function(err) {
            document.getElementById('locationStatus').innerHTML = '<i class="fas fa-times-circle" style="color:#d32f2f;margin-right:8px;"></i>Location access denied. Please enable location to check in.';
            document.getElementById('locationStatus').style.background = '#ffebee';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
} else {
    document.getElementById('locationStatus').innerHTML = '<i class="fas fa-times-circle" style="color:#d32f2f;margin-right:8px;"></i>Geolocation not supported by your browser.';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>