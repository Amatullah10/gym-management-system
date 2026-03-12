<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

$page = 'add-member';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name       = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email           = mysqli_real_escape_string($conn, $_POST['email']);
    $phone           = mysqli_real_escape_string($conn, $_POST['phone']);
    $dob             = $_POST['dob'];
    $gender          = $_POST['gender'];
    $address         = mysqli_real_escape_string($conn, $_POST['address']);
    $membership_type = $_POST['membership_type'];
    $start_date      = $_POST['start_date'];
    $end_date        = $_POST['end_date'];
    $duration        = mysqli_real_escape_string($conn, $_POST['duration']);
    $fitness_level   = $_POST['fitness_level'];
    $membership_status = 'Active';

    // Auto generate password from email
    $email_name = explode('@', $email)[0];
    $password   = $email_name . '123';

    // Check duplicate
    $check_member = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
    $check_user   = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

    if (mysqli_num_rows($check_member) > 0 || mysqli_num_rows($check_user) > 0) {
        $error = "A member with this email already exists!";
    } else {
        // Step 1: Insert into members table
        $insert_member = mysqli_query($conn, "INSERT INTO members 
            (full_name, email, phone, dob, gender, address, membership_type, start_date, end_date, duration, membership_status, fitness_level) 
            VALUES ('$full_name', '$email', '$phone', '$dob', '$gender', '$address', '$membership_type', '$start_date', '$end_date', '$duration', '$membership_status', '$fitness_level')");

        if ($insert_member) {
            // Step 2: Insert into users table
            $insert_user = mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('', '$email', '$password')");
            if ($insert_user) {
                $success = "Member registered successfully!<br>
                    <strong>Login Email:</strong> $email<br>
                    <strong>Auto Password:</strong> $password<br>
                    <small style='color:#555;'>Please inform the member of these credentials.</small>";
            } else {
                $error = "Member added but login failed! Error: " . mysqli_error($conn);
            }
        } else {
            $error = "Failed to register member! Error: " . mysqli_error($conn);
        }
    }
}
?>
<?php include '../layout/header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Add New Member</h1>
        <p class="page-subtitle">Register a new gym member — login account will be created automatically</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="app-alert app-alert-warning">
      <i class="fa-solid fa-circle-info"></i>
      <strong>Auto Password:</strong> Generated from email automatically.
      Example: <strong>rahul@gmail.com</strong> → Password: <strong>rahul123</strong>
    </div>

    <div class="form-container">
      <form method="POST">

        <div class="section">
          <h3>Personal Information</h3>
          <p class="section-subtitle">Enter the member's personal details</p>
          <div class="form-row">
            <div>
              <label>Full Name</label>
              <input type="text" name="full_name" placeholder="e.g. Rahul Sharma" required>
            </div>
            <div>
              <label>Email Address</label>
              <input type="email" name="email" id="email_input" placeholder="e.g. rahul@gmail.com" required>
              <small style="color:#999; font-size:12px; margin-top:5px; display:block;">
                Password will be: <strong id="password_preview">-</strong>
              </small>
            </div>
          </div>
          <div class="form-row">
            <div><label>Phone Number</label><input type="text" name="phone" placeholder="e.g. 9876543210" required></div>
            <div><label>Date of Birth</label><input type="date" name="dob" required></div>
          </div>
          <div class="form-row">
            <div>
              <label>Gender</label>
              <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label>Fitness Level</label>
              <select name="fitness_level" required>
                <option value="">Select Fitness Level</option>
                <option value="Beginner">Beginner</option>
                <option value="Medium">Medium</option>
                <option value="Advanced">Advanced</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div><label>Address</label><textarea name="address" placeholder="Enter full address"></textarea></div>
          </div>
        </div>

        <div class="section">
          <h3>Membership Information</h3>
          <p class="section-subtitle">Set the membership plan and duration</p>
          <div class="form-row">
            <div>
              <label>Membership Type</label>
              <select name="membership_type" required>
                <option value="">Select Plan</option>
                <option value="Basic - 799/month">Basic - ₹799/month</option>
                <option value="Standard - 999/month">Standard - ₹999/month</option>
                <option value="Premium - 1299/month">Premium - ₹1299/month</option>
              </select>
            </div>
            <div>
              <label>Duration</label>
              <select name="duration" id="duration_select" required>
                <option value="">Select Duration</option>
                <option value="1 Month">1 Month</option>
                <option value="3 Months">3 Months</option>
                <option value="6 Months">6 Months</option>
                <option value="12 Months">12 Months</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div><label>Start Date</label><input type="date" name="start_date" id="start_date" value="<?= date('Y-m-d') ?>" required></div>
            <div><label>End Date</label><input type="date" name="end_date" id="end_date" required></div>
          </div>
        </div>

        <div style="display:flex; gap:15px; margin-top:10px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-user-plus"></i> Register Member</button>
          <a href="index.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Live password preview
  document.getElementById('email_input').addEventListener('input', function() {
    var email = this.value;
    var preview = document.getElementById('password_preview');
    if (email.includes('@')) {
      preview.textContent = email.split('@')[0] + '123';
      preview.style.color = '#2e7d32';
    } else if (email.length > 0) {
      preview.textContent = email + '123';
      preview.style.color = '#f57c00';
    } else {
      preview.textContent = '-';
      preview.style.color = '#999';
    }
  });

  // Auto calculate end date
  function calculateEndDate() {
    var startDate = document.getElementById('start_date').value;
    var duration  = document.getElementById('duration_select').value;
    if (!startDate || !duration) return;
    var start = new Date(startDate);
    if (duration == '1 Month')   start.setMonth(start.getMonth() + 1);
    else if (duration == '3 Months') start.setMonth(start.getMonth() + 3);
    else if (duration == '6 Months') start.setMonth(start.getMonth() + 6);
    else if (duration == '12 Months') start.setMonth(start.getMonth() + 12);
    var y = start.getFullYear();
    var m = String(start.getMonth() + 1).padStart(2, '0');
    var d = String(start.getDate()).padStart(2, '0');
    document.getElementById('end_date').value = y + '-' + m + '-' + d;
  }
  document.getElementById('duration_select').addEventListener('change', calculateEndDate);
  document.getElementById('start_date').addEventListener('change', calculateEndDate);
</script>
</body>
</html>