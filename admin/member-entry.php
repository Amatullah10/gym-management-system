<?php
session_start();
$page = 'member-entry';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Member Registration</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="member-entry.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
<div class="form-container">

    <h2>Member Registration</h2>

    <form method="POST">

        <!-- ========== PERSONAL INFORMATION ========== -->
        <div class="section">
            <h3>Personal Information</h3>
            <p class="section-subtitle">Basic details about the member</p>

            <div class="row">
                <div>
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>

                <div>
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>
                </div>

                <div>
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>

            <label>Address *</label>
            <textarea name="address" required></textarea>
        </div>


        <!-- ========== MEMBERSHIP DETAILS ========== -->
        <div class="section">
            <h3>Membership Details</h3>
            <p class="section-subtitle">Select membership type and duration</p>

            <div class="row">
                <div>
                    <label>Membership Type *</label>
                    <select name="membership_type" required>
                        <option>Basic - 799/month</option>
                        <option>Standard - 999/month</option>
                        <option>Premium - 1299/month</option>
                    </select>
                </div>

                <div>
                    <label>Start Date *</label>
                    <input type="date" name="start_date" required>
                </div>

                <div>
                    <label>Duration *</label>
                    <select name="duration" required>
                        <option>1 Month</option>
                        <option>3 Months</option>
                        <option>6 Months</option>
                        <option>12 Months</option>
                    </select>
                </div>
            </div>

            <label>End Date *</label>
            <input type="date" name="end_date" required>
        </div>


        <!-- ========== FITNESS INFO ========== -->
        <div class="section">
            <h3>Fitness Information</h3>

            <div class="row">
                <div>
                    <p class="section-subtitle"> Help us understand your health and fitness goals</p>
                   
                    <label >Current Fitness Level*</label>
                    <select name="fitness_level" required>
                        <option>Beginner</option>
                        <option>Medium</option>
                        <option>Advanced</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit">Register Member</button>
            <button type="reset" class="reset">Reset Form</button>
        </div>

    </form>
</div>
</div>
</body>
</html>
