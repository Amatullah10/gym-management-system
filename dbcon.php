<?php

// Simple Database Connection File

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "gym";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if ($conn) {
   // echo "Connection Done.";
}
else{
    echo "connection Failed.";
}
?>