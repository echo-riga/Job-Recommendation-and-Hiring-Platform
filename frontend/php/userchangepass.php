<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFITSYSTEM</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/userchangepass.css">
</head>
<body>
<?php
include 'check_maintenance.php';
session_start();
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userid = $_SESSION['userid'];
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($new !== $confirm) {
        echo "<script>alert('New passwords do not match.');</script>";
    } else {
        $stmt = $con->prepare("SELECT Password FROM users WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current, $hashed_password)) {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $con->prepare("UPDATE users SET Password = ? WHERE userid = ?");
            $update->bind_param("si", $new_hashed, $userid);
            $update->execute();
            $update->close();

            session_destroy();
            echo "<script>alert('Password successfully changed. You will be logged out.'); window.location.href='login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Current password is incorrect.');</script>";
        }
    }
}
?>

<?php include 'header.php'; ?>



 <div class="background-wrapper"></div>
  <div class="header-card">
    <div class="title-wrap">
      <div class="icon-box">
        🔐
      </div>
      <div>
        <h1>Change Password</h1>
        <p>Update your account password securely</p>
      </div>
    </div>
    <a href="../php/profile.php" class="back-link">&larr; Back to Profile</a>
  </div>

<div class="security-tips-box panel panel-default" style=" border-radius: 10px;
  padding: 15px;
  border-left: 4px solid #ffc107;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.3rem;
  font-size: 0.92rem;
  background-color: #fff;
  width: 655px;
  height: 155px;">
  <div class="panel-heading">
    <h4><i class="fas fa-exclamation-triangle text-warning"></i> Security Tips</h4>
  </div>
  <div class="panel-body">
    <ul class="tips-list">
      <li><i class="fas fa-check text-success"></i> Use a unique password that you don't use elsewhere</li>
      <li><i class="fas fa-check text-success"></i> Consider using a password manager to generate and store strong passwords</li>
      <li><i class="fas fa-check text-success"></i> Don't share your password with anyone</li>
      <li><i class="fas fa-check text-success"></i> Change your password regularly for better security</li>
    </ul>
  </div>
</div>

  <form class="form-card" method="POST" action="">
    <div class="form-group">
      <label for="current">Current Password</label>
      <input type="password" id="current" name="current" placeholder="Enter your current password">
    </div>

    <div class="form-group">
      <label for="new">New Password</label>
      <input type="password" id="new" name="new" placeholder="Enter your new password">
      <div class="strength" id="strengthText">Password Strength: </div>
      <div class="progress password-strength-bar" style="height: 8px; margin-top: 5px;">
  <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
</div>

    </div>

    <div class="form-group">
      <label for="confirm">Confirm New Password</label>
      <input type="password" id="confirm" name="confirm" placeholder="Confirm your new password">
    </div>

    <div class="requirements">
      <strong>Password Requirements:</strong>
      <ul>
        <li>At least 8 characters long</li>
        <li>Contains uppercase letter (A-Z)</li>
        <li>Contains lowercase letter (a-z)</li>
        <li>Contains number (0-9)</li>
        <li>Contains special character (!@#$%^&*)</li>
      </ul>
    </div>

   <div class="actions">
  <button type="reset" class="btn btn-secondary" style="background: #f3f4f6;
      color: var(--text);">Reset Form</button>
  <button class="btn btn-primary" id="openModal" style="background: var(--primary);
      color: var(--white);">
    <span style="margin-right: 0.5rem;">✔</span>Update Password
  </button>
</div>

  </form>
   <div class="updatemodal-container" id="updatemodal-container">
    <div class="modalupdate-content">
      <div class="modal-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h2>Update Password</h2>
      <p>Are you sure you want to update your password?</p>

      <div class="security-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Security Notice:</strong> This action will log you out of all devices and require you to sign in again.
      </div>

      <div class="modal-buttons">
        <button class="btnupdate btn-cancel" id="closeModal">Cancel</button>
        <button class="btnupdate btn-update"><i class="fas fa-check"></i> Yes, Update</button>
        
      </div>
    </div>
  </div>

<script>
  document.querySelectorAll('.toggle-password').forEach(icon => {
    icon.addEventListener('click', () => {
      const target = document.getElementById(icon.dataset.target);
      const isPassword = target.type === 'password';
      target.type = isPassword ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });
  });

  const newPass = document.getElementById('new');
  const confirmPass = document.getElementById('confirm');
  const strengthText = document.querySelector('.strength span:last-child');

  function updateStrength(password) {
    let strength = 0;
    const rules = [
      /.{8,}/,    
      /[A-Z]/,        
      /[a-z]/,       
      /[0-9]/,        
      /[^A-Za-z0-9]/  
    ];
    rules.forEach(rule => rule.test(password) && strength++);

    const levels = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    const colors = ['#ef4444', '#f97316', '#eab308', '#10b981', '#22c55e'];
    strengthText.textContent = password ? levels[strength - 1] || 'Too Weak' : 'Enter password';
    strengthText.style.color = password ? colors[strength - 1] || '#999' : '#666';
  }

  function validateMatch() {
    const isMatch = confirmPass.value === newPass.value;
    confirmPass.style.borderColor = isMatch || confirmPass.value === '' ? '#ddd' : '#ef4444';
  }

  newPass.addEventListener('input', () => {
    updateStrength(newPass.value);
    validateMatch();
  });

  confirmPass.addEventListener('input', validateMatch);


  function updateStrength(password) {
  let strength = 0;
  const rules = [
    /.{8,}/,        
    /[A-Z]/,     
    /[a-z]/,       
    /[0-9]/,       
    /[^A-Za-z0-9]/  
  ];
  rules.forEach(rule => rule.test(password) && strength++);

  const levels = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
  const colors = ['#ef4444', '#f97316', '#eab308', '#10b981', '#22c55e'];
  const widths = ['20%', '40%', '60%', '80%', '100%'];

  const text = strength > 0 ? levels[strength - 1] : 'Enter password';
  const color = strength > 0 ? colors[strength - 1] : '#ccc';
  const width = strength > 0 ? widths[strength - 1] : '0%';

  document.getElementById('strengthText').textContent = `Password Strength: ${text}`;
  document.getElementById('strengthText').style.color = color;

  const strengthBar = document.getElementById('strengthBar');
  strengthBar.style.width = width;
  strengthBar.style.backgroundColor = color;
}
const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const modal = document.getElementById("updatemodal-container");

openModalBtn.addEventListener("click", function(e) {
  e.preventDefault(); 
  modal.style.display = "flex";
});

closeModalBtn.addEventListener("click", function(e) {
  e.preventDefault();
  modal.style.display = "none";
});

window.addEventListener("click", function(e) {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});

document.querySelector(".btn-update").addEventListener("click", function () {
  document.querySelector(".form-card").submit();
});

  </script>
</body>
</html>