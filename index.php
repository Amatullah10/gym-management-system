

  <?php
// If you later add authentication logic, it will go here
// Example: session_start(); include('config.php');
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

    <form class="login-form">

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
        <input type="email" placeholder="Enter email">
      </div>

      <label>Password</label>
      <div class="input-wrapper">
        <i class="fa fa-lock"></i>
        <input type="password" placeholder="Enter password">
      </div>

      <button type="submit" class="btn-login">Login</button>
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


  