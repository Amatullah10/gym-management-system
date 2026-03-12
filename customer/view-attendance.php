<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'view-attendance';

// escape email
$email = mysqli_real_escape_string($conn, $_SESSION['email']);

$member_res = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$member = mysqli_fetch_assoc($member_res);

if (!$member) {
    die("<div style='font-family:Inter,sans-serif; padding:40px; color:#d32f2f;'>
    <h3>Account Error</h3>
    <p>No member profile found for: <strong>$email</strong>. Please contact the receptionist.</p>
    <a href='../auth/logout.php'>Logout</a>
    </div>");
}

$member_id = $member['id'];

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM attendance WHERE member_id = '$member_id'");
$total_attendance = mysqli_fetch_assoc($res)['cnt'];

$res2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM attendance 
WHERE member_id = '$member_id' 
AND MONTH(attendance_date) = MONTH(NOW()) 
AND YEAR(attendance_date) = YEAR(NOW())");

$this_month = mysqli_fetch_assoc($res2)['cnt'];

$records = [];

$res3 = mysqli_query($conn, "SELECT * FROM attendance WHERE member_id = '$member_id' ORDER BY attendance_date DESC");

while ($row = mysqli_fetch_assoc($res3)) {
    $records[] = $row;
}
?>

<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
<div class="main-content">

<div class="page-header">
<div>
<h1 class="page-title">View Attendance</h1>
<p class="page-subtitle">Your gym attendance records</p>
</div>
</div>

<div class="stats-grid">
<div class="stat-card">
<div class="stat-icon red"><i class="fa-solid fa-calendar-check"></i></div>
<div class="stat-info">
<h3><?= $total_attendance ?></h3>
<p>Total Attendance</p>
</div>
</div>

<div class="stat-card">
<div class="stat-icon green"><i class="fa-solid fa-calendar-days"></i></div>
<div class="stat-info">
<h3><?= $this_month ?></h3>
<p>This Month</p>
</div>
</div>
</div>

<div class="members-table-container">
<div class="table-header"><h3>Attendance Records</h3></div>

<table class="members-table">

<thead>
<tr>
<th>#</th>
<th>Date</th>
<th>Status</th>
<th>Check In</th>
<th>Check Out</th>
<th>Duration</th>
</tr>
</thead>

<tbody>

<?php if (!empty($records)): ?>

<?php foreach ($records as $i => $r):

$duration = '-';

if (!empty($r['check_in_time']) && !empty($r['check_out_time'])) {
$diff = strtotime($r['check_out_time']) - strtotime($r['check_in_time']);
$hours = floor($diff / 3600);
$mins = floor(($diff % 3600) / 60);
$duration = $hours.'h '.$mins.'m';
}

?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= date('d-m-Y', strtotime($r['attendance_date'])) ?></td>

<td>
<span class="status-badge <?= strtolower($r['status']) ?>">
<?= htmlspecialchars($r['status']) ?>
</span>
</td>

<td>
<?= !empty($r['check_in_time']) ? date('h:i A', strtotime($r['check_in_time'])) : '<span style="color:#aaa">-</span>' ?>
</td>

<td>
<?= !empty($r['check_out_time']) ? date('h:i A', strtotime($r['check_out_time'])) : '<span style="color:#aaa">Not recorded</span>' ?>
</td>

<td><?= $duration ?></td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center" style="padding:30px; color:#aaa;">
No attendance records found.
</td>
</tr>

<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>