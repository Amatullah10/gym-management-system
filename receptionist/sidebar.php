<?php
// receptionist/sidebar.php
?>
<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">
  <!-- Branding -->
  <div class="brand">
    <i class="fas fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p>Receptionist</p>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">

      <!-- Dashboard -->
      <li class="<?php if($page=='dashboard'){ echo 'active'; }?>">
        <a href="index.php">
          <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
      </li>

      <!-- Attendance Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="attendance-menu" class="toggle-input"
          <?php if(in_array($page, ['mark-attendance','view-attendance'])) echo 'checked'; ?>>
        <label for="attendance-menu" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='mark-attendance'){ echo 'active'; }?>">
            <a href="mark_attendance.php"><i class="fas fa-arrow-right"></i> Mark Attendance</a>
          </li>
          <li class="<?php if($page=='view-attendance'){ echo 'active'; }?>">
            <a href="view_attendance.php"><i class="fas fa-arrow-right"></i> View Attendance</a>
          </li>
        </ul>
      </li>

      <!-- Members Dropdown -->
      <li class="submenu">
        <input type="checkbox" id="members-menu" class="toggle-input"
          <?php if(in_array($page, ['add-member','member-lookup'])) echo 'checked'; ?>>
        <label for="members-menu" class="submenu-label">
          <i class="fas fa-users"></i><span>Members</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='add-member'){ echo 'active'; }?>">
            <a href="add_member.php"><i class="fas fa-arrow-right"></i> Add Member</a>
          </li>
          <li class="<?php if($page=='member-lookup'){ echo 'active'; }?>">
            <a href="member_lookup.php"><i class="fas fa-arrow-right"></i> Member Lookup</a>
          </li>
        </ul>
      </li>

      <!-- Announcements -->
      <li class="<?php if($page=='announcements'){ echo 'active'; }?>">
        <a href="view_announcements.php">
          <i class="fas fa-bullhorn"></i><span>Announcements</span>
        </a>
      </li>

    </ul>
  </div>

  <!-- Logout -->
  <a href="../auth/logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>
</aside>
<!-- ======== SIDEBAR END ======== -->