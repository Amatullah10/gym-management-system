


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? $page_title : 'Gym Management System' ?></title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
  <style>
  .main-wrapper { margin-top: 70px !important; }
</style>
</head>
<body>

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
    <span class="welcome-text">Welcome back!</span>
    <span class="user-role"><?= isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Admin' ?></span>
  </div>
</div>
