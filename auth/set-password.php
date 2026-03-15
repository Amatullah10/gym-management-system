<?php
session_start();
require_once '../dbcon.php';

$error = $success = '';
$valid_token = false;
$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';
$email = '';

// Validate token
if ($token) {
    $res = mysqli_query($conn, "SELECT * FROM password_reset_tokens
        WHERE token='$token' AND used=0 AND expires_at > NOW()");
    if ($res && mysqli_num_rows($res) > 0) {
        $row   = mysqli_fetch_assoc($res);
        $email = $row['email'];
        $valid_token = true;
    } else {
        $error = 'This link is invalid or has expired. Please contact the gym admin.';
    }
}

// Handle password set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $e = mysqli_real_escape_string($conn, $email);

        // Update password in users table
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE email='$e'");

        // Mark token as used
        mysqli_query($conn, "UPDATE password_reset_tokens SET used=1 WHERE token='$token'");

        $success = 'Password set successfully! You can now log in.';
        $valid_token = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set Password - FitnessPro</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f3f6f9, #dee7ee); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .logo { text-align: center; margin-bottom: 25px; }
    .logo i { font-size: 36px; color: #941614; }
    .logo h2 { color: #941614; font-size: 22px; margin-top: 8px; }
    .logo p { color: #999; font-size: 13px; margin-top: 4px; }
    label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 5px; margin-top: 15px; }
    input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: 'Inter', sans-serif; }
    input:focus { outline: none; border-color: #941614; box-shadow: 0 0 0 3px rgba(148,22,20,0.1); }
    .btn { width: 100%; background: #941614; color: white; border: none; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 20px; font-family: 'Inter', sans-serif; }
    .btn:hover { background: #b01917; }
    .alert { padding: 12px 15px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .login-link { text-align: center; margin-top: 20px; font-size: 13px; color: #666; }
    .login-link a { color: #941614; font-weight: 600; text-decoration: none; }
    .req { font-size: 11px; color: #aaa; margin-top: 4px; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <i class="fas fa-dumbbell"></i>
    <h2>FitnessPro</h2>
    <p>Set your account password</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <div class="login-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Go to Login</a></div>

  <?php elseif ($valid_token): ?>
    <p style="color:#666;font-size:14px;margin-bottom:5px;">Setting password for: <strong><?= htmlspecialchars($email) ?></strong></p>
    <form method="POST">
      <label>New Password</label>
      <input type="password" name="password" placeholder="Enter new password" required>
      <p class="req">Minimum 6 characters</p>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Confirm your password" required>

      <button type="submit" class="btn"><i class="fas fa-lock"></i> Set Password</button>
    </form>

  <?php elseif (!$token): ?>
    <div class="alert alert-error">No token provided. Please use the link from your email.</div>
  <?php endif; ?>
</div>
</body>
</html>