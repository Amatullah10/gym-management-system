<?php
// Email helper — uses PHPMailer + config/email.php
// Usage: sendMail($to, $subject, $body)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email.php';

function sendMail($to_email, $to_name, $subject, $html_body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}

function generateResetToken($email, $conn) {
    // Delete any existing tokens for this email
    $e = mysqli_real_escape_string($conn, $email);
    mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE email='$e'");

    // Generate secure token
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    mysqli_query($conn, "INSERT INTO password_reset_tokens (email, token, expires_at)
        VALUES ('$e', '$token', '$expires')");

    return $token;
}

function sendSetPasswordEmail($to_email, $to_name, $conn) {
    require_once __DIR__ . '/../config/email.php';
    $token = generateResetToken($to_email, $conn);
    $link  = APP_URL . '/auth/set-password.php?token=' . $token;

    $body = "
    <div style='font-family:Inter,sans-serif;max-width:500px;margin:0 auto;padding:30px;'>
        <div style='text-align:center;margin-bottom:25px;'>
            <h2 style='color:#941614;margin:0;'>NextGen Fitness Gym</h2>
        </div>
        <h3 style='color:#333;'>Welcome, {$to_name}!</h3>
        <p style='color:#666;'>Your account has been created. Click the button below to set your password and activate your account.</p>
        <div style='text-align:center;margin:30px 0;'>
            <a href='{$link}' style='background:#941614;color:white;padding:14px 30px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;'>
                Set My Password
            </a>
        </div>
        <p style='color:#999;font-size:12px;'>This link expires in 24 hours. If you did not expect this email, ignore it.</p>
        <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
        <p style='color:#bbb;font-size:11px;text-align:center;'>FitnessPro Gym Management System</p>
    </div>";

    return sendMail($to_email, $to_name, 'Set Your FitnessPro Password', $body);
}