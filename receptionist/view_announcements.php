<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'announcements';

// Fetch announcements
$announcements = [];
$res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($res)) { $announcements[] = $row; }

$total = count($announcements);
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Announcements</h1>
        <p class="page-subtitle">All gym announcements and notices</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-bullhorn"></i></div>
        <div class="stat-info"><h3><?= $total ?></h3><p>Total Announcements</p></div>
      </div>
    </div>

    <!-- Announcements List -->
    <?php if (!empty($announcements)): ?>
      <?php foreach ($announcements as $a): ?>
      <div class="members-table-container" style="margin-bottom:15px;">
        <div class="table-header flex justify-between align-center">
          <div>
            <h3 style="margin:0; font-size:17px;"><?= htmlspecialchars($a['title']) ?></h3>
            <p class="page-subtitle" style="margin-top:5px;">
              <i class="fa-solid fa-calendar" style="color:var(--active-color);"></i>
              <?= date('d M Y', strtotime($a['created_at'])) ?>
            </p>
          </div>
        </div>
        <div style="padding:20px 25px;">
          <p style="margin:0; font-size:14px; color:#555; line-height:1.7;">
            <?= nl2br(htmlspecialchars($a['message'])) ?>
          </p>
        </div>
      </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="members-table-container">
        <div class="text-center" style="padding:50px 20px;">
          <i class="fa-solid fa-bullhorn" style="font-size:45px; margin-bottom:15px; display:block; color:#ddd;"></i>
          <p style="font-size:15px; font-weight:500; color:#aaa;">No announcements yet.</p>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>