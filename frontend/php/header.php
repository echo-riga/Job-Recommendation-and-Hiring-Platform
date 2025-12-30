<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFITSYSTEM</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/header.css?v=6">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';

$isLoggedIn = isset($_SESSION['userid']);
$profileName = '';
$profileImage = '';
$initials = '';

if ($isLoggedIn && isset($con)) {
  $userid = $_SESSION['userid'];

  $query = "SELECT firstname, lastname, email, contact, bday, educlvl, course, school, image 
            FROM users WHERE userid = ?";
  $stmt = $con->prepare($query);
  $stmt->bind_param("i", $userid);
  $stmt->execute();
  $stmt->bind_result($firstname, $lastname, $email, $contact, $bday, $educlvl, $course, $school, $image);
  $stmt->fetch();
  $stmt->close();

  $fullname = htmlspecialchars(trim("$firstname $lastname"));
  $profileName = htmlspecialchars($firstname);
  $initials = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));

  if (!empty($image)) {
    $profileImage = 'data:image/jpeg;base64,' . base64_encode($image);
  }
}

$systemName = "JOBFIT"; 
$systemLogoBase64 = '../image/jftlogo.png';

$stmtSys = $con->prepare("SELECT sysname, sysimage FROM systemname WHERE sysid = 1");
$stmtSys->execute();
$resSys = $stmtSys->get_result();
if ($resSys->num_rows === 1) {
    $sysData = $resSys->fetch_assoc();
    $systemName = htmlspecialchars($sysData['sysname']);
    if (!empty($sysData['sysimage'])) {
        $systemLogoBase64 = 'data:image/jpeg;base64,' . base64_encode($sysData['sysimage']);
    }
}
$stmtSys->close();
?>


<!-- HEADER -->
<header>
   <input type="checkbox" id="toggler">
  <label for="toggler" class="fa fa-bars"></label>
 <div class="logo">
        <?php echo $systemName; ?>
    </div>
<nav class="navbar">
  <div class="nav-item-with-dropdown">
     <a href="../php/home.php">Home</a>
    <span class="dropdown-toggle" id="homeDropdownToggle">▾</span>
    <div class="dropdown-contentheader" id="homeDropdown">
      <a href="../php/home.php">Home</a>
      <a href="#about">About</a>
      <a href="#job">Browse Jobs</a>
      <a href="#whotaketest">Who Take Test</a>
      <a href="#tips">Tips</a>
      <a href="#services">Services</a>
      <a href="#contact">Contact</a>
    </div>
  </div>
  <!-- BROWSE JOB -->
    <a href="../php/browse.php">Browse Jobs</a>
  <!-- ABOUT -->
    <a href="../php/about.php">About Us</a>
  <!-- YOUR RESULT -->
  <?php if ($isLoggedIn): ?>
    <a href="../php/yourtest.php">Your Result</a>
  <?php else: ?>
    <a href="#" id="showLoginModal">Your Result</a>
  <?php endif; ?>
</nav>



  <?php if ($isLoggedIn): ?>
    <div class="profile-dropdown">
      <div class="profile-toggle" onclick="toggleProfileDropdown()">
       <?php if (!empty($profileImage)): ?>
    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image" class="profile-pic">
<?php else: ?>
     <div class="profile-pic"><?= htmlspecialchars($initials) ?></div>
<?php endif; ?>

        <span class="profile-name">Hi, <?= $profileName ?></span>
        <span class="arrow">&#9662;</span>
      </div>
      <div class="profile-menu" id="profileMenu">
        <a href="../php/profile2.php">Profile</a>
        <a href="#" id="logoutBtn" class="logout">Logout</a>
      </div>
    </div>
  <?php else: ?>
    <div class="icons">
      <a href="../php/login.php"><i class="fa fa-sign-in-alt"></i> Login</a>
    </div>
  <?php endif; ?>
</header>
<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<?php endif; ?>

<!-- LOGOUT MODAL -->
  <div class="modal" id="logoutModal">
    <div class="modalupdate-content">
      <div class="modal-icon"><i class="fas fa-sign-out-alt"></i></div>
      <h2>Confirm Logout</h2>
      <p>Are you sure you want to logout of your account?</p>
      <div class="security-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Security Notice:</strong> This action will log you out of all devices and require you to sign in again.
      </div>
      <div class="modal-buttons">
        <button class="btn-no" id="cancelLogout" class="btn-no">Cancel</button>
        <button class="btn-yes" onclick="location.href='../php/logout.php'"><i class="fas fa-check"></i>Logout</button>
      </div>
    </div>
  </div>


<!-- Logout Success Modal -->
<div id="logoutSuccessModal" class="modal-suc" style="display: none;">
  <div class="modal-suc-content">
    <div class="suc-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h2>Logout Successful</h2>
    <p>You have been successfully logged out.</p>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
  const dropdowns = [
    { toggleId: 'homeDropdownToggle', contentId: 'homeDropdown' },
    { toggleId: 'resumeDropdownToggle', contentId: 'resumeDropdown' }
  ];

  dropdowns.forEach(drop => {
    const toggle = document.getElementById(drop.toggleId);
    const content = document.getElementById(drop.contentId);

    toggle?.addEventListener('click', function (e) {
      e.stopPropagation();
      content?.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
      if (!toggle.contains(e.target) && !content.contains(e.target)) {
        content?.classList.remove('show');
      }
    });
  });

  window.toggleProfileDropdown = function () {
    document.getElementById("profileMenu")?.classList.toggle("show");
  };

  window.addEventListener('click', function (event) {
    if (!event.target.closest('.profile-dropdown')) {
      document.getElementById("profileMenu")?.classList.remove("show");
    }
  });


  const logoutModal = document.getElementById('logoutModal');
  const cancelLogout = document.getElementById('cancelLogout');
  const logoutBtnSidebar = document.getElementById('logoutBtn');
  const logoutBtnDropdown = document.getElementById('logoutDropdownBtn');

  function openLogoutModal(e) {
    e.preventDefault();
    logoutModal.style.display = 'flex';
  }

  logoutBtnSidebar?.addEventListener('click', openLogoutModal);
  logoutBtnDropdown?.addEventListener('click', openLogoutModal);
  cancelLogout?.addEventListener('click', function () {
    logoutModal.style.display = 'none';
  });
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('logout') === 'success') {
  const modal = document.getElementById('logoutSuccessModal');
  if (modal) {
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
    console.log('Logout success modal shown');

    setTimeout(() => modal.style.opacity = '0', 2500);
    setTimeout(() => {
      if (modal.parentNode) modal.parentNode.removeChild(modal);
      const newUrl = window.location.origin + window.location.pathname;
      window.history.replaceState({}, document.title, newUrl);
      console.log('Logout success modal removed');
    }, 3000);
  }
}
window.addEventListener('load', function () {
    const showLoginModal = document.getElementById('showLoginModal');
    const registerOverlay = document.getElementById('registerOverlay');
    const closeRegisterModal = document.getElementById('closeRegisterModal');

    if (showLoginModal && registerOverlay && closeRegisterModal) {
      showLoginModal.addEventListener('click', function (e) {
        e.preventDefault();
        registerOverlay.style.display = 'flex';
      });

      closeRegisterModal.addEventListener('click', function () {
        registerOverlay.style.display = 'none';
      });

      registerOverlay.addEventListener('click', function (event) {
        if (event.target === registerOverlay) {
          registerOverlay.style.display = 'none';
        }
      });
    }
  });

document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', () => {
    const parent = toggle.closest('.nav-item-with-dropdown');
    parent.classList.toggle('open');
  });
});
</script>
</body>
</html>