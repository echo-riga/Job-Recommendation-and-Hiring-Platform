<?php
include 'check_maintenance.php';
include 'phplogin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFIT</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <link rel="stylesheet" href="../css/login.css?v=3">
</head>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';

$error = "";
$submitted_email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action']) && $_POST['action'] === 'signup') {
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
      $error = "Account already exists.";
      $submitted_email = $email; 
    } else {
      $stmt = $con->prepare("INSERT INTO users (firstname, lastname, email, Password) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $fname, $lname, $email, $password);
      if ($stmt->execute()) {
        $_SESSION['userid'] = $stmt->insert_id;
        header("Location: home.php");
        exit();
      } else {
        $error = "Signup failed.";
        $submitted_email = $email; 
      }
    }
  } elseif (isset($_POST['action']) && $_POST['action'] === 'signin') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $submitted_email = $email; 

    $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
      $row = $res->fetch_assoc();
      
      if (password_verify($password, $row['Password']) && $row['status'] === 'INACTIVE') {

        $error = "
            ⚠️ Your account is currently inactive. Please contact the administrator to reactivate your account.
            <br><button type='button' id='submit-reactivation-ticket' data-email='".htmlspecialchars($email)."' class='reactivation-btn' style='margin-top: 10px; padding: 10px 15px; background-color: #f97316; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;'>
              Submit Reactivation Ticket
            </button>
            <span id='ticket-feedback' style='margin-left: 10px; font-weight: bold;'></span>
        ";
      }

      elseif (password_verify($password, $row['Password']) && $row['status'] !== 'INACTIVE') { 
        $_SESSION['userid'] = $row['userid'];


        $fullname = "user: " . $row['firstname'] . " " . $row['lastname'];

        $action = "Login";
        $details = "$fullname logged in";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $row['userid'], $fullname, $action, $details);
        $audit->execute();
        $audit->close();

        header("Location: home.php");
        exit();
      } else {

        $error = "Invalid credentials.";
      }
    } else {
      $error = "Account not found.";
    }
  }
}
?>
 <body>
    <div class="container">
        <div class="form-wrapper" id="auth-box">

          <form class="form active" id="sign-in-form" method="POST">
            <input type="hidden" name="action" value="signin">
            <div class="form-header">
              <div class="icon-box">
                <i class="fas fa-user"></i>
              </div>
              <h2>Welcome Back!</h2>
              <p class="subtitle">Sign in to your account to continue</p>
              <div class="page-indicator">
                <span class="active1"></span>
                <span class="active2"></span>
                <span class="active3"></span>
              </div>
            </div>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($submitted_email) ?>" required>

            <label>Password</label>
            <div class="password-wrapper">
              <input type="password" name="password" id="signin-password" required>
              <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="signin-password">
            </div>

            <div class="forgot-wrapper">
              <a href="#" id="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit">Sign In</button>
            <p class="switch-form">Don't have an account? <a href="#" id="go-signup">Sign up here</a></p>
          </form>

          <form class="form" id="sign-up-form" method="POST">
            <input type="hidden" name="action" value="signup">
            <div class="form-header">
              <div class="icon-box">
                <i class="fas fa-user-plus"></i>
              </div>
              <h2>Join us today!</h2>
              <p class="subtitle">Sign up to find your perfect IT career match</p>
              <div class="page-indicator">
                <span class="active1"></span>
                <span class="active2"></span>
                <span class="active3"></span>
              </div>
            </div>


            <div class="name-group">
              <div class="input-block">
                <label>First Name</label>
                <input type="text" name="firstname" placeholder="John" required>
              </div>
              <div class="input-block">
                <label>Last Name</label>
                <input type="text" name="lastname" placeholder="Doe" required>
              </div>
            </div>


            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>

            <label>Password</label>
            <div class="password-wrapper">
              <input type="password" name="password" id="signup-password" required>
              <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="signup-password">
            </div>
              <div class="strength-bar">
                  <div class="strength-bar-inner" id="strength-bar"></div>
              </div>
              <div class="password-criteria">
                <div class="criteria-grid">
                    <div class="criteria-item" id="length"><i class="fas fa-circle"></i>- 8+ characters</div>
                    <div class="criteria-item" id="upper"><i class="fas fa-circle"></i>- Uppercase letter</div>
                    <div class="criteria-item" id="lower"><i class="fas fa-circle"></i>- Lowercase letter</div>
                    <div class="criteria-item" id="number"><i class="fas fa-circle"></i>- Number</div>
                    <div class="criteria-item" id="special"><i class="fas fa-circle"></i>- Special character</div>
                </div>
              </div>

            <label>Confirm Password</label>
            <div class="password-wrapper">
              <input type="password" id="confirm-password" required>
              <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="confirm-password">
            </div>


            <label class="terms">
              <input type="checkbox" required>
              I agree to the <a href="#">Terms and Conditions</a>
            </label>

            <button type="submit">Create Account</button>
            <p class="switch-form">Already have an account? <a href="#" id="go-signin">Sign in here</a></p>
          </form>

          <?php if ($error): ?>
            <div class="error-message" style="display: block;"><?= $error ?></div>
          <?php endif; ?>
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


  <div id="terms-modal" class="modal">
    <div class="modal-content terms-large">
      <span class="close-btn" id="terms-close">&times;</span>
      <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 1rem;">Terms and Conditions</h2>
