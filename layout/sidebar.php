<?php
// Get user role from session
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';



// ── Base path per role ──
if ($user_role == 'admin') {
    $base = '../admin1/';
} elseif ($user_role == 'receptionist') {
    $base = '../receptionist/';
} elseif ($user_role == 'trainer') {
    $base = '../trainer/';
} elseif ($user_role == 'accountant') {
    $base = '../accountant/';
} elseif ($user_role == 'customer') {
    $base = '../customer/';
} else {
    $base = '../';
}

// ── Define menu permissions per role ──
$menus = [
    'admin' => [
        'dashboard'       => true,
        'members'         => true,
        'staff'           => true,
        'attendance'      => true,
        'announcements'   => true,
        'payments'        => true,
        'equipment'       => true,
        'reports'         => true,
        'settings'        => true
    ],
    'trainer' => [
        'dashboard'        => true,
        'assigned_members' => true,
        'attendance'       => true,
        'announcements'    => true,
        'reports'          => false
    ],
    'receptionist' => [
        'dashboard'      => true,
        'members'        => true,
        'attendance'     => true,
        'announcements'  => true
    ],
    'accountant' => [
        'dashboard'     => true,
        'payments'      => true,
        'reports'       => true,
        'members'       => true
    ],
    'customer' => [
        'dashboard'     => true,
        'my_profile'    => true,
        'my_attendance' => true,
        'my_workout'    => true,
        'my_payments'   => true,
        'my_progress'   => true,
        'announcements' => true
    ]
];

// ── Get menu for current role (MUST be after $menus is defined) ──
$user_menu = $menus[$user_role] ?? [];
?>

