<?php
// sidebar.php - no session_start() here
?>
<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">
  <!-- Branding -->
  <div class="brand">
    <i class="fas fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p>Customer</p>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">

      <!-- Dashboard -->
      <li class="<?php if($page=='dashboard'){ echo 'active'; }?>">
        <a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      </li>

      <!-- My Progress Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="my-progress" class="toggle-input">
        <label for="my-progress" class="submenu-label">
          <i class="fas fa-chart-line"></i><span>My Progress</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='weight'){ echo 'active'; }?>">
            <a href="weight.php"><i class="fas fa-arrow-right"></i> Weight</a>
          </li>
          <li class="<?php if($page=='bmi'){ echo 'active'; }?>">
            <a href="bmi.php"><i class="fas fa-arrow-right"></i> BMI</a>
          </li>
          <li class="<?php if($page=='fitness'){ echo 'active'; }?>">
            <a href="fitness.php"><i class="fas fa-arrow-right"></i> Fitness</a>
          </li>
        </ul>
      </li>

      <!-- Attendance Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="attendance-menu" class="toggle-input">
        <label for="attendance-menu" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='view-attendance'){ echo 'active'; }?>">
            <a href="view-attendance.php"><i class="fas fa-arrow-right"></i> View Attendance</a>
          </li>
          <li class="<?php if($page=='attendance-report'){ echo 'active'; }?>">
            <a href="attendance-report.php"><i class="fas fa-arrow-right"></i> Attendance Reports</a>
          </li>
        </ul>
      </li>

      <!-- Payments Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="payments" class="toggle-input">
        <label for="payments" class="submenu-label">
          <i class="fas fa-money-bill-wave"></i><span>Payments</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='due-payments'){ echo 'active'; }?>">
            <a href="due-payments.php"><i class="fas fa-arrow-right"></i> Due Payments</a>
          </li>
          <li class="<?php if($page=='overdue-payments'){ echo 'active'; }?>">
            <a href="overdue-payments.php"><i class="fas fa-arrow-right"></i> Overdue Payments</a>
          </li>
          <li class="<?php if($page=='payment-history'){ echo 'active'; }?>">
            <a href="payment-history.php"><i class="fas fa-arrow-right"></i> Payment History</a>
          </li>
          <li class="<?php if($page=='payment-reminders'){ echo 'active'; }?>">
            <a href="payment-reminders.php"><i class="fas fa-arrow-right"></i> Payment Reminders</a>
          </li>
        </ul>
      </li>

    </ul>
  </div>

  <!-- Logout -->
  <a href="../auth/logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>
</aside>
<!-- ======== SIDEBAR END ======== -->