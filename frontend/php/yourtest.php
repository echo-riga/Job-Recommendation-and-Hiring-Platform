<?php
session_start();
$database_path = '../../backend/php/database.php';

if (!file_exists($database_path)) {
    die("Database connection file not found at: " . $database_path);
}

include $database_path;

if (!isset($_SESSION['userid'])) {

    include 'check_maintenance.php';
} else {
    $user_id = $_SESSION['userid'];
    
    try {
        if (!$con) {
            throw new Exception("Database connection failed");
        }
        
        $query = "
            SELECT id FROM job_recommendations 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            header('Location: result.php');
            exit();
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error checking assessment results: " . $e->getMessage());
    }
    
    include 'check_maintenance.php';
}
?>

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
  <link rel="stylesheet" href="../css/yourtest.css?v=2">
</head>
<body>

<?php include 'header.php'; ?>

<div id="auth-status" data-is-logged-in="<?php echo isset($_SESSION['userid']) ? 'true' : 'false'; ?>" style="display: none;"></div>

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
        <div class="create-account" onclick="location.href='../php/register.php'">Create Account</div>
        <div class="secure">
            <svg viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Secure & Private
        </div>
    </div>
</div>


<section class="career-section">
  <div class="container">
    <h1 class="title">IT CAREER SUITABILITY TEST</h1>
    <div class="title-underline"></div> 
    <p class="subtitle">Discover your ideal path in the tech industry with our comprehensive assessment designed by industry experts</p>

    <div class="assessment-box">
      <div class="assessment-left">
        <h2>Find Your Perfect IT Career Match</h2>
        <p>
          Discover your ideal IT career path with our comprehensive assessment. 
          This test evaluates your logical thinking, situational judgment, and technical aptitude 
          to recommend the best IT role that matches your strengths and preferences.
        </p>
        <div class="features-container">
          <ul class="features">
            <li><i class="fas fa-check-circle"></i> Based on logic, real-world scenarios, and skills assessment</li>
            <li><i class="fas fa-clock"></i> Takes around 5-10 minutes to complete</li>
            <li><i class="fas fa-chart-line"></i> Receive instant personalized results</li>
          </ul>
        </div>
        
        <button class="start-btn">Start Assessment</button>

        <div class="loader" id="loader">
          <div class="spinner"></div>
        </div>
      </div>
      <div class="assessment-right">
        <img src="../image/coding.png" alt="Assessment Icon" />
      </div>
    </div>

    <div class="cards">
      <div class="card">
        <i class="fas fa-cogs card-icon"></i>
        <h3>Identify Your Core Strengths</h3>
        <p>
          Gain insight into your logical thinking, problem-solving, and technical abilities. 
          The assessment evaluates your strengths to help align your skills with the right IT career path.
        </p>
      </div>
      <div class="card">
        <i class="fas fa-code card-icon"></i>
        <h3>Match with Ideal IT Roles</h3>
        <p>
          Whether you're a coder, designer, or tech strategist, the test recommends IT roles 
          that best fit your personality, skills, and preferences across multiple specializations.
        </p>
      </div>
      <div class="card">
        <i class="fas fa-bolt card-icon"></i>
        <h3>Accelerate Your Tech Journey</h3>
        <p>
          Receive instant results and career suggestions tailored to help you 
          take your next step in IT—whether it's learning, training, or applying for jobs.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Footer section -->
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.querySelector('.dropdown-btn');
    const dropdown = document.querySelector('.dropdown-content');

    btn?.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      dropdown?.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
      if (!btn?.contains(e.target) && !dropdown?.contains(e.target)) {
        dropdown?.classList.remove('show');
      }
    });

    const allLinks = document.querySelectorAll(".navbar a, .dropdown-content a");

    function setActive(link) {
      allLinks.forEach(l => l.classList.remove("active-link"));
      link.classList.add("active-link");
    }

    allLinks.forEach(link => {
      link.addEventListener("click", () => setActive(link));
    });

    const currentHash = window.location.hash;
    allLinks.forEach(link => {
      if (link.getAttribute("href") === currentHash) {
        setActive(link);
      }
    });

    window.toggleProfileDropdown = function () {
      document.getElementById("profileMenu")?.classList.toggle("show");
    };

    window.addEventListener('click', function (event) {
      if (!event.target.closest('.profile-dropdown')) {
        document.getElementById("profileMenu")?.classList.remove("show");
      }
    });

    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');

    burger?.addEventListener('click', () => {
      sidebar?.classList.toggle('active');
      burger.classList.toggle('active');
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

    document.querySelector('.start-btn').addEventListener('click', () => {
        const isLoggedIn = document.getElementById('auth-status').dataset.isLoggedIn === 'true';
        
        if (!isLoggedIn) {
          alert("Log in required");
        } else {
            const loader = document.getElementById('loader');
            loader.style.display = 'flex';
            
            setTimeout(() => {
                window.location.href = 'test.php';
            }, 1500);
        }
    });

    document.getElementById('closeRegisterModal')?.addEventListener('click', function() {
        document.getElementById('registerOverlay').style.display = 'none';
    });

    document.getElementById('registerOverlay')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
  }); 
</script>

</body>
</html>