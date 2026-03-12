<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { echo json_encode([]); exit(); }
if ($_SESSION['role'] != 'receptionist') { echo json_encode([]); exit(); }

$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

$members = [];

if (!empty($search)) {
    $res = mysqli_query($conn, "SELECT id, full_name, email, phone, membership_type, membership_status 
        FROM members 
        WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'
        ORDER BY full_name ASC 
        LIMIT 10");

    while ($row = mysqli_fetch_assoc($res)) {
        $members[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($members);
?>