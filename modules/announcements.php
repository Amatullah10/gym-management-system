<?php
session_start();
require_once '../dbcon.php';

/* TEMP: simulate logged-in user */
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}
$role  = $_SESSION['role'];
$page = 'announcements';

$role     = $_SESSION['role'];
$can_post = in_array($role, ['admin', 'receptionist']);

// ── Resolve poster display name ──
function getPosterName($conn, $role, $email) {
    $email_esc = mysqli_real_escape_string($conn, $email);
    $res = mysqli_query($conn, "SELECT full_name FROM staff WHERE email='$email_esc'");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return $row['full_name'] . ' (' . ucfirst($role) . ')';
    }
    return ucfirst($role);
}

$success_message = '';
$error_message   = '';

// ── HANDLE POST ACTIONS ──
if ($can_post && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'post') {
        $title    = mysqli_real_escape_string($conn, trim($_POST['title']));
        $message  = mysqli_real_escape_string($conn, trim($_POST['message']));
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $poster   = mysqli_real_escape_string($conn, getPosterName($conn, $role, $_SESSION['email']));
        $email_esc = mysqli_real_escape_string($conn, $_SESSION['email']);
        $uid_res   = mysqli_query($conn, "SELECT id FROM users WHERE email='$email_esc'");
        $uid       = ($uid_res && mysqli_num_rows($uid_res) > 0) ? mysqli_fetch_assoc($uid_res)['id'] : 'NULL';
        $sql = "INSERT INTO announcements (title, message, category, is_pinned, posted_by, posted_by_name, created_at)
                VALUES ('$title', '$message', '$category', 0, $uid, '$poster', NOW())";
        mysqli_query($conn, $sql)
            ? $success_message = "Announcement posted successfully!"
            : $error_message   = "Error: " . mysqli_error($conn);
    }

    if ($action === 'edit') {
        $id       = (int) $_POST['ann_id'];
        $title    = mysqli_real_escape_string($conn, trim($_POST['title']));
        $message  = mysqli_real_escape_string($conn, trim($_POST['message']));
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        mysqli_query($conn, "UPDATE announcements SET title='$title', message='$message', category='$category' WHERE id=$id")
            ? $success_message = "Announcement updated!"
            : $error_message   = "Update failed: " . mysqli_error($conn);
    }

    if ($action === 'delete') {
        $id = (int) $_POST['ann_id'];
        mysqli_query($conn, "DELETE FROM announcements WHERE id=$id")
            ? $success_message = "Announcement deleted."
            : $error_message   = "Delete failed.";
    }

    if ($action === 'pin') {
        $id  = (int) $_POST['ann_id'];
        $new = ((int) $_POST['current_pin']) ? 0 : 1;
        mysqli_query($conn, "UPDATE announcements SET is_pinned=$new WHERE id=$id");
        $success_message = $new ? "Announcement pinned!" : "Announcement unpinned.";
    }
}

// ── FETCH — pinned first, then newest ──
$result        = mysqli_query($conn, "SELECT * FROM announcements ORDER BY is_pinned DESC, created_at DESC");
$announcements = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) $announcements[] = $row;
}

// Map category to existing common.css app-badge classes
function catBadgeClass($cat) {
    return match($cat) {
        'Urgent'      => 'app-badge app-badge-danger',
        'Event'       => 'app-badge app-badge-success',
        'Maintenance' => 'app-badge app-badge-warning',
        default       => 'app-badge bg-light border text-primary',
    };
}

