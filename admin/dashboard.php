<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Management System - Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-left">
        <i class="fas fa-bars"></i>
        <span class="logo-text">Gym Management System</span>
    </div>
    <div class="header-right">
        Welcome back! <strong>Admin</strong>
    </div>
</header>

<!-- Main Content -->
<div class="container">

    <!-- Welcome Section -->
    <div class="welcome">
        <h1>Admin Dashboard</h1>
        <p>Welcome back! Here's what's happening at your gym.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-icon teal"><i class="fas fa-users"></i></div>
            <div class="card-info">
                <h3>1,234</h3>
                <p>Total Members</p>
                <span class="growth green">+12% ↑</span>
            </div>
        </div>

        <div class="card">
            <div class="card-icon green"><i class="fas fa-user-tie"></i></div>
            <div class="card-info">
                <h3>28</h3>
                <p>Active Staff</p>
                <span class="growth green">+2 ↑</span>
            </div>
        </div>

        <div class="card">
            <div class="card-icon purple"><i class="fas fa-rupee-sign"></i></div>
            <div class="card-info">
                <h3>₹45,780</h3>
                <p>Monthly Revenue</p>
                <span class="growth green">+18% ↑</span>
            </div>
        </div>

        <div class="card">
            <div class="card-icon purple"><i class="fas fa-door-open"></i></div>
            <div class="card-info">
                <h3>156</h3>
                <p>Today's Check-ins</p>
                <span class="growth green">+8% ↑</span>
            </div>
        </div>
    </div>

    <!-- Two Column Section -->
    <div class="two-column">

        <!-- Recent Activity -->
        <div class="box">
            <h2>Recent Activity</h2>
            <ul class="activity-list">
                <li><span class="dot green"></span> New member registered <small>10 mins ago</small></li>
                <li><span class="dot purple"></span> Membership payment received <small>1 hour ago</small></li>
                <li><span class="dot orange"></span> Equipment maintenance completed <small>Today</small></li>
                <li><span class="dot teal"></span> Trainer assigned to member <small>Yesterday</small></li>
            </ul>
        </div>

        <!-- Upcoming Renewals -->
        <div class="box">
            <h2>Upcoming Renewals</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Date</th>
                </tr>
                <tr>
                    <td>Rahul Sharma</td>
                    <td>Gold</td>
                    <td>₹2,500</td>
                    <td>25 Sep</td>
                </tr>
                <tr>
                    <td>Anita Verma</td>
                    <td>Platinum</td>
                    <td>₹3,500</td>
                    <td>27 Sep</td>
                </tr>
                <tr>
                    <td>Amit Patel</td>
                    <td>Silver</td>
                    <td>₹1,800</td>
                    <td>30 Sep</td>
                </tr>
            </table>
        </div>

    </div>

</div>

</body>
</html>
