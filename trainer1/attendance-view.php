<?php
// Dummy summary data
$summary = [
    "total" => 42,
    "rate" => 92,
    "streak" => 7,
    "missed" => 4
];

// Dummy today's attendance
$todayAttendance = [
    ["name"=>"Sarah Johnson","time"=>"6:30 AM","status"=>"Checked In","img"=>"images/user1.jpg"],
    ["name"=>"Michael Chen","time"=>"7:00 AM","status"=>"Checked In","img"=>"images/user2.jpg"],
    ["name"=>"James Wilson","time"=>"8:15 AM","status"=>"Checked In","img"=>"images/user3.jpg"],
    ["name"=>"Emily Davis","time"=>"—","status"=>"Scheduled","img"=>"images/user1.jpg"],
    ["name"=>"Robert Brown","time"=>"—","status"=>"Absent","img"=>"images/user2.jpg"]
];

// Attendance calendar (P = Present, A = Absent)
$calendar = [
    2=>"P",3=>"P",6=>"P",7=>"P",8=>"P",9=>"P",10=>"P",
    4=>"A",11=>"A",17=>"A"
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance View</title>
    <link rel="stylesheet" href="attendance-view.css">
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="top-bar">
        <h2>Attendance View</h2>
        <input type="text" placeholder="Search..." class="search-box">
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <p>Total Sessions</p>
            <h3><?php echo $summary['total']; ?></h3>
        </div>
        <div class="stat-card">
            <p>Attendance Rate</p>
            <h3><?php echo $summary['rate']; ?>%</h3>
        </div>
        <div class="stat-card">
            <p>Current Streak</p>
            <h3><?php echo $summary['streak']; ?> days</h3>
        </div>
        <div class="stat-card">
            <p>Missed Sessions</p>
            <h3><?php echo $summary['missed']; ?></h3>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-grid">

        <!-- Calendar -->
        <div class="card">
            <div class="card-header">
                <h3>Attendance Calendar</h3>
                <select>
                    <option>All Members</option>
                </select>
            </div>

            <div class="calendar">
                <div class="month">January 2026</div>
                <div class="days">
                    <?php
                    for ($d = 1; $d <= 31; $d++) {
                        $class = "";
                        if (isset($calendar[$d])) {
                            $class = ($calendar[$d] == "P") ? "present" : "absent";
                        }
                        echo "<div class='day $class'>$d</div>";
                    }
                    ?>
                </div>

                <div class="legend">
                    <span class="present-dot"></span> Present
                    <span class="absent-dot"></span> Absent
                </div>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="card">
            <h3>Today's Attendance</h3>

            <?php foreach ($todayAttendance as $row): ?>
            <div class="attendance-item">
                <img src="<?php echo $row['img']; ?>">
                <div class="info">
                    <strong><?php echo $row['name']; ?></strong>
                    <small><?php echo $row['time']; ?></small>
                </div>
                <span class="badge <?php echo strtolower(str_replace(' ','-',$row['status'])); ?>">
                    <?php echo $row['status']; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

</body>
</html>
