<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "gym_management");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $membership_type = $_POST['membership_type'];
    $start_date = $_POST['start_date'];
    $duration = $_POST['duration'];
    $end_date = $_POST['end_date'];
    $fitness_level = $_POST['fitness_level'];

    // Insert into database
    $sql = "INSERT INTO members (full_name, email, phone, dob, gender, address, membership_type, start_date, duration, end_date, fitness_level)
            VALUES ('$full_name', '$email', '$phone', '$dob', '$gender', '$address', '$membership_type', '$start_date', '$duration', '$end_date', '$fitness_level')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Member registered successfully!');</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Registration</title>
    <link rel="stylesheet" href="member-entry.css">
</head>
<body>
<div class="container">
    <h2>Member Registration</h2>
    <form method="POST">

        <h3>Personal Information</h3>

        <label>Full Name *</label>
        <input type="text" name="full_name" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Phone Number *</label>
        <input type="text" name="phone" required>

        <label>Date of Birth *</label>
        <input type="date" name="dob" required>

        <label>Gender *</label>
        <select name="gender" required>
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select>

        <label>Address *</label>
        <textarea name="address" required></textarea>

        <h3>Membership Details</h3>

        <label>Membership Type *</label>
        <select name="membership_type" required>
            <option>Basic - 799/month</option>
            <option>Standard - 999/month</option>
            <option>Premium - 1299/month</option>
        </select>

        <label>Start Date *</label>
        <input type="date" name="start_date" required>

        <label>Duration *</label>
        <select name="duration" required>
            <option>1 Month</option>
            <option>3 Months</option>
            <option>6 Months</option>
            <option>12 Months</option>
        </select>

        <label>End Date *</label>
        <input type="date" name="end_date" required>

        <h3>Fitness Information</h3>

        <label>Current Fitness Level *</label>
        <select name="fitness_level" required>
            <option>Beginner</option>
            <option>Medium</option>
            <option>Advanced</option>
        </select>

        <div class="btn-group">
            <button type="submit">Register Member</button>
            <button type="reset" class="reset">Reset Form</button>
        </div>
    </form>
</div>
</body>
</html>
