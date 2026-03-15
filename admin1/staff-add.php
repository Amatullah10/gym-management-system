<?php
session_start();
require_once '../dbcon.php';

/* TEMP: simulate logged-in user */
$_SESSION['role'] = 'admin';
$page = 'staff-add'; // For active sidebar highlight

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name    = mysqli_real_escape_string($conn, $_POST['full_name']);
    $role         = mysqli_real_escape_string($conn, $_POST['role']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $phone        = mysqli_real_escape_string($conn, $_POST['phone']);
    $join_date    = mysqli_real_escape_string($conn, $_POST['join_date']);
    $salary       = mysqli_real_escape_string($conn, $_POST['salary']);
    $address      = mysqli_real_escape_string($conn, $_POST['address']);
    $skills       = mysqli_real_escape_string($conn, $_POST['skills']);
    $experience   = mysqli_real_escape_string($conn, $_POST['experience']);
    $emergency    = mysqli_real_escape_string($conn, $_POST['emergency_contact']);

    // Check if email already exists
    $check_email = "SELECT id FROM staff WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $error_message = "Email already exists! Please use a different email.";
    } else {
        $sql = "INSERT INTO staff (full_name, role, email, phone, join_date, salary, address, skills, experience, emergency_contact, status)
                VALUES ('$full_name', '$role', '$email', '$phone', '$join_date', '$salary', '$address', '$skills', '$experience', '$emergency', 'Active')";

        if (mysqli_query($conn, $sql)) {
            // Create user login based on role
            $check_user = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
            if (mysqli_num_rows($check_user) === 0) {
                if ($role === 'trainer') {
                    // Trainer: create account with empty password, send email to set it
                    mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('trainer', '$email', '')");
                    require_once '../auth/mailer.php';
                    $sent = sendSetPasswordEmail($email, $full_name, $conn);
                    $success_message = $sent
                        ? "Trainer added! Set-password email sent to <strong>$email</strong>."
                        : "Trainer added! Could not send email. Share set-password link manually.";
                } elseif (in_array($role, ['accountant', 'receptionist'])) {
                    // Accountant/Receptionist: admin sets password, store hashed
                    $plain_pass = $_POST['password'] ?? '';
                    if (empty($plain_pass)) {
                        $error_message = "Please set a password for $role.";
                    } else {
                        $hashed = password_hash($plain_pass, PASSWORD_BCRYPT);
                        $hashed_safe = mysqli_real_escape_string($conn, $hashed);
                        mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('$role', '$email', '$hashed_safe')");
                        $success_message = "Staff member added! Login: <strong>$email</strong> / <strong>$plain_pass</strong>";
                    }
                } else {
                    // Other roles (maintenance etc) — no login
                    $success_message = "Staff member added successfully!";
                }
            }
            if ($success_message) header("refresh:3;url=staff-list.php");
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
  <title>Add New Staff - Gym Management</title>

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
    <div class="form-container mx-auto" style="max-width: 900px;">

      <h2><i class="fas fa-user-plus me-2"></i>Add New Staff Member</h2>

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

        <!-- ========== BASIC INFORMATION ========== -->
        <div class="section">
          <h3>Basic Information</h3>
          <p class="section-subtitle">Personal and contact details of the staff member</p>

          <div class="form-row">
            <div>
              <label>Full Name *</label>
              <input type="text" name="full_name" placeholder="Enter full name" required>
            </div>

            <div>
              <label>Role *</label>
              <select name="role" required>
                <option value="">Select Role</option>
                <option value="trainer">Trainer</option>
                <option value="receptionist">Receptionist</option>
                <option value="accountant">Accountant</option>
                <option value="manager">Manager</option>
                <option value="maintenance">Maintenance</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Email *</label>
              <input type="email" name="email" placeholder="example@email.com" required>
            </div>

            <div>
              <label>Phone *</label>
              <input type="text" name="phone" placeholder="Enter phone number" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Join Date *</label>
              <input type="date" name="join_date" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div>
              <label>Monthly Salary (₹)</label>
              <input type="number" name="salary" placeholder="e.g., 25000">
            </div>
          </div>

          <label>Address</label>
          <textarea name="address" placeholder="Full address..."></textarea>
        </div>

        <!-- ========== PROFESSIONAL DETAILS ========== -->
        <div class="section">
          <h3>Professional Details</h3>
          <p class="section-subtitle">Skills, experience and emergency information</p>

          <div class="form-row">
            <div>
              <label>Specialization / Skills</label>
              <textarea name="skills" placeholder="e.g., Weight Training, Cardio, Customer Service, Accounting..."></textarea>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Experience</label>
              <input type="text" name="experience" placeholder="e.g., 3 years, 6 months">
            </div>

            <div>
              <label>Emergency Contact</label>
              <input type="text" name="emergency_contact" placeholder="Emergency contact number">
            </div>
          </div>
        </div>

        <!-- ========== LOGIN / PASSWORD ========== -->
        <div class="section" id="passwordSection" style="display:none;">
          <h3><i class="fas fa-lock"></i> Login Password</h3>
          <p class="section-subtitle">Set a login password for this staff member</p>
          <div class="form-row">
            <div>
              <label>Password *</label>
              <input type="password" name="password" id="passwordInput" placeholder="Set login password">
            </div>
          </div>
        </div>

        <div class="section" id="trainerEmailInfo" style="display:none;">
          <div class="app-alert app-alert-warning" style="margin:0;">
            <i class="fas fa-info-circle"></i> A set-password email will be automatically sent to the trainer's email address.
          </div>
        </div>

        <!-- ========== BUTTONS ========== -->
        <div class="d-flex gap-3">
          <button type="submit" class="btn app-btn-primary w-100">
            <i class="fas fa-user-plus me-2"></i>Add Staff Member
          </button>
          <a href="staff-list.php" class="btn app-btn-secondary w-100 text-center text-decoration-none">
            <i class="fas fa-arrow-left me-2"></i>Back to List
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show/hide password field based on role
document.querySelector('select[name="role"]').addEventListener('change', function() {
    const role = this.value;
    const pwSection = document.getElementById('passwordSection');
    const trainerInfo = document.getElementById('trainerEmailInfo');
    const pwInput = document.getElementById('passwordInput');

    if (role === 'accountant' || role === 'receptionist') {
        pwSection.style.display = 'block';
        trainerInfo.style.display = 'none';
        pwInput.required = true;
    } else if (role === 'trainer') {
        pwSection.style.display = 'none';
        trainerInfo.style.display = 'block';
        pwInput.required = false;
    } else {
        pwSection.style.display = 'none';
        trainerInfo.style.display = 'none';
        pwInput.required = false;
    }
});
</script>
</body>
</html>