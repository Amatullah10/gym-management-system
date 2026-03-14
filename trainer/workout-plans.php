<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    header("Location: ../index.php"); exit();
}
$page = 'workout-plans';
$trainer_email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Handle DELETE
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM workout_plan_library WHERE id=$del_id");
    header("Location: workout-plans.php?success=Plan deleted."); exit();
}

// Handle ASSIGN TO MEMBER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $plan_id   = (int)$_POST['plan_id'];
    $member_id = (int)$_POST['member_id'];
    $plan_row  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM workout_plan_library WHERE id=$plan_id"));
    if ($plan_row && $member_id) {
        $pname = mysqli_real_escape_string($conn, $plan_row['name']);
        $exists = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM workout_plans WHERE member_id=$member_id"));
        if ($exists) {
            mysqli_query($conn, "UPDATE workout_plans SET current_plan='$pname', status='Active' WHERE member_id=$member_id");
        } else {
            mysqli_query($conn, "INSERT INTO workout_plans (member_id, current_plan, status) VALUES ($member_id, '$pname', 'Active')");
        }
        header("Location: workout-plans.php?success=Plan assigned successfully!"); exit();
    }
}

// Fetch plans
$search   = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';
$where    = "WHERE 1=1";
if ($search)   $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
if ($category) $where .= " AND category='$category'";

$plans_q = mysqli_query($conn, "SELECT wpl.*,
    (SELECT COUNT(*) FROM workout_plans wp WHERE wp.current_plan=wpl.name) as member_count
    FROM workout_plan_library wpl $where ORDER BY wpl.created_at DESC");
$plans = [];
while($r = mysqli_fetch_assoc($plans_q)) $plans[] = $r;

// Fetch members for assign modal
$members_q = mysqli_query($conn, "SELECT id, full_name FROM members WHERE membership_status='Active' ORDER BY full_name ASC");
$members = [];
while($r = mysqli_fetch_assoc($members_q)) $members[] = $r;

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

    <!-- Page Header -->
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h1 class="page-title">Workout Plans</h1>
        <p class="page-subtitle">Create and manage workout plans for your members</p>
      </div>
      <a href="create-plan.php" class="btn app-btn-primary"><i class="fas fa-plus"></i> Create Plan</a>
    </div>

    <!-- Search + Category Filter -->
    <div class="table-container" style="padding:15px;margin-bottom:20px;">
      <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <div class="search-box" style="flex:1;min-width:200px;">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search workout plans..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <?php
          $cats = [''=>'All','Strength'=>'Strength','Cardio'=>'Cardio','General'=>'General','Athletic'=>'Athletic'];
          foreach($cats as $val=>$label):
            $active = $category === $val;
          ?>
          <button type="submit" name="cat" value="<?= $val ?>" class="btn" style="padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid <?= $active ? 'var(--active-color)' : '#e0e0e0' ?>;background:<?= $active ? 'var(--active-color)' : 'white' ?>;color:<?= $active ? 'white' : '#555' ?>;">
            <?= $label ?>
          </button>
          <?php endforeach; ?>
        </div>
      </form>
    </div>

    <!-- Plans Grid -->
    <?php if(empty($plans)): ?>
      <div style="text-align:center;padding:60px;color:#aaa;">
        <i class="fas fa-dumbbell" style="font-size:40px;margin-bottom:15px;display:block;"></i>
        No workout plans found. <a href="create-plan.php" style="color:var(--active-color);">Create one</a>
      </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;">
      <?php foreach($plans as $plan):
        $diff_color = $plan['difficulty']==='Beginner' ? '#2e7d32' : ($plan['difficulty']==='Intermediate' ? '#f57c00' : 'var(--active-color)');
      ?>
      <div class="table-container" style="padding:20px;position:relative;">

        <!-- Difficulty Badge -->
        <div style="position:absolute;top:15px;right:15px;">
          <span style="font-size:11px;font-weight:700;color:<?= $diff_color ?>;background:<?= $plan['difficulty']==='Beginner' ? '#e8f5e9' : ($plan['difficulty']==='Intermediate' ? '#fff3e0' : '#ffebee') ?>;padding:4px 10px;border-radius:20px;">
            <?= $plan['difficulty'] ?>
          </span>
        </div>

        <!-- Icon -->
        <div style="width:46px;height:46px;background:#fde8e8;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
          <i class="fas fa-dumbbell" style="color:var(--active-color);font-size:18px;"></i>
        </div>

        <h3 style="margin:0 0 8px;font-size:16px;font-weight:700;padding-right:80px;"><?= htmlspecialchars($plan['name']) ?></h3>
        <p style="font-size:13px;color:#666;margin:0 0 15px;line-height:1.5;"><?= htmlspecialchars($plan['description']) ?></p>

        <!-- Meta -->
        <div style="display:flex;justify-content:space-between;font-size:12px;color:#999;margin-bottom:15px;">
          <span><i class="fas fa-clock"></i> <?= $plan['duration_weeks'] ?> weeks</span>
          <span><i class="fas fa-users"></i> <?= $plan['member_count'] ?> members</span>
        </div>

        <!-- Exercises preview -->
        <?php if($plan['exercises']): ?>
        <div style="font-size:12px;color:#888;background:#fafafa;padding:10px;border-radius:8px;margin-bottom:15px;line-height:1.6;">
          <strong style="color:#555;">Exercises:</strong> <?= htmlspecialchars(substr($plan['exercises'], 0, 80)) ?>...
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div style="display:flex;gap:8px;">
          <button onclick="openAssignModal(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['name'], ENT_QUOTES) ?>')"
            class="btn" style="flex:1;background:var(--active-color);color:white;padding:8px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
            <i class="fas fa-user-plus"></i> Assign to Member
          </button>
          <a href="create-plan.php?edit=<?= $plan['id'] ?>" class="btn-action edit" title="Edit"><i class="fas fa-edit"></i></a>
          <a href="workout-plans.php?delete=<?= $plan['id'] ?>" class="btn-action delete" title="Delete"
            onclick="return confirm('Delete this plan?')"><i class="fas fa-trash"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:16px;padding:30px;width:100%;max-width:420px;margin:20px;">
    <h3 style="margin:0 0 5px;font-size:18px;font-weight:700;">Assign Plan</h3>
    <p id="assignPlanName" style="color:#666;font-size:14px;margin:0 0 20px;"></p>
    <form method="POST">
      <input type="hidden" name="assign" value="1">
      <input type="hidden" name="plan_id" id="assignPlanId">
      <label>Select Member *</label>
      <select name="member_id" required style="margin-bottom:20px;">
        <option value="">-- Select Member --</option>
        <?php foreach($members as $m): ?>
        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div style="display:flex;gap:10px;">
        <button type="button" onclick="closeAssignModal()" class="btn app-btn-secondary" style="flex:1;">Cancel</button>
        <button type="submit" class="btn app-btn-primary" style="flex:1;">Assign</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openAssignModal(planId, planName) {
  document.getElementById('assignPlanId').value = planId;
  document.getElementById('assignPlanName').textContent = 'Plan: ' + planName;
  document.getElementById('assignModal').style.display = 'flex';
}
function closeAssignModal() {
  document.getElementById('assignModal').style.display = 'none';
}
// Close on backdrop click
document.getElementById('assignModal').addEventListener('click', function(e) {
  if (e.target === this) closeAssignModal();
});
setTimeout(() => {
  const a = document.querySelector('.app-alert');
  if(a) { a.style.transition='opacity 0.5s'; a.style.opacity='0'; setTimeout(()=>a.remove(),500); }
}, 4000);
</script>