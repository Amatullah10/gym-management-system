<?php 
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'accountant') {
    header("Location: ../index.php");
    exit();      //this prevents directly opening of the accountant dashboard 
}
  $page = 'dashboard'; // change this according to the current page
  include 'sidebar.php';
?>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