function catIcon($cat) {
    return match($cat) {
        'Urgent'      => '<i class="fa-solid fa-triangle-exclamation me-1"></i>',
        'Event'       => '<i class="fa-solid fa-calendar-star me-1"></i>',
        'Maintenance' => '<i class="fa-solid fa-bell me-1"></i>',
        default       => '<i class="fa-solid fa-circle-info me-1"></i>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements - Gym Management</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS — common.css only, no extra file -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>

<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <!-- Alerts — common.css: .app-alert .app-alert-success / .app-alert-error -->
    <?php if ($success_message): ?>
      <div class="app-alert app-alert-success">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_message) ?>
      </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="app-alert app-alert-error">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- Page Header — common.css: .page-header .page-title .page-subtitle -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="page-title">
            <i class="fa-solid fa-bullhorn me-2"></i>Announcements
          </h1>
          <p class="page-subtitle">
            <?= $can_post ? 'Create and manage announcements for all members' : 'View latest announcements from management' ?>
          </p>
        </div>
        <?php if ($can_post): ?>
          <button class="btn app-btn-primary" data-bs-toggle="modal" data-bs-target="#newAnnModal">
            <i class="fa-solid fa-plus me-1"></i> New Announcement
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- View-only notice — common.css: .app-alert .app-alert-warning -->
    <?php if (!$can_post): ?>
      <div class="app-alert app-alert-warning">
        <i class="fa-solid fa-circle-info me-1"></i>
        You are viewing announcements posted by management.
      </div>
    <?php endif; ?>

    <!-- Search + Category Filter — common.css: .search-box .filter-select -->
    <div class="d-flex gap-3 mb-4">
      <div class="search-box flex-grow-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search announcements...">
      </div>
      <select class="filter-select" id="catFilter">
        <option value="">All Categories</option>
        <option value="General">General</option>
        <option value="Urgent">Urgent</option>
        <option value="Event">Event</option>
        <option value="Maintenance">Maintenance</option>
      </select>
    </div>

    <!-- Announcements List -->
    <div id="annList">
      <?php if (count($announcements) > 0): ?>
        <?php foreach ($announcements as $ann):
          $pinned  = (int) $ann['is_pinned'];
          $cat     = $ann['category'] ?? 'General';
          $badgeCls = catBadgeClass($cat);
          $icon     = catIcon($cat);
        ?>

        <!--
          Card: common.css .app-card
          Pinned red left border: Bootstrap border-start border-3 border-danger
        -->
        <div class="app-card mb-3 <?= $pinned ? 'border-start border-3 border-danger' : '' ?>"
             data-cat="<?= htmlspecialchars($cat) ?>">

          <!-- Top row: title + action buttons -->
          <div class="d-flex justify-content-between align-items-start gap-3 mb-2">

            <!-- Title — common.css h2 colour is active-color, use .app-card-header for weight -->
            <h5 class="app-card-header mb-0 d-flex align-items-center gap-2">
              <?php if ($pinned): ?>
                <i class="fa-solid fa-thumbtack text-danger"></i>
              <?php endif; ?>
              <?= htmlspecialchars($ann['title']) ?>
            </h5>

            <!-- Action buttons — common.css: .action-buttons .btn-action .view/.edit/.delete -->
            <?php if ($can_post): ?>
            <div class="action-buttons flex-shrink-0">
              <!-- Pin toggle — reuse .btn-action.view (purple) -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="pin">
                <input type="hidden" name="ann_id" value="<?= $ann['id'] ?>">
                <input type="hidden" name="current_pin" value="<?= $pinned ?>">
                <button type="submit"
                        class="btn-action <?= $pinned ? 'delete' : 'view' ?>"
                        title="<?= $pinned ? 'Unpin' : 'Pin' ?>">
                  <i class="fa-solid fa-thumbtack"></i>
                </button>
              </form>
              <!-- Edit -->
              <button class="btn-action edit" title="Edit"
                      onclick='openEditModal(<?= json_encode($ann) ?>)'>
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <!-- Delete -->
              <button class="btn-action delete" title="Delete"
                      onclick="openDeleteModal(<?= $ann['id'] ?>, '<?= htmlspecialchars($ann['title']) ?>')">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
            <?php endif; ?>
          </div>

          <!-- Meta row: category badge + posted by + date -->
          <!-- common.css: .app-badge .app-badge-danger/.success/.warning + Bootstrap text utilities -->
          <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
            <span class="<?= $badgeCls ?>">
              <?= $icon ?><?= htmlspecialchars($cat) ?>
            </span>
            <?php if (!empty($ann['posted_by_name'])): ?>
            <span class="expiry-date">
              <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($ann['posted_by_name']) ?>
            </span>
            <?php endif; ?>
            <span class="expiry-date">
              <i class="fa-regular fa-clock me-1"></i><?= date('d M Y', strtotime($ann['created_at'])) ?>
            </span>
          </div>

          <!-- Message text -->
          <p class="page-subtitle mb-0" style="">
            <?= htmlspecialchars($ann['message']) ?>
          </p>

        </div>
        <?php endforeach; ?>

      <?php else: ?>
        <!-- Empty state — common.css: .text-center .page-subtitle -->
        <div class="app-card text-center py-5">
          <i class="fa-regular fa-bell-slash fa-3x mb-3 text-muted"></i>
          <p class="page-subtitle">No announcements found.</p>
          <?php if ($can_post): ?>
            <button class="btn app-btn-primary mt-10"
                    data-bs-toggle="modal" data-bs-target="#newAnnModal">
              Post First Announcement
            </button>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>


<!-- ================================================
     MODALS — only for admin / receptionist
     Form inputs/labels auto-styled by common.css globally
================================================ -->
<?php if ($can_post): ?>

<!-- NEW ANNOUNCEMENT MODAL -->
<div class="modal fade" id="newAnnModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">
          <i class="fa-solid fa-bullhorn text-danger me-2"></i>New Announcement
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="post">
        <div class="modal-body">
          <!-- common.css: label + input[type=text] globally styled -->
          <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" placeholder="Announcement title" required autofocus>
          </div>
          <div class="mb-3">
            <label>Category</label>
            <select name="category" required>
              <option value="General">General</option>
              <option value="Urgent">Urgent</option>
              <option value="Event">Event</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Message</label>
            <textarea name="message" placeholder="Write your announcement..." required
                      style="min-height:120px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn app-btn-primary">
            <i class="fa-solid fa-paper-plane me-1"></i> Post Announcement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- EDIT ANNOUNCEMENT MODAL -->
<div class="modal fade" id="editAnnModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">
          <i class="fa-regular fa-pen-to-square text-danger me-2"></i>Edit Announcement
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="ann_id" id="edit_ann_id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" id="edit_ann_title" required>
          </div>
          <div class="mb-3">
            <label>Category</label>
            <select name="category" id="edit_ann_cat" required>
              <option value="General">General</option>
              <option value="Urgent">Urgent</option>
              <option value="Event">Event</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Message</label>
            <textarea name="message" id="edit_ann_msg" required
                      style="min-height:120px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn app-btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deleteAnnModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content" style="border-radius:12px;border:none;">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold text-danger">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="ann_id" id="del_ann_id">
        <div class="modal-body pt-1">
          <p class="mb-1">Are you sure you want to delete <strong id="del_ann_title"></strong>?</p>
          <p class="page-subtitle mb-0">This action cannot be undone.</p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn app-btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash-can me-1"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php endif; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── EDIT MODAL ──
function openEditModal(ann) {
  document.getElementById('edit_ann_id').value    = ann.id;
  document.getElementById('edit_ann_title').value = ann.title;
  document.getElementById('edit_ann_cat').value   = ann.category || 'General';
  document.getElementById('edit_ann_msg').value   = ann.message;
  new bootstrap.Modal(document.getElementById('editAnnModal')).show();
}

// ── DELETE MODAL ──
function openDeleteModal(id, title) {
  document.getElementById('del_ann_id').value          = id;
  document.getElementById('del_ann_title').textContent = title;
  new bootstrap.Modal(document.getElementById('deleteAnnModal')).show();
}

// ── SEARCH ──
document.getElementById('searchInput').addEventListener('input', filterAnn);

// ── CATEGORY FILTER ──
document.getElementById('catFilter').addEventListener('change', filterAnn);

function filterAnn() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const cat    = document.getElementById('catFilter').value;

  document.querySelectorAll('#annList .app-card').forEach(card => {
    const matchSearch = !search || card.textContent.toLowerCase().includes(search);
    const matchCat    = !cat   || card.dataset.cat === cat;
    card.style.display = (matchSearch && matchCat) ? '' : 'none';
  });
}

// ── AUTO-HIDE ALERTS ──
setTimeout(() => {
  const a = document.querySelector('.app-alert');
  if (a) {
    a.style.transition = 'opacity 0.5s';
    a.style.opacity    = '0';
    setTimeout(() => a.remove(), 500);
  }
}, 5000);
</script>
</body>
</html>