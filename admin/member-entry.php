<?php

session_start();
// if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
//     header("Location: ../index.php");
//     exit();      //this prevents directly opening of the dashboard 
// }
$page = 'member-entry'; // current page indicator

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Registration</title>
    
<link rel="stylesheet" href="sidebar.css">
  <link rel="stylesheet" href="member-entry.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


      <!-- sidebar design -->
   
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
<div class="form-container">
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
</div>
</body>
</html>
