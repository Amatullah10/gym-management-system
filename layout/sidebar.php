<?php
if (!isset($_SESSION)) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$page = basename($_SERVER['PHP_SELF']); // for active state
?>

<aside class="sidebar">

  <!-- BRAND -->
  <div class="brand">
    <i class="fa-solid fa-dumbbell logo"></i>
    <div class="brand-text">
      <h2>FitnessPro</h2>
      <p><?= ucfirst($role) ?></p>
    </div>
  </div>

  <!-- NAVIGATION -->
  <div class="nav-section">
    <p class="nav-title">Navigation</p>
    <ul class="nav-list">

      <!-- ================= ADMIN ================= -->
      <?php if ($role === 'admin'): ?>

        <li class="<?= $page === 'dashboard1.php' ? 'active' : '' ?>">
          <a href="/admin1/dashboard1.php">
            <i class="fa-solid fa-gauge"></i>Dashboard
          </a>
        </li>

        <!-- MEMBERS -->
        <li class="submenu <?= in_array($page, ['members.php','member-entry.php','remove-member.php','edit-member.php']) ? 'parent-active' : '' ?>">
          <input type="checkbox" id="membersMenu" class="toggle-input"
            <?= in_array($page, ['members.php','member-entry.php','remove-member.php','edit-member.php']) ? 'checked' : '' ?>>
          <label for="membersMenu" class="submenu-label">
            <i class="fa-solid fa-users"></i>Members
          </label>

          <ul class="submenu-items">
            <li class="<?= $page === 'members.php' ? 'active' : '' ?>">
  <a href="../admin1/members.php">
    <i class="fa-solid fa-arrow-right"></i> List All Members
  </a>
</li>

<li class="<?= $page === 'member-entry.php' ? 'active' : '' ?>">
  <a href="../admin1/member-entry.php">
    <i class="fa-solid fa-arrow-right"></i> Member Entry Form
  </a>
</li>

<li class="<?= $page === 'remove-member.php' ? 'active' : '' ?>">
  <a href="../admin/remove-member.php">
    <i class="fa-solid fa-arrow-right"></i> Remove Member
  </a>
</li>

<li class="<?= $page === 'edit-member.php' ? 'active' : '' ?>">
  <a href="../admin/edit-member.php">
    <i class="fa-solid fa-arrow-right"></i> Update Member Details
  </a>
</li>

          </ul>
        </li>

        <!-- STAFF -->
        <li class="submenu <?= in_array($page, ['staff-list.php','staff-add.php','staff-remove.php','staff-update.php']) ? 'parent-active' : '' ?>">
          <input type="checkbox" id="staffMenu" class="toggle-input"
            <?= in_array($page, ['staff-list.php','staff-add.php','staff-remove.php','staff-update.php']) ? 'checked' : '' ?>>
          <label for="staffMenu" class="submenu-label">
            <i class="fa-solid fa-user-gear"></i>Staff
          </label>

          <ul class="submenu-items">
            <li class="<?= $page === 'staff-list.php' ? 'active' : '' ?>">
              <a href="/admin/staff-list.php">
                <i class="fa-solid fa-arrow-right"></i>List Staff</a>
            </li>
            <li class="<?= $page === 'staff-add.php' ? 'active' : '' ?>">
              <a href="/admin/staff-add.php">
                <i class="fa-solid fa-arrow-right"></i>Add Staff</a>
            </li>
            <li class="<?= $page === 'staff-remove.php' ? 'active' : '' ?>">
              <a href="/admin/staff-remove.php">
                <i class="fa-solid fa-arrow-right"></i>Remove Staff</a>
            </li>
            <li class="<?= $page === 'staff-update.php' ? 'active' : '' ?>">
              <a href="/admin/staff-update.php">
                <i class="fa-solid fa-arrow-right"></i>Update Staff</a>
            </li>
          </ul>
        </li>

        <li>
          <a href="/admin/payment-list.php">
            <i class="fa-solid fa-credit-card"></i>Payments
          </a>
        </li>

        <li>
          <a href="/admin/equipment-list.php">
            <i class="fa-solid fa-dumbbell"></i>Equipment
          </a>
        </li>

        <li>
          <a href="/admin/reports.php">
            <i class="fa-solid fa-chart-line"></i>Reports
          </a>
        </li>

        <li>
          <a href="/admin/settings.php">
            <i class="fa-solid fa-gear"></i>Settings
          </a>
        </li>

      <?php endif; ?>

      <!-- ================= TRAINER ================= -->
      <?php if ($role === 'trainer'): ?>

        <li class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">
          <a href="/trainer/dashboard.php">
            <i class="fa-solid fa-gauge"></i>Dashboard
          </a>
        </li>

        <li>
          <a href="/trainer/assigned-members.php">
            <i class="fa-solid fa-users"></i>Assigned Members
          </a>
        </li>

        <li>
          <a href="/trainer/progress-view.php">
            <i class="fa-solid fa-chart-bar"></i>Progress
          </a>
        </li>

        <li>
          <a href="/trainer/attendance-view.php">
            <i class="fa-solid fa-calendar-check"></i>Attendance
          </a>
        </li>

      <?php endif; ?>

      <!-- ================= ACCOUNTANT ================= -->
      <?php if ($role === 'accountant'): ?>

        <li>
          <a href="/accountant/dashboard.php">
            <i class="fa-solid fa-gauge"></i>Dashboard
          </a>
        </li>

        <li>
          <a href="/accountant/payment-list.php">
            <i class="fa-solid fa-credit-card"></i>Payments
          </a>
        </li>

        <li>
          <a href="/accountant/financial-reports.php">
            <i class="fa-solid fa-chart-line"></i>Financial Reports
          </a>
        </li>

      <?php endif; ?>

      <!-- ================= RECEPTIONIST ================= -->
      <?php if ($role === 'receptionist'): ?>

        <li>
          <a href="/receptionist/dashboard.php">
            <i class="fa-solid fa-gauge"></i>Dashboard
          </a>
        </li>

        <li>
          <a href="/receptionist/mark-attendance.php">
            <i class="fa-solid fa-calendar-check"></i>Mark Attendance
          </a>
        </li>

        <li>
          <a href="/receptionist/member-lookup.php">
            <i class="fa-solid fa-magnifying-glass"></i>Member Lookup
          </a>
        </li>

        <li>
          <a href="/receptionist/walkin-register.php">
            <i class="fa-solid fa-user-plus"></i>Walk-in Register
          </a>
        </li>

      <?php endif; ?>

      <!-- ================= MEMBER ================= -->
      <?php if ($role === 'member'): ?>

        <li>
          <a href="/member/dashboard.php">
            <i class="fa-solid fa-house"></i>Dashboard
          </a>
        </li>

        <li>
          <a href="/member/my-progress.php">
            <i class="fa-solid fa-chart-bar"></i>My Progress
          </a>
        </li>

        <li>
          <a href="/member/my-attendance.php">
            <i class="fa-solid fa-calendar"></i>My Attendance
          </a>
        </li>

        <li>
          <a href="/member/my-payments.php">
            <i class="fa-solid fa-credit-card"></i>My Payments
          </a>
        </li>

      <?php endif; ?>

    </ul>
  </div>

  <!-- LOGOUT -->
  <a href="../auth/logout.php" class="logout">
    <i class="fa-solid fa-right-from-bracket"></i>Logout
  </a>

</aside>