<div style="max-height: 400px; overflow-y: auto; font-size: 14px; line-height: 1.6; color: #374151;">

  <h3 style="font-size: 16px; font-weight: 600; margin-top: 1rem;">1. Introduction</h3>
  <p>
    Welcome to <strong>JOBFITKOEI</strong>.<br><br>

    These <strong>Terms and Conditions</strong> govern your access to and use of our website, online platform, and all related services operated by JOBFITKOEI.<br><br>

    Our <strong>Privacy Policy</strong> also applies to your use of the Service and explains how we collect, protect, and handle your personal information in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Republic of the Philippines.<br><br>

    By using JOBFITKOEI, you acknowledge that you have read, understood, and agreed to be bound by these Terms and our Privacy Policy. 
    If you do not agree with these Agreements, you may not access or use the Service.
  </p>

  <h3 style="font-size: 16px; font-weight: 600; margin-top: 1rem;">2. Communications</h3>
  <p>
    By creating an account or using our Service, you agree to receive communications from JOBFITKOEI related to your account activity, job applications, and system updates. 
    You may also receive optional promotional or informational materials. You can opt out of promotional communications at any time by following the unsubscribe link or contacting us directly.
  </p>

  <h3 style="font-size: 16px; font-weight: 600; margin-top: 1rem;">3. Data Privacy and Protection</h3>
  <p>
    JOBFITKOEI respects your right to privacy and is fully committed to complying with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and its Implementing Rules and Regulations.<br><br>

    In accordance with <strong>Section 11</strong> (General Data Privacy Principles) and <strong>Section 12</strong> (Lawful Processing of Personal Information) of the Act:
    <ul style="margin-left: 1rem; list-style-type: disc;">
      <li>Personal information shall be collected and processed lawfully, fairly, and transparently for legitimate purposes related to job application, employment matching, and system improvement.</li>
      <li>Data collected shall be adequate, relevant, and limited to what is necessary for the intended purpose.</li>
      <li>We shall ensure that all personal data is accurate, up to date, and retained only as long as necessary.</li>
    </ul>
    <br>
    Under <strong>Section 16</strong> (Rights of the Data Subject), you have the right to:
    <ul style="margin-left: 1rem; list-style-type: disc;">
      <li>Be informed of how your data is collected and processed;</li>
      <li>Access and correct your personal information;</li>
      <li>Withdraw consent and object to processing, subject to legal and contractual obligations;</li>
      <li>File a complaint with the National Privacy Commission (NPC) in case of violations.</li>
    </ul>
    <br>
    JOBFITKOEI implements organizational, physical, and technical security measures as required under <strong>Section 20</strong> (Security of Personal Information) to protect your data from unauthorized access, alteration, or disclosure.
  </p>

  <h3 style="font-size: 16px; font-weight: 600; margin-top: 1rem;">4. Limitation of Liability</h3>
  <p>
    JOBFITKOEI and its affiliates shall not be liable for any indirect, incidental, or consequential damages resulting from your use of or inability to use the Service. 
    The career recommendations and hiring-related results generated by the system are intended as guidance only and do not constitute a guarantee of employment.
  </p>

  <h3 style="font-size: 16px; font-weight: 600; margin-top: 1rem;">5. Governing Law</h3>
  <p>
    These Terms shall be governed by and construed in accordance with the laws of the Republic of the Philippines, including the <strong>Data Privacy Act of 2012</strong> and other applicable regulations. 
    Any disputes arising from or related to your use of JOBFITKOEI shall be handled exclusively by the appropriate courts within the Philippines.
  </p>

</div>


      <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
        <button id="decline-terms" class="terms-btn outlined">Decline</button>
        <button id="accept-terms" class="terms-btn filled">Accept</button>
      </div>
    </div>
  </div>



