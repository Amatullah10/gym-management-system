<?php
session_start();
require_once '../dbcon.php';
require_once 'mailer.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $res   = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if ($res && mysqli_num_rows($res) > 0) {
        // Get name from members or staff table
        $name_res = mysqli_query($conn, "SELECT full_name FROM members WHERE email='$email'");
        if (!$name_res || mysqli_num_rows($name_res) === 0) {
            $name_res = mysqli_query($conn, "SELECT full_name FROM staff WHERE email='$email'");
        }
        $name_row = mysqli_fetch_assoc($name_res);
        $name = $name_row['full_name'] ?? 'User';

        $sent = sendSetPasswordEmail($email, $name, $conn);
        if ($sent) {
            $success = "Password reset link sent to <strong>$email</strong>. Check your inbox.";
        } else {
            $error = "Failed to send email. Please check your mail configuration.";
        }
    } else {
        // Don't reveal if email exists or not
        $success = "If that email exists, a reset link has been sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - FitnessPro</title>
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
    .back-link { text-align: center; margin-top: 20px; font-size: 13px; }
    .back-link a { color: #941614; font-weight: 600; text-decoration: none; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <i class="fas fa-dumbbell"></i>
    <h2>FitnessPro</h2>
    <p>Reset your password</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <p style="color:#666;font-size:14px;margin-bottom:5px;">Enter your email address and we'll send you a link to reset your password.</p>
  <form method="POST">
    <label>Email Address</label>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
  </form>
  <?php endif; ?>

  <div class="back-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Login</a></div>
</div>
</body>
</html>