<?php
include("dbcon.php");
session_start(); // <-- Required to use $_SESSION

if (isset($_POST['submit'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $role = $_POST['role']; // captured from hidden input

  // Check credentials in database
  $query = "SELECT * FROM users WHERE email='$email' AND password='$password' AND role='$role'";
  $data = mysqli_query($conn, $query);

  if ($data && mysqli_num_rows($data) > 0) {
    // Valid login
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;

    // Redirect based on role
    switch ($role) {
      case 'admin':
        header("Location: admin/index.php");
        break;
      case 'trainer':
        header("Location: trainer/index.php");
        break;
      case 'accountant':
        header("Location: accountant/index.php");
        break;
      case 'receptionist':
        header("Location: receptionist/index.php");
        break;
      case 'customer':
        header("Location: customer/index.php");
        break;
      default:
        echo "<script>alert('Invalid role');</script>";
    }
    exit();
  } else {
    echo "<script>alert('Invalid email, password, or role!');</script>";
  }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitnessPro - Admin Login</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="index.css">
</head>
<body>

  <div class="login-container">
  <div class="login-card">

    <div class="icon-badge"><i class="fa fa-dumbbell"></i></div>

    <h2 class="login-title">Fitness Pro</h2>
    <p class="login-subtitle">Sign in to continue</p>

    <form action ="#" method="POST" class="login-form">
<input type="hidden" name="role" id="roleInput">
      <label>Login As</label>
      <div class="dropdown">
        <div class="selected"><i class="fa fa-user"></i> Select Role</div>
        <div class="menu">
          <div><i class="fa fa-user-shield"></i> Admin</div>
          <div><i class="fa fa-user-tie"></i> Trainer</div>
          <div><i class="fa fa-user"></i> Receptionist</div>
          <div><i class="fa fa-user-gear"></i> Accountant</div>
          <div><i class="fa fa-users"></i> Customer</div>
        </div>
      </div>

      <label>Email</label>
      <div class="input-wrapper">
        <i class="fa fa-envelope"></i>
        <input type="email" placeholder="Enter email" name="email">
      </div>

      <label>Password</label>
      <div class="input-wrapper">
        <i class="fa fa-lock"></i>
        <input type="password" placeholder="Enter password" name="password">
      </div>

      <button type="submit" class="btn-login" name="submit" >Login</button>
      <a href="#" class="forgot-link">Forgot Password?</a>

    </form>
  </div>
</div>

<script>
// Selecting elements
const dropdown = document.querySelector(".dropdown");
const selected = document.querySelector(".selected");
const menu = document.querySelector(".menu");
const options = document.querySelectorAll(".menu div");

// 1. Toggle dropdown
dropdown.addEventListener("click", () => {
  menu.classList.toggle("show");
});

// 2. Select option

options.forEach(option => {
  option.addEventListener("click", () => {
    selected.innerHTML = option.innerHTML;
     document.getElementById("roleInput").value = option.textContent.trim().toLowerCase();
  });
});

// 3. Close if clicked outside
document.addEventListener("click", (event) => {
  if (!dropdown.contains(event.target)) {
    menu.classList.remove("show");
  }
});
</script>


</body>
</html>


</body>
</html>


  