<?php
// Dummy member & progress data (replace with DB later)
$member = [
    "name" => "Sarah Johnson",
    "image" => "images/user1.jpg",
    "weight" => 76,
    "bmi" => 23.4,
    "fat" => 18,
    "hr" => 62
];

$weightProgress = [85, 83, 81, 80, 78, 76];
$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Progress View</title>
    <link rel="stylesheet" href="progress-view.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="top-bar">
        <h2>Progress View</h2>
        <input type="text" placeholder="Search..." class="search-box">
    </div>

    <!-- Member Selector -->
    <div class="member-select">
        <img src="<?php echo $member['image']; ?>">
        <div>
            <p>Viewing progress for</p>
            <select>
                <option><?php echo $member['name']; ?></option>
            </select>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $member['weight']; ?> kg</h3>
            <p>Current Weight</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $member['bmi']; ?></h3>
            <p>BMI</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $member['fat']; ?>%</h3>
            <p>Body Fat %</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $member['hr']; ?> bpm</h3>
            <p>Resting HR</p>
        </div>
    </div>

    <!-- Weight Chart -->
    <div class="card">
        <h3>Weight Progress</h3>
        <canvas id="weightChart"></canvas>
    </div>

    <!-- Goals & Achievements -->
    <div class="bottom-grid">

        <div class="card">
            <h3>Goals & Progress</h3>

            <div class="goal">
                <span>Lose 10kg</span>
                <div class="bar"><div style="width:90%"></div></div>
                <small>Current: 9kg | Target: 10kg</small>
            </div>

            <div class="goal">
                <span>Run 5K under 25 min</span>
                <div class="bar"><div style="width:80%"></div></div>
                <small>Current: 26.5 min | Target: 25 min</small>
            </div>

            <div class="goal">
                <span>Bench Press 100kg</span>
                <div class="bar"><div style="width:60%"></div></div>
                <small>Current: 60kg | Target: 100kg</small>
            </div>

            <div class="goal">
                <span>Complete 50 sessions</span>
                <div class="bar"><div style="width:84%"></div></div>
                <small>Current: 42 | Target: 50</small>
            </div>
        </div>

        <div class="card">
            <h3>Achievements</h3>

            <ul class="achievements">
                <li>🏃 First 5K Completed <small>Mar 15, 2024</small></li>
                <li>🔥 Lost 5kg Milestone <small>Feb 28, 2024</small></li>
                <li>🏆 30-Day Streak <small>Feb 10, 2024</small></li>
                <li>🌅 Early Bird Award <small>Jan 20, 2024</small></li>
            </ul>
        </div>

    </div>
</div>

<script>
const ctx = document.getElementById('weightChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            data: <?php echo json_encode($weightProgress); ?>,
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: false }
        }
    }
});
</script>

</body>
</html>
