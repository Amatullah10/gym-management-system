<?php
session_start();

/* TEMP: simulate logged-in user */
$_SESSION['role'] = 'admin';
$page = 'member-entry'; // For active sidebar highlight
?>

<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">

  <div class="main-content">
    <div class="form-container mx-auto" style="max-width:900px;">

      <h2>Member Registration</h2>

      <form method="POST">

        <!-- ========== PERSONAL INFORMATION ========== -->
        <div class="section">
          <h3>Personal Information</h3>
          <p class="section-subtitle">Basic details about the member</p>

          <div class="form-row">
            <div>
              <label>Full Name *</label>
              <input type="text" name="full_name" required>
            </div>

            <div>
              <label>Email *</label>
              <input type="email" name="email" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Phone Number *</label>
              <input type="text" name="phone" required>
            </div>

            <div>
              <label>Date of Birth *</label>
              <input type="date" name="dob" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Gender *</label>
              <select name="gender" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
          </div>

          <label>Address *</label>
          <textarea name="address" required></textarea>
        </div>

        <!-- ========== MEMBERSHIP DETAILS ========== -->
        <div class="section">
          <h3>Membership Details</h3>
          <p class="section-subtitle">Select membership type and duration</p>

          <div class="form-row">
            <div>
              <label>Membership Type *</label>
              <select name="membership_type" required>
                <option>Basic - 799/month</option>
                <option>Standard - 999/month</option>
                <option>Premium - 1299/month</option>
              </select>
            </div>

            <div>
              <label>Start Date *</label>
              <input type="date" name="start_date" required>
            </div>

            <div>
              <label>Duration *</label>
              <select name="duration" required>
                <option>1 Month</option>
                <option>3 Months</option>
                <option>6 Months</option>
                <option>12 Months</option>
              </select>
            </div>
          </div>

          <label>End Date *</label>
          <input type="date" name="end_date" required>
        </div>

        <!-- ========== FITNESS INFO ========== -->
        <div class="section">
          <h3>Fitness Information</h3>
          <p class="section-subtitle">Help us understand your health and fitness goals</p>

          <div class="form-row">
            <div>
              <label>Current Fitness Level *</label>
              <select name="fitness_level" required>
                <option>Beginner</option>
                <option>Medium</option>
                <option>Advanced</option>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
