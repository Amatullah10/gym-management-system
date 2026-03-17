<?php
session_start();
require_once '../dbcon.php';

/* TEMP: simulate logged-in user */
$_SESSION['role'] = 'admin';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: staff-list.php");
    exit();
}

$staff_id   = (int) $_POST['staff_id'];
$full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
$email      = mysqli_real_escape_string($conn, $_POST['email']);
$phone      = mysqli_real_escape_string($conn, $_POST['phone']);
$role       = mysqli_real_escape_string($conn, $_POST['role']);
$status     = mysqli_real_escape_string($conn, $_POST['status']);
$experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
$salary     = mysqli_real_escape_string($conn, $_POST['salary'] ?? '');

// Validate staff exists
$check = mysqli_query($conn, "SELECT id, email FROM staff WHERE id = $staff_id");
if (!$check || mysqli_num_rows($check) === 0) {
    header("Location: staff-list.php?error=Staff member not found.");
    exit();
}

$old = mysqli_fetch_assoc($check);
$old_email = $old['email'];

// Check if the new email is already taken by someone else
if ($email !== $old_email) {
    $email_check = mysqli_query($conn, "SELECT id FROM staff WHERE email = '$email' AND id != $staff_id");
    if ($email_check && mysqli_num_rows($email_check) > 0) {
        header("Location: staff-list.php?error=Email already in use by another staff member.");
        exit();
    }
}

// Update staff table
$sql = "UPDATE staff SET
            full_name  = '$full_name',
            email      = '$email',
            phone      = '$phone',
            role       = '$role',
            status     = '$status',
            experience = '$experience',
            salary     = '$salary'
        WHERE id = $staff_id";

if (!mysqli_query($conn, $sql)) {
    header("Location: staff-list.php?error=Failed to update staff: " . mysqli_error($conn));
    exit();
}

// If email changed, sync users table too
if ($email !== $old_email) {
    $old_email_safe = mysqli_real_escape_string($conn, $old_email);
    mysqli_query($conn, "UPDATE users SET email = '$email' WHERE email = '$old_email_safe'");
}

// If role changed and new role needs a login that doesn't exist yet, create it
$roles_with_login = ['trainer', 'receptionist', 'accountant'];
if (in_array($role, $roles_with_login)) {
    $user_check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if ($user_check && mysqli_num_rows($user_check) === 0) {
        if ($role === 'trainer') {
            mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('trainer', '$email', '')");
            // Optionally send set-password email — uncomment if mailer is available:
            // require_once '../auth/mailer.php';
            // sendSetPasswordEmail($email, $full_name, $conn);
        } else {
            // receptionist / accountant — no password set here, admin must handle separately
            mysqli_query($conn, "INSERT INTO users (role, email, password) VALUES ('$role', '$email', '')");
        }
    } else {
        // Update role in users table in case it changed
        mysqli_query($conn, "UPDATE users SET role = '$role' WHERE email = '$email'");
    }
}

header("Location: staff-list.php?success=Staff member updated successfully!");
exit();
?>