<script>
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

     
      forgotLink.onclick = (e) => {
        e.preventDefault();
        forgotModal.style.display = 'flex';
        forgotErrorMessage.textContent = ''; 
        forgotEmailInput.value = ''; 
      };

      forgotCloseBtn.onclick = () => {
        forgotModal.style.display = 'none';
      };


      otpCloseBtn.onclick = () => {
        otpModal.style.display = 'none';
      };


      window.onclick = (e) => {
        if (e.target === forgotModal) {
          forgotModal.style.display = 'none';
        }
        if (e.target === otpModal) {
          otpModal.style.display = 'none';
        }
      };


      document.getElementById('go-signup').onclick = () => {
        document.getElementById('sign-in-form').classList.remove('active');
        document.getElementById('sign-up-form').classList.add('active');
      };
      document.getElementById('go-signin').onclick = () => {
        document.getElementById('sign-up-form').classList.remove('active');
        document.getElementById('sign-in-form').classList.add('active');
      };

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

        fetch('login.php', {
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
            forgotErrorMessage.textContent = data.message || "Failed to send OTP. Please try again.";
            forgotErrorMessage.style.color = '#ef4444'; 
          }

          sendOtpBtn.disabled = false;
          sendOtpBtn.textContent = 'Send OTP';
        })
        .catch(error => {
          console.error('Error:', error);
          forgotErrorMessage.textContent = `An error occurred: ${error.message}. Please try again later.`; 
          forgotErrorMessage.style.color = '#ef4444'; 
          sendOtpBtn.disabled = false;
          sendOtpBtn.textContent = 'Send OTP';
        });
      });

      verifyOtpBtn.addEventListener('click', () => {
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        otpErrorMessage.textContent = ''; 
        otpErrorMessage.style.color = '';

        if (otp.length !== 4) {
          otpErrorMessage.textContent = "Please enter the full 4-digit OTP.";
          otpErrorMessage.style.color = '#ef4444'; 
          return;
        }

        fetch('login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=verify_otp&otp=${encodeURIComponent(otp)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'forgot-pass.php';
          } else {
            otpErrorMessage.textContent = data.message || "Invalid OTP. Please try again.";
            otpErrorMessage.style.color = '#ef4444'; 
          }
        })
        .catch(error => {
          console.error('Error:', error);
          otpErrorMessage.textContent = `An error occurred: ${error.message}. Please try again later.`; 
        });
      });

      resendOtpLink.addEventListener('click', (e) => {
        e.preventDefault();
        const email = forgotEmailInput.value;

        if (!email) {
            otpErrorMessage.textContent = "Please re-enter your email in the previous step and try again.";
            otpErrorMessage.style.color = '#ef4444'; 
            return;
        }

        resendOtpLink.style.pointerEvents = 'none';
        resendOtpLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        otpErrorMessage.textContent = '';
        otpErrorMessage.style.color = ''; 

        fetch('login.php', {
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
                otpErrorMessage.textContent = data.message || "Failed to resend OTP. Please try again.";
                otpErrorMessage.style.color = '#ef4444'; 
            }
            resendOtpLink.style.pointerEvents = 'auto'; 
            resendOtpLink.textContent = 'Resend OTP';
        })
        .catch(error => {
            console.error('Error:', error);
            otpErrorMessage.textContent = `An error occurred while resending OTP: ${error.message}.`; // Display exact error message
            otpErrorMessage.style.color = '#ef4444'; 
            resendOtpLink.style.pointerEvents = 'auto'; 
            resendOtpLink.textContent = 'Resend OTP';
        });
      });
      
      function sendReactivationRequest(email, button, feedbackSpan) {
          
          button.disabled = true;
          button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
          feedbackSpan.textContent = '';
          feedbackSpan.style.color = '';

          const errorContainer = button.closest('.error-message');

          fetch('login.php', { 
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `action=submit_ticket&email=${encodeURIComponent(email)}`
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  if (errorContainer) {
                      errorContainer.innerHTML = "✅ Submitted successfully! Wait for the admin response in your email.";
                      errorContainer.style.color = '#22c55e'; 
                      errorContainer.style.textAlign = 'center';
                  } else {
                      feedbackSpan.textContent = '✅ Submitted successfully! Wait for the admin response in your email.';
                      feedbackSpan.style.color = '#22c55e';
                      button.style.display = 'none'; 
                  }
              } else {
                  feedbackSpan.textContent = `❌ Failed: ${data.message}`;
                  feedbackSpan.style.color = '#ef4444'; 
                  button.innerHTML = 'Retry Submit Ticket';
                  button.style.backgroundColor = '#f97316'; 
                  button.disabled = false;
              }
          })
          .catch(error => {
              console.error('Error:', error);
              feedbackSpan.textContent = '❌ Network Error: Could not reach server.';
              feedbackSpan.style.color = '#ef4444';
              button.innerHTML = 'Retry Submit Ticket';
              button.style.backgroundColor = '#f97316';
              button.disabled = false;
          });
      }

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


      const passwordInput = document.getElementById('signup-password');
      const strengthBar = document.getElementById('strength-bar');
      const criteria = ["length", "upper", "lower", "number", "special"];

      if (passwordInput) { 
        passwordInput.addEventListener('input', () => {
          const val = passwordInput.value;
          let passed = 0;

          const checks = {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            lower: /[a-z]/.test(val),
            number: /[0-9]/.test(val),
            special: /[^A-Za-z0-9]/.test(val)
          };

          criteria.forEach(key => {
            const item = document.getElementById(key);
            if (item) { 
              const icon = item.querySelector('i');
              if (checks[key]) {
                item.classList.add('valid');
                item.classList.remove('invalid');
                if (icon) icon.className = 'fas fa-check-circle';
                passed++;
              } else {
                item.classList.remove('valid');
                item.classList.add('invalid');
                if (icon) icon.className = 'fas fa-times-circle';
              }
            }
          });

          const percent = (passed / 5) * 100;
          strengthBar.style.width = percent + '%';

          if (percent <= 40) {
            strengthBar.style.backgroundColor = '#ef4444'; 
          } else if (percent <= 80) {
            strengthBar.style.backgroundColor = '#f97316';
          } else {
            strengthBar.style.backgroundColor = '#22c55e'; 
          }
        });
      }

      const termsModal = document.getElementById('terms-modal');
      const acceptBtn = document.getElementById('accept-terms');
      const declineBtn = document.getElementById('decline-terms');
      const closeBtn = document.getElementById('terms-close');
      const termsCheckbox = document.querySelector('.terms input');
      const termsLink = document.querySelector('.terms a');

      if (termsLink) { 
        termsLink.addEventListener('click', (e) => {
          e.preventDefault();
          if (termsModal) termsModal.style.display = 'flex';
        });
      }

      if (acceptBtn) {
        acceptBtn.onclick = () => {
          if (termsModal) termsModal.style.display = 'none';
          if (termsCheckbox) termsCheckbox.checked = true;
        };
      }

      if (declineBtn) {
        declineBtn.onclick = () => {
          if (termsModal) termsModal.style.display = 'none';
        };
      }
      if (closeBtn) {
        closeBtn.onclick = () => {
          if (termsModal) termsModal.style.display = 'none';
        };
      }


      const signupForm = document.getElementById('sign-up-form');
      const signupPassword = document.getElementById('signup-password');
      const confirmPassword = document.getElementById('confirm-password');

      const createError = (msg) => {
        let error = document.getElementById('signup-error');
        if (!error) {
          error = document.createElement('div');
          error.id = 'signup-error';
          error.className = 'error-message';
          if (confirmPassword && confirmPassword.parentElement) {
            confirmPassword.parentElement.after(error);
          } else if (signupPassword && signupPassword.parentElement) {
             signupPassword.parentElement.after(error); 
          }
        }
        error.textContent = msg;
      };

      if (signupForm) { 
        signupForm.addEventListener('submit', (e) => {
          const pwd = signupPassword.value;
          const confirmPwd = confirmPassword.value;
          const checks = {
            length: pwd.length >= 8,
            upper: /[A-Z]/.test(pwd),
            lower: /[a-z]/.test(pwd),
            number: /[0-9]/.test(pwd),
            special: /[^A-Za-z0-9]/.test(pwd)
          };

          const allPassed = Object.values(checks).every(Boolean);
          if (!allPassed) {
            e.preventDefault();
            createError("Password must meet all criteria.");
            return;
          }

          if (pwd !== confirmPwd) {
            e.preventDefault();
            createError("Passwords do not match.");
            return;
            }

          const existingError = document.getElementById('signup-error');
          if (existingError) existingError.remove();
        });
      }

      document.addEventListener("DOMContentLoaded", function () {
        const params = new URLSearchParams(window.location.search);
        const shouldShowSignup = params.get("view") === "signup";

        if (shouldShowSignup) {
          document.getElementById('sign-in-form').classList.remove('active');
          document.getElementById('sign-up-form').classList.add('active');

          setTimeout(() => {
            const form = document.getElementById("sign-up-form");
            if (form) form.scrollIntoView({ behavior: "smooth" });
          }, 300);
        }
      });
</script>
</body>
</html>