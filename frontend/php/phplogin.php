<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';

require 'Mail/phpmailer/PHPMailerAutoload.php';

$error = ""; 

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

  if ($_POST['action'] === 'send_otp') {
    $recipientEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    $stmt = $con->prepare("SELECT userid FROM users WHERE email = ?"); 
    $stmt->bind_param("s", $recipientEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $response['message'] = "Account not found with that email address.";
      echo json_encode($response);
      exit();
    }

    $user = $res->fetch_assoc(); 
    $_SESSION['otp_userid'] = $user['userid']; 

    $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $otp_expiry = time() + (5 * 60); 


    $_SESSION['otp_email'] = $recipientEmail;
    $_SESSION['otp_code'] = $otp;
    $_SESSION['otp_expiry'] = $otp_expiry;


    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'jftsystem@gmail.com'; 
        $mail->Password = 'vwhs rehv nang bxuu';

        $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'JOBFIT: Your One-Time Password (OTP) for Password Reset';
        $mail->Body    = "
            <p>Dear User,</p>
            <p>You have requested a One-Time Password (OTP) to reset your password for your JOBFIT account.</p>
            <p>Your OTP is: <strong>" . $otp . "</strong></p>
            <p>This OTP is valid for 5 minutes. Do not share this code with anyone.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p>Thank you,<br>
            The JOBFIT Team</p>
        ";
        $mail->send();

        $response['success'] = true;
        $response['message'] = "OTP sent to your email.";

    } catch (Exception $e) {
        $response['message'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
    }
    echo json_encode($response);
    exit();

  } elseif ($_POST['action'] === 'verify_otp') {
    $enteredOtp = $_POST['otp'];

    if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expiry']) || !isset($_SESSION['otp_email'])) {
      $response['message'] = "OTP session expired or not initiated. Please request a new OTP.";
      echo json_encode($response);
      exit();
    }

    $storedOtp = $_SESSION['otp_code'];
    $otpExpiry = $_SESSION['otp_expiry'];
    $otpEmail = $_SESSION['otp_email'];

    if (time() > $otpExpiry) {
      $response['message'] = "OTP has expired. Please request a new one.";
    } elseif ($enteredOtp !== $storedOtp) {
      $response['message'] = "Invalid OTP. Please try again.";
    } else {
      $response['success'] = true;
      $response['message'] = "OTP verified successfully!";
      unset($_SESSION['otp_code']);
      unset($_SESSION['otp_expiry']);
      $_SESSION['reset_email'] = $otpEmail;
    }
    echo json_encode($response);
    exit();
  } elseif ($_POST['action'] === 'resend_otp') {
    $recipientEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    $stmt = $con->prepare("SELECT userid FROM users WHERE email = ?");
    $stmt->bind_param("s", $recipientEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $response['message'] = "Account not found with that email address.";
      echo json_encode($response);
      exit();
    }

    $user = $res->fetch_assoc();
    $_SESSION['otp_userid'] = $user['userid'];

    $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $otp_expiry = time() + (5 * 60);

    $_SESSION['otp_email'] = $recipientEmail;
    $_SESSION['otp_code'] = $otp;
    $_SESSION['otp_expiry'] = $otp_expiry;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'jftsystem@gmail.com';
        $mail->Password = 'vwhs rehv nang bxuu';

        $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'JOBFIT: Resent One-Time Password (OTP) for Password Reset';
        $mail->Body    = "
            <p>Dear User,</p>
            <p>You have requested to resend a One-Time Password (OTP) to reset your password for your JOBFIT account.</p>
            <p>Your new OTP is: <strong>" . $otp . "</strong></p>
            <p>This new OTP is valid for 5 minutes. Do not share this code with anyone.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p>Thank you,<br>
            The JOBFIT Team</p>
        ";
        $mail->send();

        $response['success'] = true;
        $response['message'] = "New OTP sent successfully!";

    } catch (Exception $e) {
        $response['message'] = "Failed to resend OTP. Mailer Error: {$mail->ErrorInfo}";
    }
    echo json_encode($response);
    exit();
  } 
  
  elseif ($_POST['action'] === 'submit_ticket') {
    $recipientEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format for ticket submission.";
        echo json_encode($response);
        exit();
    }

    $admin_email = 'jftsystem@gmail.com';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'jftsystem@gmail.com'; 
        $mail->Password = 'vwhs rehv nang bxuu'; 

        $mail->setFrom('jftsystem@gmail.com', 'JOBFIT KOEI System (Automated Ticket)');
        $mail->addAddress($admin_email); 
        $mail->addReplyTo($recipientEmail, 'Inactive User'); 

        $mail->isHTML(true);
        $mail->Subject = 'URGENT: User Account Reactivation Request - ' . $recipientEmail;
        $mail->Body    = "
            <p>Dear JOBFIT Administrator,</p>
            <p>A <strong>user</strong> attempted to log in but was blocked because their account is <strong>INACTIVE</strong>.</p>
            <p>They have automatically submitted this reactivation ticket.</p>
            <hr>
            <h3>Account Details:</h3>
            <p><strong>Inactive User Email:</strong> " . htmlspecialchars($recipientEmail) . "</p>
            <p><strong>Action Required:</strong> Please verify their credentials first before activating their account in the system.</p>
            <p>Thank you,<br>
            The JOBFIT KOEI System</p>
        ";
        $mail->send();

        $response['success'] = true;
        $response['message'] = "Submitted ticket successfully!";

    } catch (Exception $e) {
        $response['message'] = "Ticket submission failed. Mailer Error: {$mail->ErrorInfo}";
    }
    echo json_encode($response);
    exit();
  }
}
?>