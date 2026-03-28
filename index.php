<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'dbcon.php';

if (isset($_POST['submit'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    // Step 1: Check if user exists with this email at all
    $check_email = mysqli_query($conn, "SELECT id, role, password FROM users WHERE email='$email'");

    if (!$check_email || mysqli_num_rows($check_email) === 0) {
        $error_message = "No account found with this email address.";
    } else {
        $check_row = mysqli_fetch_assoc($check_email);

        // Step 2: Check if role matches
        if ($check_row['role'] !== $role) {
            $error_message = "This account is registered as <strong>" . htmlspecialchars($check_row['role']) . "</strong>, not as <strong>" . htmlspecialchars($role) . "</strong>. Please select the correct role.";
        } else {
            // Step 3: Verify password
            $password_ok = password_verify($password, $check_row['password']) || $check_row['password'] === $password;

            if (!$password_ok) {
                $error_message = "Incorrect password. If you registered recently, try the temporary password (your email name + 123, e.g. <strong>rahul123</strong>). Or use <a href='auth/forgot-password.php' style='color:#941614;'>Forgot Password</a> to reset it.";
            } else {
                // All checks passed — log in
                $query = "SELECT * FROM users WHERE email='$email' AND role='$role'";
                $data  = mysqli_query($conn, $query);
                $row   = mysqli_fetch_assoc($data);

                session_unset();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email']   = $row['email'];
                $_SESSION['role']    = $row['role'];

                switch ($row['role']) {
                    case 'admin':        header("Location: admin1/dashboard1.php"); break;
                    case 'trainer':      header("Location: trainer/index.php"); break;
                    case 'accountant':   header("Location: accountant/index.php"); break;
                    case 'receptionist': header("Location: receptionist/index.php"); break;
                    case 'customer':     header("Location: customer/index.php"); break;
                    default: header("Location: index.php");
                }
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextGen Fitness - Login</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="icon-badge">
                <img src="assets/logo.png" alt="NextGen Fitness" style="width:130px;height:1300px;object-fit:contain;"></div>
            
            <!-- Title -->
            <h2 class="login-title">NextGen Fitness</h2>
            <p class="login-subtitle">Sign in to continue</p>

            <!-- Error Message -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fa fa-exclamation-circle"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="#" method="POST" class="login-form">
                <input type="hidden" name="role" id="roleInput">

                <!-- Role Dropdown -->
                <label>Login As</label>
                <div class="dropdown">
                    <div class="selected">
                        <i class="fa fa-user"></i>
                        <span>Select Role</span>
                    </div>
                    <div class="menu">
                        <div data-role="admin"><i class="fa fa-user-shield"></i> Admin</div>
                        <div data-role="trainer"><i class="fa fa-user-tie"></i> Trainer</div>
                        <div data-role="receptionist"><i class="fa fa-user"></i> Receptionist</div>
                        <div data-role="accountant"><i class="fa fa-user-gear"></i> Accountant</div>
                        <div data-role="customer"><i class="fa fa-users"></i> Customer</div>
                    </div>
                </div>

                <!-- Email Input -->
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>

                <!-- Password Input -->
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" id="passwordInput" placeholder="Enter password" required>
                    <i class="fa fa-eye-slash" id="togglePassword" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);cursor:pointer;color:#666;font-size:14px;left:auto;" title="Show/Hide Password"></i>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login" name="submit">Login</button>

                <!-- Forgot Password Link -->
                <a href="auth/forgot-password.php" class="forgot-link">Forgot Password?</a>
            </form>
        </div>
    </div>

    <script>
        // Selecting elements
        const dropdown = document.querySelector(".dropdown");
        const selected = document.querySelector(".selected");
        const menu = document.querySelector(".menu");
        const options = document.querySelectorAll(".menu div");

        // Toggle dropdown menu
        selected.addEventListener("click", (e) => {
            e.stopPropagation();
            menu.classList.toggle("show");
        });

        // Select role option
        options.forEach(option => {
            option.addEventListener("click", (e) => {
                e.stopPropagation();
                
                const role = option.getAttribute("data-role");
                const icon = option.querySelector("i").cloneNode(true);
                const text = option.textContent.trim();
                
                // Clear and rebuild selected content
                selected.innerHTML = '';
                selected.appendChild(icon);
                const span = document.createElement('span');
                span.textContent = text;
                selected.appendChild(span);
                
                // Set hidden input value
                document.getElementById("roleInput").value = role;
                
                // Close menu
                menu.classList.remove("show");
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", (event) => {
            if (!dropdown.contains(event.target)) {
                menu.classList.remove("show");
            }
        });

        // Password eye toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput  = document.getElementById('passwordInput');
        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            togglePassword.classList.toggle('fa-eye-slash', !isHidden);
            togglePassword.classList.toggle('fa-eye', isHidden);
        });
    </script>
</body>
</html>