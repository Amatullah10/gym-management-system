<?php
// Get user role from session
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Define menu items for each role
$menus = [
    'admin' => [
        'dashboard' => true,
        'members' => true,
        'trainers' => true,
        'attendance' => true,
        'announcements' => true,
        'payments' => true,
        'equipment' => true,
        'reports' => true,
        'settings' => true
    ],
    'trainer' => [
        'dashboard' => true,
        'assigned_members' => true,
        'attendance' => true,
        'announcements' => true,
        'reports' => false
    ],
    'receptionist' => [
        'dashboard' => true,
        'members' => true,
        'attendance' => true,
        'announcements' => true
    ],
    'accountant' => [
        'dashboard' => true,
        'payments' => true,
        'reports' => true,
        'members' => true
    ],
    'customer' => [
        'dashboard' => true,
        'my_profile' => true,
        'my_attendance' => true,
        'my_workout' => true,
        'announcements' => true
    ]
];

// Get current user menu permissions
$user_menu = isset($menus[$user_role]) ? $menus[$user_role] : $menus['guest'];
?>

<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">
  <!-- Branding -->
  <div class="brand">
    <i class="fas fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p><?= ucfirst($user_role) ?></p>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">
      
      <!-- Dashboard - Available to all -->
      <?php if (isset($user_menu['dashboard']) && $user_menu['dashboard']): ?>
      <li class="<?php if($page=='dashboard'){ echo 'active'; }?>">
        <a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      </li>
      <?php endif; ?>

      <!-- Members Menu - Admin, Receptionist, Accountant -->
      <?php if (isset($user_menu['members']) && $user_menu['members']): ?>
      <li class="submenu">
        <input type="checkbox" id="members-menu" class="toggle-input">
        <label for="members-menu" class="submenu-label">
          <i class="fas fa-users"></i><span>Members</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='members'){ echo 'active'; }?>">
            <a href="members.php"><i class="fas fa-arrow-right"></i> List All Members</a>
          </li>
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='members-entry'){ echo 'active'; }?>">
            <a href="member-entry.php"><i class="fas fa-arrow-right"></i> Member Entry Form</a>
          </li>
          <li class="<?php if($page=='members-remove'){ echo 'active'; }?>">
            <a href="remove-member.php"><i class="fas fa-arrow-right"></i> Remove Member</a>
          </li>
          <li class="<?php if($page=='members-update'){ echo 'active'; }?>">
            <a href="update-member.php"><i class="fas fa-arrow-right"></i> Update Member Details</a>
          </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Trainers Menu - Admin Only -->
      <?php if (isset($user_menu['trainers']) && $user_menu['trainers']): ?>
      <li class="submenu">
        <input type="checkbox" id="trainers-menu" class="toggle-input">
        <label for="trainers-menu" class="submenu-label">
          <i class="fas fa-user-tie"></i><span>Trainers</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='trainers-list'){ echo 'active'; }?>">
            <a href="trainers-list.php"><i class="fas fa-arrow-right"></i> All Trainers</a>
          </li>
          <li class="<?php if($page=='trainer-add'){ echo 'active'; }?>">
            <a href="trainer-add.php"><i class="fas fa-arrow-right"></i> Add Trainer</a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Assigned Members - Trainer Only -->
      <?php if (isset($user_menu['assigned_members']) && $user_menu['assigned_members']): ?>
      <li class="<?php if($page=='assigned-members'){ echo 'active'; }?>">
        <a href="assigned-member.php">
          <i class="fas fa-users-cog"></i><span>Assigned Members</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Attendance Menu - Admin, Trainer, Receptionist -->
      <?php if (isset($user_menu['attendance']) && $user_menu['attendance']): ?>
      <li class="submenu">
        <input type="checkbox" id="attendance-menu" class="toggle-input">
        <label for="attendance-menu" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='mark-attendance'){ echo 'active'; }?>">
            <a href="mark-attendance.php"><i class="fas fa-arrow-right"></i> Mark Attendance</a>
          </li>
          <li class="<?php if($page=='view-attendance'){ echo 'active'; }?>">
            <a href="view-attendance.php"><i class="fas fa-arrow-right"></i> View Attendance</a>
          </li>
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='attendance-report'){ echo 'active'; }?>">
            <a href="attendance-report.php"><i class="fas fa-arrow-right"></i> Attendance Reports</a>
          </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Announcements Menu - Admin, Trainer, Receptionist -->
      <?php if (isset($user_menu['announcements']) && $user_menu['announcements']): ?>
      <li class="submenu">
        <input type="checkbox" id="announcements-menu" class="toggle-input">
        <label for="announcements-menu" class="submenu-label">
          <i class="fas fa-bullhorn"></i><span>Announcements</span>
        </label>
        <ul class="submenu-items">
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='post-announcements'){ echo 'active'; }?>">
            <a href="post-announcements.php"><i class="fas fa-arrow-right"></i> Post Announcements</a>
          </li>
          <?php endif; ?>
          <li class="<?php if($page=='view-announcements'){ echo 'active'; }?>">
            <a href="view-announcements.php"><i class="fas fa-arrow-right"></i> View Announcements</a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Payments Menu - Admin, Accountant -->
      <?php if (isset($user_menu['payments']) && $user_menu['payments']): ?>
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
      <?php endif; ?>

      <!-- Equipment Menu - Admin Only -->
      <?php if (isset($user_menu['equipment']) && $user_menu['equipment']): ?>
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
      <?php endif; ?>

      <!-- Customer/Member Specific Pages -->
      <?php if (isset($user_menu['my_profile']) && $user_menu['my_profile']): ?>
      <li class="<?php if($page=='my-profile'){ echo 'active'; }?>">
        <a href="my-profile.php"><i class="fas fa-user"></i><span>My Profile</span></a>
      </li>
      <?php endif; ?>

      <?php if (isset($user_menu['my_attendance']) && $user_menu['my_attendance']): ?>
      <li class="<?php if($page=='my-attendance'){ echo 'active'; }?>">
        <a href="my-attendance.php"><i class="fas fa-calendar-check"></i><span>My Attendance</span></a>
      </li>
      <?php endif; ?>

      <?php if (isset($user_menu['my_workout']) && $user_menu['my_workout']): ?>
      <li class="<?php if($page=='my-workout'){ echo 'active'; }?>">
        <a href="my-workout.php"><i class="fas fa-dumbbell"></i><span>My Workout Plan</span></a>
      </li>
      <?php endif; ?>

      <!-- Reports - Admin, Accountant -->
      <?php if (isset($user_menu['reports']) && $user_menu['reports']): ?>
      <li class="<?php if($page=='reports'){ echo 'active'; }?>">
        <a href="reports.php"><i class="fas fa-chart-line"></i><span>Reports</span></a>
      </li>
      <?php endif; ?>

      <!-- Settings - Admin Only -->
      <?php if (isset($user_menu['settings']) && $user_menu['settings']): ?>
      <li class="<?php if($page=='settings'){ echo 'active'; }?>">
        <a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a>
      </li>
      <?php endif; ?>

    </ul>
  </div>

  <!-- Logout -->
   <a href="../auth/logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>
</aside>
<!-- ======== SIDEBAR END ======== -->