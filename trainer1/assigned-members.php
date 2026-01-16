<?php
// Dummy assigned members data (later replace with DB fetch)
$members = [
    [
        "name" => "Sarah Johnson",
        "email" => "sarah.j@email.com",
        "phone" => "+1 234-567-8901",
        "program" => "Strength Training",
        "days" => "Mon / Wed / Fri",
        "status" => "Active",
        "image" => "images/user1.jpg"
    ],
    [
        "name" => "Michael Chen",
        "email" => "m.chen@email.com",
        "phone" => "+1 234-567-8902",
        "program" => "Weight Loss Program",
        "days" => "Tue / Thu / Sat",
        "status" => "Active",
        "image" => "images/user2.jpg"
    ],
    [
        "name" => "Emily Davis",
        "email" => "emily.d@email.com",
        "phone" => "+1 234-567-8903",
        "program" => "Flexibility & Yoga",
        "days" => "Mon / Wed",
        "status" => "On Hold",
        "image" => "images/user3.jpg"
    ]
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assigned Members</title>
    <link rel="stylesheet" href="assigned-members.css">
</head>
<body>

<div class="container">
    <div class="page-header">
        <h2>Assigned Members</h2>
        <input type="text" placeholder="Search members..." class="search-box">
    </div>

    <div class="members-grid">
        <?php foreach ($members as $member): ?>
        <div class="member-card">
            <div class="card-header">
                <img src="<?php echo $member['image']; ?>" alt="Member">
                <div>
                    <h3><?php echo $member['name']; ?></h3>
                    <p><?php echo $member['email']; ?></p>
                    <p><?php echo $member['phone']; ?></p>
                </div>
                <span class="status <?php echo strtolower(str_replace(' ', '-', $member['status'])); ?>">
                    <?php echo $member['status']; ?>
                </span>
            </div>

            <hr>

            <div class="card-body">
                <p><strong>Program:</strong> <?php echo $member['program']; ?></p>
                <p><strong>Days:</strong> <?php echo $member['days']; ?></p>
            </div>

            <div class="card-actions">
                <a href="#" class="btn">View</a>
                <a href="#" class="btn-outline">Edit Plan</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
