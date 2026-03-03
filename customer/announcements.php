<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'customer') { header("Location: ../index.php"); exit(); }

$page = 'announcements';

$announcements = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'announcements'");
if (mysqli_num_rows($check_table) > 0) {
    $res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY date DESC");
    while ($row = mysqli_fetch_assoc($res)) { $announcements[] = $row; }
}
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Announcements</h1>
        <p class="page-subtitle">Stay updated with the latest gym news</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-bullhorn"></i></div><div class="stat-info"><h3><?= count($announcements) ?></h3><p>Total Announcements</p></div></div>
    </div>

    <?php if (!empty($announcements)): ?>
      <?php foreach ($announcements as $a): ?>
        <div class="members-table-container" style="margin-bottom:20px;">
          <div class="table-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3><i class="fa-solid fa-bullhorn" style="color:var(--active-color); margin-right:8px;"></i><?= htmlspecialchars($a['title']) ?></h3>
            <span style="font-size:13px; color:#999;"><?= date('d M Y', strtotime($a['date'])) ?></span>
          </div>
          <div style="padding:20px 25px;">
            <p style="color:#555; font-size:15px; line-height:1.7; margin:0;"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
            <?php if (!empty($a['target_audience'])): ?>
              <div style="margin-top:15px;"><span class="status-badge active" style="font-size:11px;"><?= htmlspecialchars($a['target_audience']) ?></span></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="members-table-container">
        <div class="table-header"><h3>All Announcements</h3></div>
        <div style="padding:40px; text-align:center; color:#aaa;">
          <i class="fa-solid fa-bullhorn" style="font-size:40px; margin-bottom:15px; display:block;"></i>
          No announcements yet. Check back later!
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>