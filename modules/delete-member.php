<?php
session_start();
require_once '../dbcon.php';

// Only allow admin and receptionist
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'receptionist'])) {
    header("Location: ../index.php");
    exit();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $member_id = (int) $_POST['member_id'];

    if ($member_id <= 0) {
        header("Location: members.php?error=Invalid member ID.");
        exit();
    }

    // Get member email before deleting (to also remove user login account)
    $res = mysqli_query($conn, "SELECT email FROM members WHERE id = $member_id");
    if (!$res || mysqli_num_rows($res) === 0) {
        header("Location: members.php?error=Member not found.");
        exit();
    }
    $member = mysqli_fetch_assoc($res);
    $email  = mysqli_real_escape_string($conn, $member['email']);

    // Delete related records first (foreign key safety)
    mysqli_query($conn, "DELETE FROM trainer_assignments WHERE member_id = $member_id");
    mysqli_query($conn, "DELETE FROM attendance WHERE member_id = $member_id");
    mysqli_query($conn, "DELETE FROM payments WHERE member_id = $member_id");
    mysqli_query($conn, "DELETE FROM workout_plans WHERE member_id = $member_id");
    mysqli_query($conn, "DELETE FROM progress_reports WHERE member_id = $member_id");
    mysqli_query($conn, "DELETE FROM trainer_sessions WHERE member_id = $member_id");

    // Delete the member record
    if (mysqli_query($conn, "DELETE FROM members WHERE id = $member_id")) {
        // Also remove their user login account
        mysqli_query($conn, "DELETE FROM users WHERE email = '$email' AND role = 'customer'");
        header("Location: members.php?success=Member deleted successfully!");
    } else {
        header("Location: members.php?error=" . urlencode(mysqli_error($conn)));
    }

} else {
    header("Location: members.php");
}
exit();
?>