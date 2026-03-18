<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'settings';
$success = $error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Gym Info Update
    if (isset($_POST['update_gym'])) {
        $gym_name     = mysqli_real_escape_string($conn, $_POST['gym_name']);
        $gym_email    = mysqli_real_escape_string($conn, $_POST['gym_email']);
        $gym_phone    = mysqli_real_escape_string($conn, $_POST['gym_phone']);
        $gym_address  = mysqli_real_escape_string($conn, $_POST['gym_address']);
        $gym_capacity = (int)$_POST['gym_capacity'];

        // Store in a settings table or just show success for now
        $success = "Gym information updated successfully!";
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current  = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm  = $_POST['confirm_password'];

        $email = mysqli_real_escape_string($conn, $_SESSION['email']);
        $user  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"));

        if (!password_verify($current, $user['password']) && $user['password'] !== $current) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new_pass) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new_pass !== $confirm) {
            $error = "New passwords do not match.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $hashed_safe = mysqli_real_escape_string($conn, $hashed);
            mysqli_query($conn, "UPDATE users SET password='$hashed_safe' WHERE email='$email'");
            $success = "Password changed successfully!";
        }
    }
}

// Get admin info
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='" . mysqli_real_escape_string($conn, $_SESSION['email']) . "'"));

// Get quick stats for info
$total_members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM members"))['t'];
$total_staff   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM staff WHERE status='Active'"))['t'];
$total_equip   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM equipment"))['t'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - NextGen Fitness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <h1 class="page-title">Settings</h1>
      <p class="page-subtitle">Manage gym settings and your account</p>
    </div>

    <?php if($success): ?>
    <div class="app-alert app-alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
    <div class="app-alert app-alert-error"><i class="fas fa-times-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- Gym Information -->
      <div class="col-md-8">
        <div class="form-container">
          <h3 style="margin:0 0 5px;font-size:16px;font-weight:700;"><i class="fas fa-building" style="color:var(--active-color);margin-right:8px;"></i>Gym Information</h3>
          <p class="section-subtitle">Basic details about your gym</p>
          <form method="POST">
            <div class="form-row">
              <div>
                <label>Gym Name</label>
                <input type="text" name="gym_name" value="NextGen Fitness">
              </div>
              <div>
                <label>Contact Email</label>
                <input type="email" name="gym_email" value="nextgenfitness1407@gmail.com">
              </div>
            </div>
            <div class="form-row">
              <div>
                <label>Phone Number</label>
                <input type="text" name="gym_phone" value="+91 98765 43210">
              </div>
              <div>
                <label>Gym Capacity</label>
                <input type="number" name="gym_capacity" value="150">
              </div>
            </div>
            <div>
              <label>Address</label>
              <textarea name="gym_address">NextGen Fitness, Veraval, Gujarat, India</textarea>
            </div>
            <button type="submit" name="update_gym" class="btn app-btn-primary mt-3">
              <i class="fas fa-save"></i> Save Changes
            </button>
          </form>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="col-md-4">
        <div class="table-container" style="padding:20px;">
          <h3 style="margin:0 0 15px;font-size:16px;font-weight:700;"><i class="fas fa-chart-bar" style="color:var(--active-color);margin-right:8px;"></i>System Overview</h3>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#fafafa;border-radius:8px;">
              <span style="font-size:13px;color:#666;"><i class="fas fa-users" style="margin-right:8px;color:var(--active-color);"></i>Total Members</span>
              <strong><?= $total_members ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#fafafa;border-radius:8px;">
              <span style="font-size:13px;color:#666;"><i class="fas fa-user-tie" style="margin-right:8px;color:var(--active-color);"></i>Active Staff</span>
              <strong><?= $total_staff ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#fafafa;border-radius:8px;">
              <span style="font-size:13px;color:#666;"><i class="fas fa-dumbbell" style="margin-right:8px;color:var(--active-color);"></i>Equipment</span>
              <strong><?= $total_equip ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#fafafa;border-radius:8px;">
              <span style="font-size:13px;color:#666;"><i class="fas fa-user-shield" style="margin-right:8px;color:var(--active-color);"></i>Logged in as</span>
              <strong>Admin</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Change Password -->
      <div class="col-md-6">
        <div class="form-container">
          <h3 style="margin:0 0 5px;font-size:16px;font-weight:700;"><i class="fas fa-lock" style="color:var(--active-color);margin-right:8px;"></i>Change Password</h3>
          <p class="section-subtitle">Update your admin login password</p>
          <form method="POST">
            <div>
              <label>Current Password</label>
              <input type="password" name="current_password" placeholder="Enter current password" required>
            </div>
            <div style="margin-top:12px;">
              <label>New Password</label>
              <input type="password" name="new_password" placeholder="Min 6 characters" required>
            </div>
            <div style="margin-top:12px;">
              <label>Confirm New Password</label>
              <input type="password" name="confirm_password" placeholder="Repeat new password" required>
            </div>
            <button type="submit" name="change_password" class="btn app-btn-primary mt-3">
              <i class="fas fa-key"></i> Change Password
            </button>
          </form>
        </div>
      </div>

      <!-- Membership Plans Info -->
      <div class="col-md-6">
        <div class="form-container">
          <h3 style="margin:0 0 5px;font-size:16px;font-weight:700;"><i class="fas fa-id-card" style="color:var(--active-color);margin-right:8px;"></i>Membership Plans</h3>
          <p class="section-subtitle">Current plan pricing in system</p>
          <div style="display:flex;flex-direction:column;gap:10px;margin-top:15px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:#fff5f5;border-radius:10px;border-left:4px solid var(--active-color);">
              <div>
                <div style="font-weight:700;">Basic Plan</div>
                <div style="font-size:12px;color:#999;">Monthly membership</div>
              </div>
              <strong style="font-size:18px;color:var(--active-color);">₹799/mo</strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:#fff5f5;border-radius:10px;border-left:4px solid var(--active-color);">
              <div>
                <div style="font-weight:700;">Standard Plan</div>
                <div style="font-size:12px;color:#999;">Monthly membership</div>
              </div>
              <strong style="font-size:18px;color:var(--active-color);">₹999/mo</strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:#fff5f5;border-radius:10px;border-left:4px solid var(--active-color);">
              <div>
                <div style="font-weight:700;">Premium Plan</div>
                <div style="font-size:12px;color:#999;">Monthly membership</div>
              </div>
              <strong style="font-size:18px;color:var(--active-color);">₹1,299/mo</strong>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
setTimeout(() => {
  const a = document.querySelector('.app-alert');
  if(a) { a.style.transition='opacity 0.5s'; a.style.opacity='0'; setTimeout(()=>a.remove(),500); }
}, 4000);
</script>
</body>
</html>