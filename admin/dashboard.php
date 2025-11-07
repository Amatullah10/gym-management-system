<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitnessPro - Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

  <div class="container">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar">
      <div class="brand">
        <i class="fas fa-dumbbell logo"></i>
        <div class="brand-text">
          <h2>FitnessPro</h2>
          <p>Admin</p>
        </div>
      </div>

      <div class="nav-section">
        <p class="nav-title">Navigation</p>
        <ul class="nav-list">
          <li class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></li>
          <li><i class="fas fa-users"></i><span>Members</span></li>
          <li><i class="fas fa-user-cog"></i><span>Staff</span></li>
          <li><i class="fas fa-calendar-check"></i><span>Attendance</span></li>
          <li><i class="fas fa-credit-card"></i><span>Payments</span></li>
          <li><i class="fas fa-dumbbell"></i><span>Equipment</span></li>
          <li><i class="fas fa-chart-line"></i><span>Reports</span></li>
          <li><i class="fas fa-cog"></i><span>Settings</span></li>
        </ul>
      </div>

      <div class="logout">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
      </div>
    </aside>

    <!-- RIGHT MAIN CONTENT -->
    <main class="main-content">
      <div class="breadcrumb">Gym Management System</div>
      <h1>Admin Dashboard</h1>
      <p class="subheading">Welcome back!</p>

      <!-- Placeholder content -->
      <div class="dashboard-content">
        <div class="card">Members: 120</div>
        <div class="card">Active Staff: 8</div>
        <div class="card">Today's Attendance: 56</div>
        <div class="card">Revenue: ₹45,000</div>
      </div>
    </main>

  </div>

</body>
</html>
