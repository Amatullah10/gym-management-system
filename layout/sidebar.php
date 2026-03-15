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
        'dashboard'     => true,
        'members'       => true,
        'staff'         => true,
        'attendance'    => true,
        'announcements' => true,
        'payments'      => true,
        'equipment'     => true,
        'reports'       => true,
        'settings'      => true
    ],
    'trainer' => [
        'dashboard'        => true,
        'assigned_members' => true,
        'attendance'       => true,
        'announcements'    => true,
        'reports'          => false
    ],
    'receptionist' => [
        'dashboard'     => true,
        'members'       => true,
        'attendance'    => true,
        'announcements' => true
    ],
    'accountant' => [
        'dashboard'  => true,
        'payments'   => true,
        'members'    => true
    ],
    'customer' => [
        'dashboard'     => true,
        'my_profile'    => true,
        'my_attendance' => true,
        'my_workout'    => true,
        'announcements' => true
    ]
];

// ── Get menu for current role ──
$user_menu = $menus[$user_role] ?? [];
?>

<!-- ======== SIDEBAR START ======== -->
<aside class="sidebar" id="sidebar">

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

      <!-- ── Dashboard ── -->
      <?php if (!empty($user_menu['dashboard'])): ?>
      <li class="<?php if($page=='dashboard') echo 'active'; ?>">
        <a href="<?= $base ?>index.php">
          <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Members — Admin, Receptionist, Accountant ── -->
      <?php if (!empty($user_menu['members'])): ?>
      <li class="submenu <?php if(in_array($page, ['members','members-entry','members-remove','members-update'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="members-menu" class="toggle-input"
          <?php if(in_array($page, ['members','members-entry','members-remove','members-update'])) echo 'checked'; ?>>
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
          <li class="<?php if($page=='members-remove') echo 'active'; ?>">
            <a href="../modules/remove-member.php">
              <i class="fas fa-arrow-right"></i> Remove Member
            </a>
          </li>
          <li class="<?php if($page=='members-update') echo 'active'; ?>">
            <a href="../modules/update-member.php">
              <i class="fas fa-arrow-right"></i> Update Member Details
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Manage Staff — Admin Only ── -->
      <?php if (!empty($user_menu['staff'])): ?>
      <li class="submenu <?php if(in_array($page, ['staff-list','staff-add'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="staff-menu" class="toggle-input"
          <?php if(in_array($page, ['staff-list','staff-add'])) echo 'checked'; ?>>
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
      <li class="submenu <?php if(in_array($page, ['mark-attendance','view-attendance','attendance-report'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="attendance-menu" class="toggle-input"
          <?php if(in_array($page, ['mark-attendance','view-attendance','attendance-report'])) echo 'checked'; ?>>
        <label for="attendance-menu" class="submenu-label">
          <i class="fas fa-calendar-check"></i><span>Attendance</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='mark-attendance') echo 'active'; ?>">
            <a href="<?= $base ?>mark-attendance.php">
              <i class="fas fa-arrow-right"></i> Mark Attendance
            </a>
          </li>
          <li class="<?php if($page=='view-attendance') echo 'active'; ?>">
            <a href="<?= $base ?>view-attendance.php">
              <i class="fas fa-arrow-right"></i> View Attendance
            </a>
          </li>
          <?php if ($user_role == 'admin' || $user_role == 'receptionist'): ?>
          <li class="<?php if($page=='attendance-report') echo 'active'; ?>">
            <a href="<?= $base ?>attendance-report.php">
              <i class="fas fa-arrow-right"></i> Attendance Reports
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Announcements ── -->
      <?php if (!empty($user_menu['announcements'])): ?>
      <li class="<?php if($page=='announcements') echo 'active'; ?>">
        <a href="../modules/announcements.php">
          <i class="fas fa-bullhorn"></i><span>Announcements</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Payments — Admin only (uses modules/) ── -->
      <?php if (!empty($user_menu['payments']) && $user_role == 'admin'): ?>
      <li class="submenu <?php if(in_array($page, ['payments','payment-form','payment-receipt'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="payments-menu" class="toggle-input"
          <?php if(in_array($page, ['payments','payment-form','payment-receipt'])) echo 'checked'; ?>>
        <label for="payments-menu" class="submenu-label">
          <i class="fas fa-credit-card"></i><span>Payments</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='payments') echo 'active'; ?>">
            <a href="../modules/payments.php">
              <i class="fas fa-arrow-right"></i> All Payment Records
            </a>
          </li>
          <li class="<?php if($page=='payment-form') echo 'active'; ?>">
            <a href="../modules/payment-form.php">
              <i class="fas fa-arrow-right"></i> Add Payment
            </a>
          </li>
          <li class="<?php if($page=='payment-receipt') echo 'active'; ?>">
            <a href="../modules/payment-receipt.php">
              <i class="fas fa-arrow-right"></i> Payment Receipt
            </a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Payments — Accountant (uses accountant/) ── -->
      <?php if (!empty($user_menu['payments']) && $user_role == 'accountant'): ?>
      <li class="submenu <?php if(in_array($page, ['payment-list','record-payment','search-payment','due-payments','overdue-payments','payment-report'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="payments-menu" class="toggle-input"
          <?php if(in_array($page, ['payment-list','record-payment','search-payment','due-payments','overdue-payments','payment-report'])) echo 'checked'; ?>>
        <label for="payments-menu" class="submenu-label">
          <i class="fas fa-credit-card"></i><span>Payments</span>
        </label>
        <ul class="submenu-items">
          <li class="<?php if($page=='payment-list') echo 'active'; ?>">
            <a href="<?= $base ?>payment-list.php">
              <i class="fas fa-arrow-right"></i> All Payments
            </a>
          </li>
          <li class="<?php if($page=='record-payment') echo 'active'; ?>">
            <a href="<?= $base ?>record-payment.php">
              <i class="fas fa-arrow-right"></i> Record Payment
            </a>
          </li>
          <li class="<?php if($page=='search-payment') echo 'active'; ?>">
            <a href="<?= $base ?>search-payment.php">
              <i class="fas fa-arrow-right"></i> Search Payment
            </a>
          </li>
          <li class="<?php if($page=='due-payments') echo 'active'; ?>">
            <a href="<?= $base ?>due-payments.php">
              <i class="fas fa-arrow-right"></i> Due Payments
            </a>
          </li>
          <li class="<?php if($page=='overdue-payments') echo 'active'; ?>">
            <a href="<?= $base ?>overdue-payments.php">
              <i class="fas fa-arrow-right"></i> Overdue Payments
            </a>
          </li>
          <li class="<?php if($page=='payment-report') echo 'active'; ?>">
            <a href="<?= $base ?>payment-report.php">
              <i class="fas fa-arrow-right"></i> Payment Report
            </a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- ── Equipment — Admin Only ── -->
      <?php if (!empty($user_menu['equipment'])): ?>
      <li class="submenu <?php if(in_array($page, ['equipment-list','equipment-add','equipment-maintenance','equipment-report'])) echo 'parent-active'; ?>">
        <input type="checkbox" id="equipment-menu" class="toggle-input"
          <?php if(in_array($page, ['equipment-list','equipment-add','equipment-maintenance','equipment-report'])) echo 'checked'; ?>>
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
          <li class="<?php if($page=='equipment-maintenance') echo 'active'; ?>">
            <a href="<?= $base ?>equipment-maintenance.php">
              <i class="fas fa-arrow-right"></i> Maintenance Schedule
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
      <li class="<?php if($page=='my-attendance') echo 'active'; ?>">
        <a href="<?= $base ?>my-attendance.php">
          <i class="fas fa-calendar-check"></i><span>My Attendance</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if (!empty($user_menu['my_workout'])): ?>
      <li class="<?php if($page=='my-workout') echo 'active'; ?>">
        <a href="<?= $base ?>my-workout.php">
          <i class="fas fa-dumbbell"></i><span>My Workout Plan</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- ── Reports — Admin Only (Accountant report is inside Payments submenu) ── -->
      <?php if (!empty($user_menu['reports']) && $user_role == 'admin'): ?>
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

  <!-- Logout -->
  <a href="../auth/logout.php" class="logout">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>

</aside>
<!-- ======== SIDEBAR END ======== -->