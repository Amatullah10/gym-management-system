<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">
  <!-- Branding -->
  <div class="brand">
    <i class="fas fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p>Admin</p>
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
          <i class="fas fa-users"></i><span>Members</span>
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

      <li><a href="staff.php"><i class="fas fa-user-cog"></i><span>Staff</span></a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
      <li><a href="payments.php"><i class="fas fa-credit-card"></i><span>Payments</span></a></li>
      <li><a href="equipment.php"><i class="fas fa-dumbbell"></i><span>Equipment</span></a></li>
      <li><a href="reports.php"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
      <li><a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>
  </div>

  <!-- Logout -->
  <div class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </div>
</aside>
<!-- ======== SIDEBAR END ======== -->
