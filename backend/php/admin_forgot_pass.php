<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'database.php'; 

$error = "";

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_adminid'])) {
    header("Location: admin_login.php?error=access_denied");
    exit();
}

$user_id_for_reset = $_SESSION['otp_adminid']; 
$user_email_for_reset = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Password validation logic (same as user)
    if (strlen($newPassword) < 8 ||
        !preg_match('/[A-Z]/', $newPassword) ||
        !preg_match('/[a-z]/', $newPassword) ||
        !preg_match('/[0-9]/', $newPassword) ||
        !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $error = "Password must meet all criteria (8+ chars, uppercase, lowercase, number, special char).";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        //  Update the 'admin' table
        $stmt = $con->prepare("UPDATE admin SET adminpassword = ? WHERE adminid = ?");
        $stmt->bind_param("si", $hashedPassword, $user_id_for_reset);
        
        if ($stmt->execute()) {

          //  Fetch admin details for audit logging
          $fetch = $con->prepare("SELECT adminid, adminname, adminemail FROM admin WHERE adminid = ?");
          $fetch->bind_param("i", $user_id_for_reset);
          $fetch->execute();
          $result = $fetch->get_result();
          $row = $result->fetch_assoc();
          $fetch->close();

          if ($row) {
              //  Insert "Change Password" action into audit table
              $adminName = $row['adminname'] ?? $row['adminemail'];
              $fullname = "admin: " . $adminName;
              $action = "Change Password";
              $details = "$fullname changed password";

              $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
              $audit->bind_param("isss", $row['adminid'], $fullname, $action, $details);
              $audit->execute();
              $audit->close();
          }

          //  Clear all OTP session data
          unset($_SESSION['otp_adminid']);
          unset($_SESSION['reset_email']);
          unset($_SESSION['otp_code']);
          unset($_SESSION['otp_expiry']);

          //  Log the admin in automatically
          $_SESSION['admin'] = $user_email_for_reset;
          $_SESSION['adminid'] = $user_id_for_reset;

          //  Redirect to admin dashboard
          header("Location: dashboard.php");
          exit();

        } else {
            $error = "Failed to reset password. Please try again.";
        }

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFIT ADMIN - Reset Password</title> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <div class="container">
        <div class="form-wrapper" id="auth-box">

          <form class="form active" id="reset-password-form" method="POST">
            <input type="hidden" name="action" value="reset_password">
            <div class="form-header">
              <div class="icon-box">
                <i class="fas fa-key"></i>
              </div>
              <h2>Set New Password</h2>
              <p class="subtitle">Enter your new password for <?= htmlspecialchars($user_email_for_reset) ?></p>
              <div class="page-indicator">
                <span class="active1"></span>
                <span class="active2"></span>
                <span class="active3"></span>
              </div>
            </div>

            <?php if ($error): ?>
                <div class="error-message" style="display: block;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <label>New Password</label>
            <div class="password-wrapper">
              <input type="password" name="new_password" id="new-password" required>
              <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="new-password">
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
              <input type="password" name="confirm_password" id="confirm-password" required>
              <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="confirm-password">
            </div>

            <div id="password-error-message" class="error-message" style="display: none;"></div>

            <button type="submit">Change Password</button>
            <p class="switch-form"><a href="admin_login.php">Go back to Admin Login</a></p>
          </form>
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

      const newPasswordInput = document.getElementById('new-password');
      const confirmPasswordInput = document.getElementById('confirm-password');
      const strengthBar = document.getElementById('strength-bar');
      const criteria = ["length", "upper", "lower", "number", "special"];
      const passwordErrorMessage = document.getElementById('password-error-message');
      const resetPasswordForm = document.getElementById('reset-password-form');

      const validatePassword = () => {
        const newPwd = newPasswordInput.value;
        const confirmPwd = confirmPasswordInput.value;
        let passedCriteria = 0;

        const checks = {
          length: newPwd.length >= 8,
          upper: /[A-Z]/.test(newPwd),
          lower: /[a-z]/.test(newPwd),
          number: /[0-9]/.test(newPwd),
          special: /[^A-Za-z0-9]/.test(newPwd)
        };

        criteria.forEach(key => {
          const item = document.getElementById(key);
          if (item) {
            const icon = item.querySelector('i');
            if (checks[key]) {
              item.classList.add('valid');
              item.classList.remove('invalid');
              if (icon) icon.className = 'fas fa-check-circle';
              passedCriteria++;
            } else {
              item.classList.remove('valid');
              item.classList.add('invalid');
              if (icon) icon.className = 'fas fa-times-circle';
            }
          }
        });

        const percent = (passedCriteria / 5) * 100;
        strengthBar.style.width = percent + '%';
        if (percent <= 40) {
          strengthBar.style.backgroundColor = '#ef4444'; 
        } else if (percent <= 80) {
          strengthBar.style.backgroundColor = '#f97316'; 
        } else {
          strengthBar.style.backgroundColor = '#22c55e';
        }

        if (newPwd !== confirmPwd && confirmPwd.length > 0) {
          passwordErrorMessage.textContent = "Passwords do not match.";
          passwordErrorMessage.style.display = 'block';
        } else {
          passwordErrorMessage.textContent = "";
          passwordErrorMessage.style.display = 'none';
        }

        return allPassed = Object.values(checks).every(Boolean) && (newPwd === confirmPwd);
      };

      if (newPasswordInput) {
        newPasswordInput.addEventListener('input', validatePassword);
      }
      if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePassword);
      }

      if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', (e) => {
          const isValid = validatePassword(); 

          const newPwd = newPasswordInput.value;
          const confirmPwd = confirmPasswordInput.value;
          const checks = {
            length: newPwd.length >= 8,
            upper: /[A-Z]/.test(newPwd),
            lower: /[a-z]/.test(newPwd),
            number: /[0-9]/.test(newPwd),
            special: /[^A-Za-z0-9]/.test(newPwd)
          };
          const allCriteriaMet = Object.values(checks).every(Boolean);

          if (!allCriteriaMet) {
            e.preventDefault();
            passwordErrorMessage.textContent = "Password must meet all criteria.";
            passwordErrorMessage.style.display = 'block';
          } else if (newPwd !== confirmPwd) {
            e.preventDefault();
            passwordErrorMessage.textContent = "Passwords do not match.";
            passwordErrorMessage.style.display = 'block';
          } else {
            passwordErrorMessage.textContent = "";
            passwordErrorMessage.style.display = 'none';
          }
        });
      }

      document.addEventListener("DOMContentLoaded", function() {
        const phpErrorMessageDiv = document.querySelector('.form-wrapper > .error-message');
        if (phpErrorMessageDiv && phpErrorMessageDiv.textContent.trim() !== "") {
          phpErrorMessageDiv.style.display = 'block'; 
        }
      });
</script>
</body>
</html>