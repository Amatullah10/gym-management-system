<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'receptionist') { header("Location: ../index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] != 'POST') { header("Location: add_member.php"); exit(); }

$full_name         = mysqli_real_escape_string($conn, $_POST['full_name']);
$email             = mysqli_real_escape_string($conn, $_POST['email']);
$phone             = mysqli_real_escape_string($conn, $_POST['phone']);
$dob               = $_POST['dob'];
$gender            = $_POST['gender'];
$address           = mysqli_real_escape_string($conn, $_POST['address']);
$membership_type   = $_POST['membership_type'];
$start_date        = $_POST['start_date'];
$end_date          = $_POST['end_date'];
$duration          = mysqli_real_escape_string($conn, $_POST['duration']);
$fitness_level     = $_POST['fitness_level'];
$membership_status = 'Active';

// Auto generate password from email
$email_name = explode('@', $email)[0];
$password   = $email_name . '123';

// Check duplicate in both tables
$check_member = mysqli_query($conn, "SELECT id FROM members WHERE email = '$email'");
$check_user   = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

if (mysqli_num_rows($check_member) > 0 || mysqli_num_rows($check_user) > 0) {
    header("Location: add_member.php?error=duplicate");
    exit();
}

// Step 1: Insert into members table
$insert_member = mysqli_query($conn, "INSERT INTO members 
    (full_name, email, phone, dob, gender, address, membership_type, start_date, end_date, duration, membership_status, fitness_level) 
    VALUES ('$full_name', '$email', '$phone', '$dob', '$gender', '$address', '$membership_type', '$start_date', '$end_date', '$duration', '$membership_status', '$fitness_level')");

if ($insert_member) {
    // Step 2: Insert into users table
    $insert_user = mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('', '$email', '$password')");

    if ($insert_user) {
        header("Location: add_member.php?success=1&email=" . urlencode($email) . "&password=" . urlencode($password));
        exit();
    } else {
        $err = urlencode("Member added but login failed! Error: " . mysqli_error($conn));
        header("Location: add_member.php?error=$err");
        exit();
    }
} else {
    $err = urlencode("Failed to register member! Error: " . mysqli_error($conn));
    header("Location: add_member.php?error=$err");
    exit();
}
?>