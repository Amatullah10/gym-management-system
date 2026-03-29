<?php

// Simple Database Connection File

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "gym";

// Create connection — halt execution if DB is unreachable
$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    // BUG FIX #5: Previously just echoed text and kept running,
    // causing every subsequent query to throw warnings.
    die("DB connection failed: " . mysqli_connect_error());
}
?>