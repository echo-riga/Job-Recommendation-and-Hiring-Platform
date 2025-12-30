<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'database.php';
require 'Mail/phpmailer/PHPMailerAutoload.php'; 

$error = ""; 
$response = ['success' => false, 'message' => ''];
$submitted_email = ""; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action']) && $_POST['action'] === 'signin') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $submitted_email = $email;

    $stmt = $con->prepare("SELECT * FROM admin WHERE adminemail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
      $row = $res->fetch_assoc();

      if (password_verify($password, $row['adminpassword']) && $row['adminstatus'] === 'INACTIVE') {
        
        $error = "
            ⚠️ Your account is currently inactive. Please contact the administrator to reactivate your account.
            <br><button type='button' id='submit-reactivation-ticket' data-email='".htmlspecialchars($email)."' class='reactivation-btn' style='margin-top: 10px; padding: 10px 15px; background-color: #f97316; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;'>
              Submit Reactivation Ticket
            </button>
            <span id='ticket-feedback' style='margin-left: 10px; font-weight: bold;'></span>
        ";
      }
      elseif (password_verify($password, $row['adminpassword'])) {
        $_SESSION['admin'] = $row['adminemail'];
        $_SESSION['adminid'] = $row['adminid'];

        // Audit log logic...
        $adminName = $row['adminname'] ?? $row['adminemail'];
        $fullname = "admin: " . $adminName;
        $action = "Admin Login";
        $details = "$fullname logged in";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $row['adminid'], $fullname, $action, $details);
        $audit->execute();
        $audit->close();

        header("Location: dashboard.php");
        exit();
      } 
      else {
        $error = "Invalid password.";
      }

    } else {
      $error = "Admin account not found.";
    }
  } 
  
  elseif (isset($_POST['action']) && $_POST['action'] === 'submit_ticket') {
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

        $mail->setFrom('jftsystem@gmail.com', 'JOBFIT System (Automated Ticket)');
        $mail->addAddress($admin_email); 
        $mail->addReplyTo($recipientEmail, 'Inactive Admin'); 

        $mail->isHTML(true);
        $mail->Subject = 'URGENT: Admin Account Reactivation Request - ' . $recipientEmail;
        $mail->Body    = "
            <p>Dear JOBFIT Administrator,</p>
            <p>An admin user attempted to log in but was blocked because their account is <strong>INACTIVE</strong>.</p>
            <p>They have automatically submitted this reactivation ticket.</p>
            <hr>
            <h3>Account Details:</h3>
            <p><strong>Inactive Admin Email:</strong> " . htmlspecialchars($recipientEmail) . "</p>
            <p><strong>Action Required:</strong> Please verify their credentials first before activating their account in the system.</p>
            <p>Thank you,<br>
            The JOBFIT System</p>
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
  
  // --- FORGOT PASSWORD LOGIC ---
  elseif (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    $recipientEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    $stmt = $con->prepare("SELECT adminid FROM admin WHERE adminemail = ?");
    $stmt->bind_param("s", $recipientEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $response['message'] = "Admin account not found with that email address.";
      echo json_encode($response);
      exit();
    }

    $admin = $res->fetch_assoc();
    $_SESSION['otp_adminid'] = $admin['adminid'];

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
        $mail->Subject = 'JOBFIT ADMIN: Your One-Time Password (OTP) for Password Reset';
        $mail->Body    = "
            <p>Dear Admin,</p>
            <p>You have requested a One-Time Password (OTP) to reset your password for your JOBFIT Admin account.</p>
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

  } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
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
  } elseif (isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
    $recipientEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    $stmt = $con->prepare("SELECT adminid FROM admin WHERE adminemail = ?");
    $stmt->bind_param("s", $recipientEmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $response['message'] = "Admin account not found with that email address.";
      echo json_encode($response);
      exit();
    }

    $admin = $res->fetch_assoc();
    $_SESSION['otp_adminid'] = $admin['adminid'];

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
        $mail->Subject = 'JOBFIT ADMIN: Resent One-Time Password (OTP) for Password Reset';
        $mail->Body    = "
            <p>Dear Admin,</p>
            <p>You have requested to resend an OTP for your JOBFIT Admin account.</p>
            <p>Your new OTP is: <strong>" . $otp . "</strong></p>
            <p>This OTP is valid for 5 minutes.</p>
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFIT ADMIN</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <link rel="stylesheet" href="../css/login.css"> </head>

<body>
  <div class="container">
    <div class="form-wrapper" id="auth-box"> 
      <form class="form active" id="sign-in-form" method="POST">
        <input type="hidden" name="action" value="signin">
        <div class="form-header"> <div class="icon-box">
            <i class="fas fa-user-shield"></i>
          </div>
          <h2>Administrator</h2>
          <p class="subtitle">Sign in to your account to continue</p>
        </div>

        <label>Email Address</label>
        <input type="email" name="email" id="signin-email" placeholder="your@email.com" value="<?= htmlspecialchars($submitted_email) ?>" required>

        <label>Password</label>
        <div class="password-wrapper">
          <input type="password" name="password" id="signin-password" required>
          <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="signin-password">
        </div>

        <div class="forgot-wrapper">
          <a href="#" id="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit">Sign In</button>

        <?php if ($error): ?>
          <div class="error-message" style="display: block;"><?= $error ?></div>
        <?php endif; ?>
      </form>
    </div> 
  </div>

  <div id="forgot-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="forgot-close-btn">&times;</span>
      <h2>Reset your password</h2>
      <p>Enter your email address and we'll send you a one-time password (OTP) to reset your password.</p>
      <label>Email Address</label>
      <input type="email" id="forgot-email-input" placeholder="your@email.com" required>
      <div id="forgot-error-message" class="error-message" ></div>
      <button type="button" id="send-otp-btn">Send OTP</button>
    </div>
  </div>

  <div id="otp-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="otp-close-btn">&times;</span>
      <h2>Enter OTP</h2>
      <p>A 4-digit OTP has been sent to your email. Please enter it below.</p>
      <div class="otp-input-container">
        <input type="text" class="otp-digit-input" maxlength="1">
        <input type="text" class="otp-digit-input" maxlength="1">
        <input type="text" class="otp-digit-input" maxlength="1">
        <input type="text" class="otp-digit-input" maxlength="1">
      </div>
      <div id="otp-error-message" class="error-message" ></div>
      <button type="button" id="verify-otp-btn">Verify OTP</button>
      <div class="resend-otp-container"> <p>Didn't receive the OTP? <a href="#" id="resend-otp-link">Resend OTP</a></p></div>
    </div>
  </div>

  <script>
    // --- Password Toggle ---
    document.querySelectorAll('.toggle-password').forEach(icon => {
      icon.addEventListener('click', () => {
        const targetId = icon.getAttribute('data-target');
        if (!targetId) return;
        const target = document.getElementById(targetId);
        const isHidden = target.type === 'password';

        target.type = isHidden ? 'text' : 'password';
        icon.src = isHidden ? '../image/eyeon.png' : '../image/eyeoff.png';
      });
    });


    // Function to handle the AJAX call for ticket submission
    function sendReactivationRequest(email, button, feedbackSpan) {
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        feedbackSpan.textContent = '';
        feedbackSpan.style.color = '';

        //  Get the parent container
        const errorContainer = button.closest('.error-message');

        fetch('admin_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=submit_ticket&email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Replace the entire error box content with the success message
                if (errorContainer) {
                    errorContainer.innerHTML = "Submitted successfully! Wait for the admin response in your email.";
                    errorContainer.style.color = '#22c55e'; 
                    errorContainer.style.textAlign = 'center';
                } else {
                    // Fallback (just in case)
                    feedbackSpan.textContent = 'Submitted successfully! Wait for the admin response in your email.';
                    feedbackSpan.style.color = '#22c55e';
                    button.style.display = 'none'; 
                }
            } else {
                // Failure logic 
                feedbackSpan.textContent = `❌ Failed: ${data.message}`;
                feedbackSpan.style.color = '#ef4444';
                button.innerHTML = 'Retry Submit Ticket';
                button.style.backgroundColor = '#f97316';
                button.disabled = false;
            }
        })
        .catch(error => {
            // Error logic
            console.error('Error:', error);
            feedbackSpan.textContent = '❌ Network Error: Could not reach server.';
            feedbackSpan.style.color = '#ef4444';
            button.innerHTML = 'Retry Submit Ticket';
            button.style.backgroundColor = '#f97316';
            button.disabled = false;
        });
    }

    // Event listener for the dynamically created button
    document.addEventListener('click', (e) => {
        if (e.target && e.target.id === 'submit-reactivation-ticket') {
            const button = e.target;
            const email = button.getAttribute('data-email');
            const feedbackSpan = document.getElementById('ticket-feedback');
            
            if (email && feedbackSpan) {
              sendReactivationRequest(email, button, feedbackSpan);
            } else if (!email) {
              if(feedbackSpan) {
                feedbackSpan.textContent = '❌ Error: Email not found.';
                feedbackSpan.style.color = '#ef4444';
              }
            }
        }
    });


    // --- (The rest of the MODAL & OTP SCRIPT is below and unchanged) ---

    const forgotModal = document.getElementById('forgot-modal');
    const otpModal = document.getElementById('otp-modal');
    const forgotLink = document.getElementById('forgot-link');
    const forgotCloseBtn = document.getElementById('forgot-close-btn');
    const otpCloseBtn = document.getElementById('otp-close-btn');
    const sendOtpBtn = document.getElementById('send-otp-btn');
    const verifyOtpBtn = document.getElementById('verify-otp-btn');
    const forgotEmailInput = document.getElementById('forgot-email-input');
    const forgotErrorMessage = document.getElementById('forgot-error-message');
    const otpErrorMessage = document.getElementById('otp-error-message');
    const otpInputs = document.querySelectorAll('.otp-digit-input');
    const resendOtpLink = document.getElementById('resend-otp-link');

    // Open forgot password modal
    forgotLink.onclick = (e) => {
      e.preventDefault();
      forgotModal.style.display = 'flex';
      forgotErrorMessage.textContent = '';
      forgotEmailInput.value = '';
    };

    // Close forgot password modal
    forgotCloseBtn.onclick = () => {
      forgotModal.style.display = 'none';
    };

    // Close OTP modal
    otpCloseBtn.onclick = () => {
      otpModal.style.display = 'none';
    };

    // Close modals when clicking outside
    window.onclick = (e) => {
      if (e.target === forgotModal) {
        forgotModal.style.display = 'none';
      }
      if (e.target === otpModal) {
        otpModal.style.display = 'none';
      }
    };

    // OTP input focus logic
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
            if (input.value.length === input.maxLength) {
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                } else {
                    verifyOtpBtn.click();
                }
            }
            otpErrorMessage.textContent = '';
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '') {
                if (index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });
    });

    // --- AJAX for Send OTP ---
    sendOtpBtn.addEventListener('click', () => {
      const email = forgotEmailInput.value;
      forgotErrorMessage.textContent = '';
      forgotErrorMessage.style.color = '';

      if (!email) {
        forgotErrorMessage.textContent = "Please enter your email address.";
        forgotErrorMessage.style.color = '#ef4444';
        return;
      }
      sendOtpBtn.disabled = true;
      sendOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

      fetch('admin_login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=send_otp&email=${encodeURIComponent(email)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          forgotModal.style.display = 'none';
          otpModal.style.display = 'flex';
          otpInputs.forEach(input => input.value = '');
          otpInputs[0].focus();
          otpErrorMessage.textContent = '';
          console.log(data.message);
        } else {
          forgotErrorMessage.textContent = data.message || "Failed to send OTP.";
          forgotErrorMessage.style.color = '#ef4444';
        }
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
      })
      .catch(error => {
        console.error('Error:', error);
        forgotErrorMessage.textContent = `An error occurred: ${error.message}.`;
        forgotErrorMessage.style.color = '#ef4444';
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
      });
    });

    // --- AJAX for Verify OTP ---
    verifyOtpBtn.addEventListener('click', () => {
      const otp = Array.from(otpInputs).map(input => input.value).join('');
      otpErrorMessage.textContent = '';
      otpErrorMessage.style.color = '';

      if (otp.length !== 4) {
        otpErrorMessage.textContent = "Please enter the full 4-digit OTP.";
        otpErrorMessage.style.color = '#ef4444';
        return;
      }

      fetch('admin_login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_otp&otp=${encodeURIComponent(otp)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.href = 'admin_forgot_pass.php';
        } else {
          otpErrorMessage.textContent = data.message || "Invalid OTP. Please try again.";
          otpErrorMessage.style.color = '#ef4444';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        otpErrorMessage.textContent = `An error occurred: ${error.message}.`;
        otpErrorMessage.style.color = '#ef4444';
      });
    });

    // --- Resend OTP Logic ---
    resendOtpLink.addEventListener('click', (e) => {
      e.preventDefault();
      const email = forgotEmailInput.value;

      if (!email) {
          otpErrorMessage.textContent = "Email not found. Please go back and re-enter.";
          otpErrorMessage.style.color = '#ef4444';
          return;
      }

      resendOtpLink.style.pointerEvents = 'none';
      resendOtpLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
      otpErrorMessage.textContent = '';
      otpErrorMessage.style.color = '';

      fetch('admin_login.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=resend_otp&email=${encodeURIComponent(email)}`
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              otpErrorMessage.textContent = data.message;
              otpErrorMessage.style.color = '#22c55e';
              otpInputs.forEach(input => input.value = '');
              otpInputs[0].focus();
          } else {
              otpErrorMessage.textContent = data.message || "Failed to resend OTP.";
              otpErrorMessage.style.color = '#ef4444';
          }
          resendOtpLink.style.pointerEvents = 'auto';
          resendOtpLink.textContent = 'Resend OTP';
      })
      .catch(error => {
          console.error('Error:', error);
          otpErrorMessage.textContent = `An error occurred: ${error.message}.`;
          otpErrorMessage.style.color = '#ef4444';
          resendOtpLink.style.pointerEvents = 'auto';
          resendOtpLink.textContent = 'Resend OTP';
      });
    });
  </script>
</body>
</html>