<?php
// Dummy trainer dashboard data (replace with DB later)
$trainerName = "John";

$stats = [
    "members" => 24,
    "new_members" => 3,
    "sessions" => 8,
    "progress_updates" => 12,
    "attendance_rate" => 92,
    "attendance_growth" => 5
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="top-bar">
        <h2>Dashboard</h2>
        <div class="top-actions">
            <input type="text" placeholder="Search..." class="search-box">
            <span class="notification">🔔</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">

        <div class="stat-card">
            <p>Total Members</p>
            <h3><?php echo $stats['members']; ?></h3>
            <small>+<?php echo $stats['new_members']; ?> this week</small>
        </div>

        <div class="stat-card">
            <p>Active Sessions</p>
            <h3><?php echo $stats['sessions']; ?></h3>
            <small>Today</small>
        </div>

        <div class="stat-card">
            <p>Progress Updates</p>
            <h3><?php echo $stats['progress_updates']; ?></h3>
            <small>Last 7 days</small>
        </div>

        <div class="stat-card">
            <p>Attendance Rate</p>
            <h3><?php echo $stats['attendance_rate']; ?>%</h3>
            <small>+<?php echo $stats['attendance_growth']; ?>% this month</small>
        </div>

    </div>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <h2>Welcome Back, <?php echo $trainerName; ?>!</h2>
        <p>
            You have <?php echo $stats['sessions']; ?> training sessions scheduled for today.
            Check your assigned members and track their progress from the navigation menu.
        </p>
    </div>

</div>

</body>
</html>
