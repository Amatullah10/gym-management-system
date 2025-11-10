<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">
  <!-- Branding -->
  <div class="brand">
    <i class="fas fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p>Trainer</p>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">
      <li class="<?php if($page=='dashboard'){ echo 'active'; }?>">
        <a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      </li>

      <!-- Members Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="members-menu" class="toggle-input">
        <label for="members-menu" class="submenu-label">
          <i class="fas fa-users"></i><span>My Members  </span>
        </label>

        <ul class="submenu-items">
          <li class="<?php if($page=='members'){ echo 'active'; }?>">
            <a href="members.php"><i class="fas fa-arrow-right"></i> List All Members</a>
          </li>
          <li class="<?php if($page=='members-entry'){ echo 'active'; }?>">
            <a href="member-entry.php"><i class="fas fa-arrow-right"></i> Member Entry Form</a>
          </li>
          <li class="<?php if($page=='members-remove'){ echo 'active'; }?>">
            <a href="remove-member.php"><i class="fas fa-arrow-right"></i> Remove Member</a>
          </li>
          <li class="<?php if($page=='members-update'){ echo 'active'; }?>">
            <a href="edit-member.php"><i class="fas fa-arrow-right"></i> Update Member Details</a>
          </li>
        </ul>
      </li>

     <li class="submenu">
  <input type="checkbox" id="progress-menu" class="toggle-input">
  <label for="progress-menu" class="submenu-label">
    <i class="fas fa-chart-line"></i><span>Progress</span>
  </label>
  <ul class="submenu-items">
    <li class="<?php if($page=='weight'){ echo 'active'; }?>">
      <a href="weight.php"><i class="fas fa-arrow-right"></i> Weight</a>
    </li>
    <li class="<?php if($page=='bmi'){ echo 'active'; }?>">
      <a href="bmi.php"><i class="fas fa-arrow-right"></i> BMI </a>
    </li>
    <li class="<?php if($page=='performance'){ echo 'active'; }?>">
      <a href="performance.php"><i class="fas fa-arrow-right"></i> Performance </a>
    </li>
  </ul>
</li>
      
      <li class="submenu">
  <input type="checkbox" id="attendance-menu" class="toggle-input">
  <label for="attendance-menu" class="submenu-label">
    <i class="fas fa-calendar-check"></i><span>Attendance</span>
  </label>
  <ul class="submenu-items">
    <li class="<?php if($page=='member-attendance'){ echo 'active'; }?>">
      <a href="member-attendance.php"><i class="fas fa-arrow-right"></i> Member Attendance</a>
    </li>
  <li class="<?php if($page=='attendance-report'){ echo 'active'; }?>">
      <a href="attendance-report.php"><i class="fas fa-arrow-right"></i> Attendance Reports</a>
    </li>
  </ul>
</li>
<!--
<li class="submenu">
  <input type="checkbox" id="payments-menu" class="toggle-input">
  <label for="payments-menu" class="submenu-label">
    <i class="fas fa-credit-card"></i><span>Payments</span>
  </label>
  <ul class="submenu-items">
    <li class="<?php if($page=='payment-list'){ echo 'active'; }?>">
      <a href="payment-list.php"><i class="fas fa-arrow-right"></i> All Payment Records</a>
    </li>
    <li class="<?php if($page=='payment-add'){ echo 'active'; }?>">
      <a href="payment-add.php"><i class="fas fa-arrow-right"></i> Add Payment</a>
    </li>
    <li class="<?php if($page=='payment-due'){ echo 'active'; }?>">
      <a href="payment-due.php"><i class="fas fa-arrow-right"></i> Due Payments</a>
    </li>
    <li class="<?php if($page=='payment-report'){ echo 'active'; }?>">
      <a href="payment-report.php"><i class="fas fa-arrow-right"></i> Payment Reports</a>
    </li>
  </ul>
</li>
     <li class="submenu">
  <input type="checkbox" id="equipment-menu" class="toggle-input">
  <label for="equipment-menu" class="submenu-label">
    <i class="fas fa-dumbbell"></i><span>Equipment</span>
  </label>
  <ul class="submenu-items">
    <li class="<?php if($page=='equipment-list'){ echo 'active'; }?>">
      <a href="equipment-list.php"><i class="fas fa-arrow-right"></i> List All Equipment</a>
    </li>
    <li class="<?php if($page=='equipment-add'){ echo 'active'; }?>">
      <a href="equipment-add.php"><i class="fas fa-arrow-right"></i> Add Equipment</a>
    </li>
    <li class="<?php if($page=='equipment-maintenance'){ echo 'active'; }?>">
      <a href="equipment-maintenance.php"><i class="fas fa-arrow-right"></i> Maintenance Schedule</a>
    </li>
    <li class="<?php if($page=='equipment-report'){ echo 'active'; }?>">
      <a href="equipment-report.php"><i class="fas fa-arrow-right"></i> Equipment Reports</a>
    </li>
  </ul>
</li>
      <li><a href="announcement.php"><i class="fas fa-bullhorn"></i><span>Announcements</span></a></li>
      <li><a href="reports.php"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
      <li><a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>
  </div>
  -->

  <!-- Logout -->
    <a href="../logout.php" class="logout">
  <i class="fas fa-sign-out-alt"></i><span>Logout</span>
   </a>
</aside>
<!-- ======== SIDEBAR END ======== -->
