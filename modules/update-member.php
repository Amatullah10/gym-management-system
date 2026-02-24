<?php
session_start();
require_once '../dbcon.php'; // Your existing database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $membership_status = mysqli_real_escape_string($conn, $_POST['membership_status']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $fitness_level = mysqli_real_escape_string($conn, $_POST['fitness_level']);
    
    // Update member in database
    $sql = "UPDATE members SET 
            full_name = '$full_name',
            email = '$email',
            phone = '$phone',
            dob = '$dob',
            gender = '$gender',
            address = '$address',
            membership_type = '$membership_type',
            membership_status = '$membership_status',
            duration = '$duration',
            end_date = '$end_date',
            fitness_level = '$fitness_level'
            WHERE id = $member_id";
    
    if (mysqli_query($conn, $sql)) {
        // Redirect with success message
        header("Location: members.php?success=Record has been updated successfully!");
        exit();
    } else {
        // Redirect with error message
        header("Location: members.php?error=" . urlencode(mysqli_error($conn)));
        exit();
    }
    
} else {
    // If accessed directly, redirect to members page
    header("Location: members.php");
    exit();
}
?>