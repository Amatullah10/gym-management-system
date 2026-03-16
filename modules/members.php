<?php
session_start();
require_once '../dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'member-entry'; // For active sidebar highlight

// For active sidebar highlight

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $fitness_level = mysqli_real_escape_string($conn, $_POST['fitness_level']);
    
    // Check if email already exists
    $check_email = "SELECT id FROM members WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Email already exists! Please use a different email.";
    } else {
        // Insert member into database
        $sql = "INSERT INTO members (full_name, email, phone, dob, gender, address, membership_type, start_date, end_date, duration, fitness_level, membership_status) 
                VALUES ('$full_name', '$email', '$phone', '$dob', '$gender', '$address', '$membership_type', '$start_date', '$end_date', '$duration', '$fitness_level', 'Active')";
        
        if (mysqli_query($conn, $sql)) {
            // Create user account with empty password (they set it via email link)
            $check_user = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
            if (mysqli_num_rows($check_user) === 0) {
                mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('customer', '$email', '')");
            }
            // Send welcome email with set-password link
            require_once '../auth/mailer.php';
            $sent = sendSetPasswordEmail($email, $full_name, $conn);
            $success_message = $sent
                ? "Member registered! Welcome email sent to <strong>$email</strong> to set their password."
                : "Member registered! Could not send email — share this link manually: <a href='http://localhost/gym-management-system/auth/set-password.php' target='_blank'>Set Password Page</a>";
            header("refresh:3;url=members.php");
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Registration - Gym Management</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">
    <div class="form-container mx-auto" style="max-width:900px;">

      <h2>Member Registration</h2>

      <!-- Success Message -->
      <?php if ($success_message): ?>
        <div class="app-alert app-alert-success">
          <i class="fa-solid fa-circle-check"></i> <?= $success_message ?>
        </div>
      <?php endif; ?>

      <!-- Error Message -->
      <?php if ($error_message): ?>
        <div class="app-alert app-alert-error">
          <i class="fa-solid fa-circle-exclamation"></i> <?= $error_message ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <!-- ========== PERSONAL INFORMATION ========== -->
        <div class="section">
          <h3>Personal Information</h3>
          <p class="section-subtitle">Basic details about the member</p>

          <div class="form-row">
            <div>
              <label>Full Name *</label>
              <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div>
              <label>Email *</label>
              <input type="email" name="email" value="<?= $error_message && str_contains($error_message,'Email already') ? '' : htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Phone Number *</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
            </div>
            <div>
              <label>Date of Birth *</label>
              <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Gender *</label>
              <select name="gender" required>
                <option value="">Select</option>
                <option <?= ($_POST['gender'] ?? '')==='Male'?'selected':'' ?>>Male</option>
                <option <?= ($_POST['gender'] ?? '')==='Female'?'selected':'' ?>>Female</option>
                <option <?= ($_POST['gender'] ?? '')==='Other'?'selected':'' ?>>Other</option>
              </select>
            </div>
          </div>

          <label>Address *</label>
          <textarea name="address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>

        <!-- ========== MEMBERSHIP DETAILS ========== -->
        <div class="section">
          <h3>Membership Details</h3>
          <p class="section-subtitle">Select membership type and duration</p>

          <div class="form-row">
            <div>
              <label>Membership Type *</label>
              <select name="membership_type" required>
                <option <?= ($_POST['membership_type'] ?? '')==='Basic - 799/month'?'selected':'' ?>>Basic - 799/month</option>
                <option <?= ($_POST['membership_type'] ?? '')==='Standard - 999/month'?'selected':'' ?>>Standard - 999/month</option>
                <option <?= ($_POST['membership_type'] ?? '')==='Premium - 1299/month'?'selected':'' ?>>Premium - 1299/month</option>
              </select>
            </div>
            <div>
              <label>Start Date *</label>
              <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
            </div>
            <div>
              <label>Duration *</label>
              <select name="duration" id="duration" required>
                <option <?= ($_POST['duration'] ?? '')==='1 Month'?'selected':'' ?>>1 Month</option>
                <option <?= ($_POST['duration'] ?? '')==='3 Months'?'selected':'' ?>>3 Months</option>
                <option <?= ($_POST['duration'] ?? '')==='6 Months'?'selected':'' ?>>6 Months</option>
                <option <?= ($_POST['duration'] ?? '')==='12 Months'?'selected':'' ?>>12 Months</option>
              </select>
            </div>
          </div>

          <label>End Date * <span style="font-size:12px;color:#2e7d32;">(auto-calculated from start date + duration)</span></label>
          <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
        </div>

        <!-- ========== FITNESS INFO ========== -->
        <div class="section">
          <h3>Fitness Information</h3>
          <p class="section-subtitle">Help us understand your health and fitness goals</p>

          <div class="form-row">
            <div>
              <label>Current Fitness Level *</label>
              <select name="fitness_level" required>
                <option <?= ($_POST['fitness_level'] ?? '')==='Beginner'?'selected':'' ?>>Beginner</option>
                <option <?= ($_POST['fitness_level'] ?? '')==='Medium'?'selected':'' ?>>Medium</option>
                <option <?= ($_POST['fitness_level'] ?? '')==='Advanced'?'selected':'' ?>>Advanced</option>
              </select>
            </div>
          </div>
        </div>

        <!-- ========== BUTTONS ========== -->
        <div class="d-flex gap-3">
          <button type="submit" class="btn app-btn-primary w-100">Register Member</button>
          <button type="reset" class="btn app-btn-secondary w-100">Reset Form</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto calculate end date from start date + duration
function calcEndDate() {
    const start = document.getElementById('start_date').value;
    const duration = document.getElementById('duration').value;
    if (!start || !duration) return;

    const months = {
        '1 Month': 1, '3 Months': 3,
        '6 Months': 6, '12 Months': 12
    };
    const m = months[duration] || 0;
    if (!m) return;

    const d = new Date(start);
    d.setMonth(d.getMonth() + m);
    // Format as YYYY-MM-DD
    const yyyy = d.getFullYear();
    const mm   = String(d.getMonth() + 1).padStart(2, '0');
    const dd   = String(d.getDate()).padStart(2, '0');
    document.getElementById('end_date').value = `${yyyy}-${mm}-${dd}`;
}

document.getElementById('start_date').addEventListener('change', calcEndDate);
document.getElementById('duration').addEventListener('change', calcEndDate);
</script>
</body>
</html>