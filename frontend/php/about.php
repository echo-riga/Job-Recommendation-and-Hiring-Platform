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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/about.css?v=2">
</head>
<body>
    <?php

    include 'check_maintenance.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';
include 'header.php'; 
$logoutSuccess = isset($_GET['logout']) && $_GET['logout'] === 'success';
$isLoggedIn = isset($_SESSION['userid']);
$userid = $isLoggedIn ? $_SESSION['userid'] : 0; 


$sql = "SELECT aboutus_home FROM website_content WHERE content_id";
$result = $con->query($sql);

$aboutus_home = "";
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $aboutus_home = $row['aboutus_home'];
}

$sql = "SELECT aboutus_home, who_we_are, mission, vision, quality_policy, banner, group_photo FROM website_content WHERE content_id = 1";
$result = $con->query($sql);

$aboutus_home = $who_we_are = $mission = $vision = $quality_policy = "";
$banner = $group_photo = "";

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $aboutus_home   = $row['aboutus_home'];
    $who_we_are     = $row['who_we_are'];
    $mission        = $row['mission'];
    $vision         = $row['vision'];
    $quality_policy = $row['quality_policy'];
    $banner         = $row['banner'];
    $group_photo    = $row['group_photo'];
}
?>

     
<div class="overlay" id="registerOverlay" style="display:none;">
    <div class="register-modal">
      <div class="close-btn" id="closeRegisterModal">&times;</div>
      <div class="icon">
        <svg viewBox="0 0 24 24">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
      </div>
      <h2>Hold On! Log In to Access Your Test</h2>
      <p>Please sign in to start your personalized career test and unlock tailored results just for you.</p>
      <button class="login" onclick="location.href='../php/login.php'">🔒 Log In Now</button>
      <div class="create-account" onclick="location.href='../php/login.php'">Create Account</div>
      <div class="secure">
        <svg viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Secure & Private
      </div>
    </div>
  </div>

    <section class="hero"
        style="background: 
            linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)), 
            url('data:image/jpeg;base64,<?= base64_encode($banner) ?>') center/cover no-repeat;">
        <div>
            <h1>ABOUT US</h1>
            <nav class="breadcrumb">
                <a href="../php/home.php">Home</a>
                <span> > </span>
                <span>About Us</span>
            </nav>
        </div>
    </section>

    <div class="container">
        <!-- Who We Are Section -->
          <img
            src="../image/banner.png" alt="Who We Are Banner" class="who-we-are-image"> 
        <section class="who-we-are">
          
            <div class="who-we-are-text">

                <h2 class="section-title">WHO WE ARE</h2>
                 <p class="section-text"><?= nl2br(htmlspecialchars($who_we_are)) ?></p>
            </div>
            <div class="who-we-are-image">
                <img 
                    src="data:image/jpeg;base64,<?= base64_encode($group_photo) ?>" 
                    alt="Our Team" 
                    class="team-image">
            </div>
        </section>

        <!-- Vision Section -->
        <section class="card">
            <div class="card-icon">
                <i class="fas fa-eye"></i>
            </div>
            <h2 class="card-title">OUR VISION</h2>
            <p class="card-text">
                <?= nl2br(htmlspecialchars($vision)) ?>
            </p>
        </section>

        <!-- Mission Section -->
        <section class="card">
            <div class="card-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h2 class="card-title">OUR MISSION</h2>
            <p class="card-text">
                <?= nl2br(htmlspecialchars($mission)) ?>
            </p>
        </section>

        <!-- Quality Policy Section -->
        <section class="card">
            <div class="card-icon">
                <i class="fas fa-award"></i>
            </div>
            <h2 class="card-title">QUALITY POLICY</h2>
            <p class="card-text">
                 <?= nl2br(htmlspecialchars($quality_policy)) ?></p>
        </section>
    </div>

 <!-- FOOTER -->
 <section class="footer">
  <div class="footer-content">
      <div class="footer-logo">
        <img src="<?php echo $systemLogoBase64; ?>" alt="JOBFIT logo" class="footer-logo-image">
      <h1 class="footer-title"><?php echo $systemName; ?></h1>
    </div>
    
    <div class="quick-links">
  <h3>Quick Links</h3>
  <ul>
    <li><a href="../php/home.php"><span class="icon"><i class="fas fa-home"></i></span> Home</a></li>
    <li><a href="../php/browse.php"><span class="icon"><i class="fas fa-box"></i></span> Browse Jobs</a></li>
    <li><a href="../php/about.php"><span class="icon"><i class="fas fa-brain"></i></span> About us</a></li>
    <li><a href="../php/yourtest.php"><span class="icon"><i class="fas fa-envelope"></i></span> Your Result</a></li>
  </ul>
</div>


      <div class="contact-info">
      <h3>Contact Info</h3>
      <p>Email: mails@philkoei.com.ph</p>
      <p>Phone: 283968882</p>
    </div>
    <div class="social-media">
      <h3>Follow Us</h3>
      <a href="https://www.facebook.com/PKII1989"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.philkoei.com.ph/"><i class="fas fa-globe"></i></a>
      <a href="https://www.instagram.com/philkoei_1989/?utm_source=ig_web_button_share_sheet"><i class="fab fa-instagram"></i></a>
    <a href="https://www.linkedin.com/company/philkoei-international-inc/?viewAsMember=true" target="_blank">
  <i class="fab fa-linkedin-in"></i>
</a>

      </div>
  </div>
  <hr>
  <div class="footer-credits">
       <p>© 2025 <?php echo $systemName; ?> IT by Philkoei International, Inc. All rights reserved.</p>
  </div>
</section>
    </body>
</html>