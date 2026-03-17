<?php
session_start();
require_once '../dbcon.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: ../index.php"); exit();
}
$page = 'my-profile';
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE email='$email'"));

// If no member record found, show friendly message
if (!$member) {
    include '../layout/header.php';
    include '../layout/sidebar.php';
    echo '<div class="main-wrapper"><div class="main-content">';
    echo '<div class="page-header"><h1 class="page-title">My Profile</h1></div>';
    echo '<div class="app-alert app-alert-warning"><i class="fas fa-info-circle"></i> Your profile is not set up yet. Please contact the gym admin to register your member details.</div>';
    echo '</div></div>';
    exit();
}
$member_id = $member['id'];

// Get assigned trainer
$trainer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.full_name FROM trainer_assignments ta JOIN staff s ON s.id=ta.trainer_id WHERE ta.member_id=$member_id AND ta.status='Active'"));

$edit_mode = isset($_GET['edit']);
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ── Update profile info ──
    if ($_POST['action'] === 'update_profile') {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
        $dob       = mysqli_real_escape_string($conn, $_POST['dob']);
        $address   = mysqli_real_escape_string($conn, $_POST['address']);

        mysqli_query($conn, "UPDATE members SET full_name='$full_name', phone='$phone', dob='$dob', address='$address' WHERE id=$member_id");
        $member    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM members WHERE id=$member_id"));
        $success   = "Profile updated successfully!";
        $edit_mode = false;
    }

    // ── Change password ──
    if ($_POST['action'] === 'change_password') {
        $current  = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm  = $_POST['confirm_password'];

        // Fetch current hash from users table
        $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"));

        if (!$u) {
            $error = "User account not found.";
        } elseif (!password_verify($current, $u['password']) && $u['password'] !== $current) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new_pass) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new_pass !== $confirm) {
            $error = "New passwords do not match.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $hashed_esc = mysqli_real_escape_string($conn, $hashed);
            mysqli_query($conn, "UPDATE users SET password='$hashed_esc' WHERE email='$email'");
            $success = "Password changed successfully!";
        }
    }
}

$initials = strtoupper(substr($member['full_name'] ?? 'U', 0, 2));