<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar">

  <!-- Branding -->
  <div class="brand">
    <img src="../assets/logo.png" alt="NextGen Fitness" style="width:70px;height:70px;object-fit:contain;border-radius:8px;">
    <div class="brand-text">
      <h2>NextGen Fitness</h2>
      <p><?= ucfirst($user_role) ?></p>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">

      <!-- ── Dashboard ── -->
      <?php if (!empty($user_menu['dashboard'])): ?>
      <?php
        $dashboard_file = ($user_role === 'admin') ? 'dashboard1.php' : 'index.php';
      ?>
      <li class="<?php if($page=='dashboard') echo 'active'; ?>">
        <a href="<?= $base . $dashboard_file ?>">
          <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Members — Admin, Receptionist, Accountant ── -->
      <?php if (!empty($user_menu['members'])): ?>
      <li class="submenu">
        <input type="checkbox" id="members-menu" class="toggle-input">
        <label for="members-menu" class="submenu-label">
          <i class="fas fa-users"></i><span>Members</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='members') echo 'active'; ?>">
            <a href="../modules/members.php">
              <i class="fas fa-arrow-right"></i> List All Members
            </a>
          </li>
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='members-entry') echo 'active'; ?>">
            <a href="../modules/member-entry.php">
              <i class="fas fa-arrow-right"></i> Member Entry Form
            </a>
          </li>
         
          
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Manage Staff — Admin Only ── -->
      <?php if (!empty($user_menu['staff'])): ?>
      <li class="submenu">
        <input type="checkbox" id="staff-menu" class="toggle-input">
        <label for="staff-menu" class="submenu-label">
          <i class="fas fa-user-tie"></i><span>Manage Staff</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='staff-list') echo 'active'; ?>">
            <a href="<?= $base ?>staff-list.php">
              <i class="fas fa-arrow-right"></i> View All Staff
            </a>
          </li>
          <li class="<?php if($page=='staff-add') echo 'active'; ?>">
            <a href="<?= $base ?>staff-add.php">
              <i class="fas fa-arrow-right"></i> Add Staff
            </a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Assigned Members — Trainer Only ── -->
      <?php if (!empty($user_menu['assigned_members'])): ?>
      <li class="<?php if($page=='assigned-members') echo 'active'; ?>">
        <a href="<?= $base ?>assigned-member.php">
          <i class="fas fa-users-cog"></i><span>Assigned Members</span>
        </a>
      </li>
      <li class="<?php if($page=='workout-plans') echo 'active'; ?>">
        <a href="<?= $base ?>workout-plans.php">
          <i class="fas fa-dumbbell"></i><span>Workout Plans</span>
        </a>
      </li>
      <li class="<?php if($page=='schedule') echo 'active'; ?>">
        <a href="<?= $base ?>schedule.php">
          <i class="fas fa-calendar-alt"></i><span>Schedule</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Attendance — Admin, Trainer, Receptionist ── -->
      <?php if (!empty($user_menu['attendance'])): ?>
      <li class="submenu">
        <input type="checkbox" id="attendance-menu" class="toggle-input">
        <label for="attendance-menu" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='mark-attendance') echo 'active'; ?>">
            <a href="../modules/mark-attendance.php">
              <i class="fas fa-arrow-right"></i> Mark Attendance
            </a>
          </li>
          <li class="<?php if($page=='view-attendance') echo 'active'; ?>">
            <a href="../modules/view-attendance.php">
              <i class="fas fa-arrow-right"></i> View Attendance
            </a>
          </li>
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='attendance-report') echo 'active'; ?>">
            <a href="../modules/attendance-report.php">
              <i class="fas fa-arrow-right"></i> Attendance Reports
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Announcements — All roles, single link to module ── -->
      <?php if (!empty($user_menu['announcements'])): ?>
      <li class="<?php if($page=='announcements') echo 'active'; ?>">
        <a href="../modules/announcements.php">
          <i class="fas fa-bullhorn"></i><span>Announcements</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Payments — Admin, Accountant ── -->
      <?php if (!empty($user_menu['payments'])): ?>
      <li class="submenu">
        <input type="checkbox" id="payments-menu" class="toggle-input">
        <label for="payments-menu" class="submenu-label">
          <i class="fas fa-credit-card"></i><span>Payments</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='payments') echo 'active'; ?>">
            <a href="../modules/payments.php">
              <i class="fas fa-arrow-right"></i> All Payment Records
            </a>
          </li>
          
          
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Equipment — Admin Only ── -->
      <?php if (!empty($user_menu['equipment'])): ?>
      <li class="submenu">
        <input type="checkbox" id="equipment-menu" class="toggle-input">
        <label for="equipment-menu" class="submenu-label">
          <i class="fas fa-dumbbell"></i><span>Equipment</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='equipment-list') echo 'active'; ?>">
            <a href="<?= $base ?>equipment-list.php">
              <i class="fas fa-arrow-right"></i> List All Equipment
            </a>
          </li>
          <li class="<?php if($page=='equipment-add') echo 'active'; ?>">
            <a href="<?= $base ?>equipment-add.php">
              <i class="fas fa-arrow-right"></i> Add Equipment
            </a>
          </li>
          <li class="<?php if($page=='equipment-report') echo 'active'; ?>">
            <a href="<?= $base ?>equipment-report.php">
              <i class="fas fa-arrow-right"></i> Equipment Reports
            </a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Customer Specific Pages ── -->
      <?php if (!empty($user_menu['my_profile'])): ?>
      <li class="<?php if($page=='my-profile') echo 'active'; ?>">
        <a href="<?= $base ?>my-profile.php">
          <i class="fas fa-user"></i><span>My Profile</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if (!empty($user_menu['my_attendance'])): ?>
      <li class="submenu">
        <input type="checkbox" id="attendance-customer" class="toggle-input"
          <?php if(in_array($page,['checkin','view-attendance'])) echo 'checked'; ?>>
        <label for="attendance-customer" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='checkin') echo 'active'; ?>">
            <a href="<?= $base ?>checkin.php"><i class="fas fa-arrow-right"></i> Check In</a>
          </li>
          <li class="<?php if($page=='view-attendance') echo 'active'; ?>">
            <a href="<?= $base ?>view-attendance.php"><i class="fas fa-arrow-right"></i> View Attendance</a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <?php if (!empty($user_menu['my_workout'])): ?>
      <li class="<?php if($page=='my-workout') echo 'active'; ?>">
        <a href="<?= $base ?>my-workout.php">
          <i class="fas fa-dumbbell"></i><span>My Workout Plan</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if (!empty($user_menu['my_payments'])): ?>
      <li class="<?php if($page=='my-payments') echo 'active'; ?>">
        <a href="<?= $base ?>my-payments.php">
          <i class="fas fa-receipt"></i><span>My Payments</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if (!empty($user_menu['my_progress'])): ?>
      <li class="<?php if($page=='bmi') echo 'active'; ?>">
        <a href="<?= $base ?>bmi.php">
          <i class="fas fa-chart-line"></i><span>My Progress</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Reports — Admin, Accountant ── -->
      <?php if (!empty($user_menu['reports'])): ?>
      <li class="<?php if($page=='reports') echo 'active'; ?>">
        <a href="<?= $base ?>reports.php">
          <i class="fas fa-chart-line"></i><span>Reports</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Settings — Admin Only ── -->
      <?php if (!empty($user_menu['settings'])): ?>
      <li class="<?php if($page=='settings') echo 'active'; ?>">
        <a href="<?= $base ?>settings.php">
          <i class="fas fa-cog"></i><span>Settings</span>
        </a>
      </li>
      <?php endif; ?>

    </ul>
  </div>

  <!-- Logout — fixed path, never changes -->
  <a href="../auth/logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>

</aside>
<!-- ======== SIDEBAR END ======== -->