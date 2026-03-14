<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    header("Location: ../index.php"); exit();
}
$page = 'assigned-members';

$sql = "SELECT m.id, m.full_name, m.email, m.membership_status,
    COALESCE(wp.goal,'Not Set') as goal,
    COALESCE(wp.current_plan,'Not Assigned') as current_plan,
    COALESCE(wp.progress,0) as progress,
    COALESCE(wp.status,'Pending') as workout_status
    FROM members m LEFT JOIN workout_plans wp ON m.id=wp.member_id
    ORDER BY m.full_name ASC";
$result = mysqli_query($conn, $sql);
$members = [];
while($r = mysqli_fetch_assoc($result)) $members[] = $r;
$total = count($members);

$success = isset($_GET['success']) ? $_GET['success'] : '';
include '../layout/header.php';
include '../layout/sidebar.php';
?>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/common.css">

<div class="main-wrapper">
  <div class="main-content">

    <?php if($success): ?>
      <div class="app-alert app-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h1 class="page-title">Assigned Members</h1>
        <p class="page-subtitle">Manage and track your <?= $total ?> assigned members</p>
      </div>
    </div>

    <div style="display:flex;gap:15px;margin-bottom:20px;">
      <div class="search-box" style="flex:1;">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search by name or email...">
      </div>
      <select class="filter-select" id="statusFilter">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <div class="table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Member</th>
            <th>Goal</th>
            <th>Current Plan</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="membersBody">
          <?php foreach($members as $m): ?>
          <tr data-status="<?= strtolower($m['workout_status']) ?>">
            <td>
              <div class="member-cell">
                <div class="member-avatar"><?= strtoupper(substr($m['full_name'],0,1)) ?></div>
                <div class="member-info">
                  <span class="name"><?= htmlspecialchars($m['full_name']) ?></span>
                  <span class="meta"><?= htmlspecialchars($m['email']) ?></span>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($m['goal']) ?></td>
            <td><?= htmlspecialchars($m['current_plan']) ?></td>
            <td style="min-width:150px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="flex:1;height:8px;background:#f0f0f0;border-radius:10px;overflow:hidden;">
                  <div style="height:100%;background:var(--active-color);width:<?= $m['progress'] ?>%;"></div>
                </div>
                <span style="font-weight:600;font-size:13px;"><?= $m['progress'] ?>%</span>
              </div>
            </td>
            <td><span class="status-badge <?= strtolower($m['workout_status']) ?>"><?= $m['workout_status'] ?></span></td>
            <td>
              <div class="action-buttons">
                <a href="view-member-details.php?id=<?= $m['id'] ?>" class="btn-action view" title="View Details"><i class="fas fa-eye"></i></a>
                <a href="edit-workout-plan.php?id=<?= $m['id'] ?>" class="btn-action edit" title="Edit Workout Plan"><i class="fas fa-dumbbell"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($members)): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:#aaa;">No members found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#membersBody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
document.getElementById('statusFilter').addEventListener('change', function() {
  const f = this.value;
  document.querySelectorAll('#membersBody tr').forEach(r => {
    r.style.display = (!f || r.dataset.status === f) ? '' : 'none';
  });
});
</script>