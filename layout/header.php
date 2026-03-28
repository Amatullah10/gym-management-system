<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? $page_title : 'Gym Management System' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
    .main-wrapper { margin-top: 70px !important; }
    .header-right { display: flex; align-items: center; gap: 10px; }
    .welcome-text { font-size: 14px; color: #555; }
    .welcome-text strong { color: #222; }
    .user-role-badge {
      font-size: 11px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
      background: #fde8e8;
      color: var(--active-color, #941614);
      text-transform: capitalize;
      letter-spacing: 0.3px;
    }
  </style>
</head>
<body>

<?php
// ── Fetch the logged-in user's real full name ──────────────────────────────
$_header_name = '';
$_header_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

if (!empty($_SESSION['email']) && isset($conn)) {
    $he = mysqli_real_escape_string($conn, $_SESSION['email']);

    if ($_header_role === 'customer') {
        // Customers are stored in the members table
        $nr = mysqli_query($conn, "SELECT full_name FROM members WHERE email='$he' LIMIT 1");
    } elseif ($_header_role === 'admin') {
        // Admin: first try staff table, then fall back to email prefix
        $nr = mysqli_query($conn, "SELECT full_name FROM staff WHERE email='$he' AND role='admin' LIMIT 1");
        if (!$nr || mysqli_num_rows($nr) === 0) {
            // No staff entry for admin — use "Admin" as the display name
            $_header_name = 'Admin';
            $nr = null;
        }
    } else {
        // trainer, receptionist, accountant — all in staff table
        $nr = mysqli_query($conn, "SELECT full_name FROM staff WHERE email='$he' LIMIT 1");
    }

    if (!empty($nr) && mysqli_num_rows($nr) > 0) {
        $row = mysqli_fetch_assoc($nr);
        $_header_name = $row['full_name'];
    }
}

// Final fallback
if (empty($_header_name)) {
    $_header_name = ucfirst($_header_role ?: 'User');
}
?>

<!-- Top Header Bar -->
<div class="top-header">
  <div class="header-left">
    <button class="menu-toggle" id="menuToggle">
      <i class="fa-solid fa-bars"></i>
    </button>
    <h1 class="header-title">
      <i class="fa-solid fa-dumbbell"></i>
      Gym Management System
    </h1>
  </div>
  <div class="header-right">
    <!-- Only ONE welcome message — shows real name, role shown as small badge -->
    <span class="welcome-text">Welcome, <strong><?= htmlspecialchars($_header_name) ?></strong>!</span>
    <span class="user-role-badge"><?= htmlspecialchars(ucfirst($_header_role)) ?></span>
  </div>
</div>