include '../layout/header.php';
include '../layout/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<div class="main-wrapper"><div class="main-content">

  <div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">NextGen Fitness — Customer Portal</p>
  </div>

  <?php if($success): ?><div class="app-alert app-alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

  <!-- Profile Header Banner -->
  <div class="table-container" style="margin-bottom:20px;overflow:hidden;">
    <div style="background:var(--active-color);height:100px;"></div>
    <div style="padding:0 25px 20px;position:relative;">
      <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:flex-end;gap:15px;">
          <div style="width:70px;height:70px;background:white;border-radius:12px;border:3px solid white;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:var(--active-color);margin-top:-35px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <?= $initials ?>
          </div>
          <div style="padding-bottom:4px;">
            <h3 style="margin:0;font-size:20px;font-weight:700;"><?= htmlspecialchars($member['full_name']) ?></h3>
            <div style="display:flex;gap:8px;margin-top:5px;flex-wrap:wrap;">
              <span style="background:var(--active-color);color:white;font-size:11px;padding:3px 10px;border-radius:20px;"><?= htmlspecialchars($member['membership_type']) ?></span>
              <span style="background:#e8f5e9;color:#2e7d32;font-size:11px;padding:3px 10px;border-radius:20px;"><?= $member['membership_status'] ?></span>
            </div>
          </div>
        </div>
        <?php if(!$edit_mode): ?>
        <a href="?edit=1" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1px solid #ddd;border-radius:8px;color:#555;text-decoration:none;font-size:13px;font-weight:600;">
          <i class="fas fa-pen"></i> Edit Profile
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if($edit_mode): ?>
  <!-- Edit Form -->
  <form method="POST">
    <input type="hidden" name="action" value="update_profile">
    <div class="table-container" style="padding:25px;margin-bottom:15px;">
      <h3 style="margin:0 0 20px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-user" style="color:var(--active-color);"></i> Personal Information
      </h3>
      <div class="form-row">
        <div>
          <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;">Full Name</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>
        </div>
        <div>
          <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;">Email Address</label>
          <input type="email" value="<?= htmlspecialchars($member['email']) ?>" disabled style="background:#f5f5f5;color:#999;">
        </div>
        <div>
          <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;">Phone Number</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>">
        </div>
        <div>
          <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;">Date of Birth</label>
          <input type="date" name="dob" value="<?= $member['dob'] ?>">
        </div>
      </div>
      <div>
        <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;">Home Address</label>
        <textarea name="address"><?= htmlspecialchars($member['address']) ?></textarea>
      </div>
    </div>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn app-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      <a href="my-profile.php" class="btn app-btn-secondary">Cancel</a>
    </div>
  </form>

  <?php else: ?>
  <!-- View Mode -->
  <div class="table-container" style="padding:25px;margin-bottom:15px;">
    <h3 style="margin:0 0 20px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-user" style="color:var(--active-color);"></i> Personal Information
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <?php
      $fields = [
        ['Full Name', $member['full_name'], 'fa-user'],
        ['Email Address', $member['email'], 'fa-envelope'],
        ['Phone Number', $member['phone'], 'fa-phone'],
        ['Date of Birth', $member['dob'], 'fa-calendar'],
        ['Gender', $member['gender'], 'fa-user'],
      ];
      foreach($fields as [$label, $value, $icon]):
      ?>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">
          <i class="fas <?= $icon ?>" style="margin-right:4px;"></i><?= $label ?>
        </div>
        <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($value ?? '—') ?></div>
      </div>
      <?php endforeach; ?>
      <div style="grid-column:1/-1;">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">
          <i class="fas fa-location-dot" style="margin-right:4px;"></i>Home Address
        </div>
        <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($member['address'] ?? '—') ?></div>
      </div>
    </div>
  </div>

  <!-- Change Password -->
  <div class="table-container" style="padding:25px;margin-bottom:15px;">
    <h3 style="margin:0 0 20px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--active-color);">
      <i class="fas fa-lock"></i> Change Password
    </h3>
    <?php if($error): ?><div class="app-alert app-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
    <form method="POST" style="max-width:480px;">
      <input type="hidden" name="action" value="change_password">
      <div style="margin-bottom:14px;">
        <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;display:block;margin-bottom:5px;">Current Password</label>
        <input type="password" name="current_password" required placeholder="Enter current password">
      </div>
      <div style="margin-bottom:14px;">
        <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;display:block;margin-bottom:5px;">New Password</label>
        <input type="password" name="new_password" required placeholder="At least 6 characters">
      </div>
      <div style="margin-bottom:20px;">
        <label style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;display:block;margin-bottom:5px;">Confirm New Password</label>
        <input type="password" name="confirm_password" required placeholder="Repeat new password">
      </div>
      <button type="submit" class="btn app-btn-primary">
        <i class="fas fa-key"></i> Update Password
      </button>
    </form>
  </div>

  <!-- Membership Info -->
  <div class="table-container" style="padding:25px;">
    <h3 style="margin:0 0 20px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--active-color);">
      <i class="fas fa-id-card"></i> Membership & Fitness Info
    </h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Member Since</div>
        <div style="font-weight:600;"><?= date('F d, Y', strtotime($member['created_at'])) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Membership Type</div>
        <div style="font-weight:600;"><?= htmlspecialchars($member['membership_type']) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Assigned Trainer</div>
        <div style="font-weight:600;"><?= $trainer ? htmlspecialchars($trainer['full_name']) : 'Not assigned' ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Fitness Level</div>
        <div style="font-weight:600;"><?= htmlspecialchars($member['fitness_level']) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Plan Expires</div>
        <div style="font-weight:600;"><?= date('F d, Y', strtotime($member['end_date'])) ?></div>
      </div>
      <div>
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:4px;">Status</div>
        <span class="status-badge <?= strtolower($member['membership_status']) ?>"><?= $member['membership_status'] ?></span>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>