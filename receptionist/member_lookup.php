<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'member-lookup';

$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if ($status_filter) {
    $where .= " AND membership_status = '$status_filter'";
}

$members = [];
$res = mysqli_query($conn, "SELECT * FROM members $where ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($res)) { $members[] = $row; }

$total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members"))['t'];
$active  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Active'"))['t'];
$expired = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members WHERE membership_status='Expired'"))['t'];
?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Member Lookup</h1>
        <p class="page-subtitle">Search and view all gym members</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info"><h3><?= $total ?></h3><p>Total Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-user-check"></i></div>
        <div class="stat-info"><h3><?= $active ?></h3><p>Active Members</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-user-clock"></i></div>
        <div class="stat-info"><h3><?= $expired ?></h3><p>Expired Members</p></div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="form-container" style="margin-bottom:25px;">
      <h3 style="font-size:16px; font-weight:600; margin-bottom:15px;">Search Members</h3>
      <form method="GET">
        <div class="form-row">
          <div>
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email or phone...">
          </div>
          <div>
            <label>Status</label>
            <select name="status">
              <option value="">All Status</option>
              <option value="Active"   <?= $status_filter == 'Active'   ? 'selected' : '' ?>>Active</option>
              <option value="Expired"  <?= $status_filter == 'Expired'  ? 'selected' : '' ?>>Expired</option>
              <option value="Inactive" <?= $status_filter == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>
        <div style="display:flex; gap:15px; margin-top:15px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
          <a href="member_lookup.php" class="btn app-btn-secondary"><i class="fa-solid fa-rotate"></i> Reset</a>
          <a href="add_member.php" class="btn app-btn-primary" style="margin-left:auto;"><i class="fa-solid fa-user-plus"></i> Add New Member</a>
        </div>
      </form>
    </div>

    <!-- Members Table -->
    <div class="members-table-container">
      <div class="table-header">
        <h3>All Members (<?= count($members) ?> found)</h3>
      </div>
      <div style="overflow-x:auto;">
        <table class="members-table" style="min-width:1100px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Member</th>
              <th>Phone</th>
              <th>Plan</th>
              <th>Duration</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Fitness Level</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($members)): ?>
              <?php foreach ($members as $i => $m):
                $initial   = strtoupper(substr($m['full_name'], 0, 1));
                $plan_type = strtolower(explode(' - ', $m['membership_type'])[0]);
                $days_left = round((strtotime($m['end_date']) - strtotime(date('Y-m-d'))) / 86400);

                // Auto-calculate status based on end date
                if ($days_left < 0) {
                    $status = 'expired';
                    $status_text = 'EXPIRED';
                } elseif ($days_left <= 30) {
                    $status = 'inactive';
                    $status_text = 'EXPIRING SOON';
                } else {
                    $status = 'active';
                    $status_text = 'ACTIVE';
                }
              ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>
                  <div class="member-cell">
                    <div class="member-avatar"><?= $initial ?></div>
                    <div class="member-info">
                      <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                      <span class="joined"><?= htmlspecialchars($m['email']) ?></span>
                    </div>
                  </div>
                </td>
                <td class="date-display"><?= htmlspecialchars($m['phone']) ?></td>
                <td><span class="plan-badge <?= $plan_type ?>"><?= htmlspecialchars($m['membership_type']) ?></span></td>
                <td class="date-display"><?= $m['duration'] ?></td>
                <td class="date-display"><?= $m['start_date'] ?></td>
                <td class="date-display">
                  <?= $m['end_date'] ?>
                  <?php if ($days_left >= 0 && $days_left <= 30): ?>
                    <br><small style="color:var(--warning-color);"><?= $days_left ?> days left</small>
                  <?php elseif ($days_left < 0): ?>
                    <br><small style="color:var(--danger-color);">Expired</small>
                  <?php endif; ?>
                </td>
                <td class="date-display"><?= $m['fitness_level'] ?></td>
                <td><span class="status-badge <?= $status ?>"><?= $status_text ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="9" class="text-center" style="padding:30px; color:#aaa;">No members found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>