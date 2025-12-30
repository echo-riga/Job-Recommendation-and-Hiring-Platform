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
 <link rel="stylesheet" href="../css/home.css?v=5">
</head>
<style>
  .recommended-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.recommended-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.recommended-button:active {
    transform: translateY(0);
}


.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 8px;
    z-index: 1000;
    margin-top: 8px;
}

.dropdown-content.show {
    display: block;
}

.dropdown-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #f5f5f5;
}

.dropdown-item.active {
    background-color: #f0f0f0;
    color: #667eea;
    font-weight: 500;
}

.dropdown-item.active::before {
    content: "✓";
    margin-right: 8px;
    font-weight: bold;
}


.dropdown-button {
    background: white;
    border: 1px solid #ddd;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.dropdown-button:hover {
    border-color: #667eea;
}

.dropdown-button.active-filter {
    border-color: #667eea;
    background-color: #f8f9ff;
}

</style>
<body>
<?php
include 'check_maintenance.php';
session_start();
include 'database.php';
include 'header.php'; 


$logoutSuccess = isset($_GET['logout']) && $_GET['logout'] === 'success';
$isLoggedIn = isset($_SESSION['userid']);
$userid = $isLoggedIn ? $_SESSION['userid'] : 0; 


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

$query = "SELECT postid, postsummary, postjobrole, postspecification, postexperience,postworksetup, postresponsibilities, 
                 postsalary, posttype, postdate, postdeadline, poststatus, postaddress, postapplicantlimit
          FROM jobposting 
          WHERE poststatus = 'Open' 
          ORDER BY postdate DESC 
          LIMIT 3";  

$result = mysqli_query($con, $query);

$jobs = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = [
            'id' => $row['postid'],
            'title' => $row['postjobrole'],
            'company' => 'Philkoei International, Inc.',
            'location' => $row['postaddress'],
            'type' => strtolower($row['posttype']),
            'salary' => '₱' . number_format($row['postsalary'], 0) . ' / month',
            'salaryMin' => (float)$row['postsalary'],
            'salaryMax' => (float)$row['postsalary'],
            'summary' => $row['postsummary']
        ];
    }
}


$sql = "SELECT aboutus_home FROM website_content WHERE content_id";
$result = $con->query($sql);

$aboutus_home = "";
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $aboutus_home = $row['aboutus_home'];
}

$stmtSys->close();
?>


  <?php if ($logoutSuccess): ?>
  <script>
    
    console.log("Logout successful, page reloaded cleanly.");
  </script>
  <?php endif; ?>

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


  <div class="take-test-overlay" id="takeTestOverlay">
  <div class="take-test-modal">
    <div class="take-test-close" id="closeTakeTestModal">&times;</div>
    <div class="take-test-icon">
      <svg viewBox="0 0 24 24">
        <path d="M3 3v18h18V3H3zm2 2h14v14H5V5zm3 2v2h8V7H8zm0 4v2h8v-2H8zm0 4v2h5v-2H8zm-1.5-.5 1.41 1.41 2.59-2.59-1.41-1.41L6.5 13.5zm0-4 1.41 1.41 2.59-2.59-1.41-1.41L6.5 9.5z"/>
      </svg>
    </div>
    <h2> Hi Friend!, <br>Before Taking Our Test</h2>
    <p>Register or log in first to ensure your results are saved and more accurate.</p>
    <button class="take-test-login" onclick="location.href='../php/login.php'">🔐 Log In</button>
    <div class="take-test-register" onclick="location.href='../php/login.php'">Create an Account</div>
    <div class="take-test-secure">
      <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
      Your data is secure
    </div>
  </div>
</div>

  <!--Home-->
<section class="jthome" id="home">
  <div class="jt-content">
    <h3>Find Your Perfect IT Career Match</h3>
    <span>Discover Your Ideal Path</span>
    <p>Take our free skills test to discover the IT role that suits you best based on your strengths, interests, and potential.</p>
    <?php if ($isLoggedIn): ?>
  <a href="../php/yourtest.php" class="register-home-btn">Take the test</a>
<?php else: ?>
  <button class="register-home-btn" id="openTakeTestModal">Take the test</button>
<?php endif; ?>


</div>
</section>
 <!--STATS-->
 <section class="stats">
    <div class="stat-box">
      <div class="stat-value blue">10</div>
      <div class="stat-label">Test Taken Today</div>
    </div>
    <div class="stat-box">
      <div class="stat-value blue">20</div>
      <div class="stat-label">Total Test Taken</div>
    </div>
    <div class="stat-box">
      <div class="stat-value blue">95%</div>
      <div class="stat-label">Results rated as accurate or very accurate</div>
    </div>
  </section>

<!--About-->
<section class="jt-about" id="about">
   <h1 class="about-heading"><span>ABOUT</span>US</h1>
   <div class="row">
    <div class="video-container" >
      <video src="../image/bgvideo.mp4" loop autoplay muted></video>
      <h3>FIND YOUR PERFECT ROLE</h3>
</div>
<div class="jt-content">
<h3>Why Choose Us?</h3>
     <p><?= htmlspecialchars($aboutus_home) ?></p>

</div>
</section>
  <section class="browse" id="job">
    <div class="jobcontainer">
        <div class="jobheader">
            <h1>NEW JOB OFFER</h1>
            <p>Discover exciting career opportunities</p>
        </div>
        <div class="search-section">
            <div class="search-controls">
                <div class="search-container">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        type="text" 
                        id="searchInput"
                        class="search-input"
                        placeholder="Search job titles..."
                    >
                </div>
                

                <div class="filter-buttons">
                <div class="dropdown">
    <button class="dropdown-button" id="dropdownButton">
        <span id="dropdownSelectedText">All Types</span>
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div class="dropdown-content">
        <div class="dropdown-item" data-value="all" onclick="filterJobs('all')">All Types</div>
        <div class="dropdown-item" data-value="full-time" onclick="filterJobs('full-time')">Full Time</div>
        <div class="dropdown-item" data-value="part-time" onclick="filterJobs('part-time')">Part Time</div>
        <div class="dropdown-item" data-value="internship" onclick="filterJobs('internship')">Internship</div>
    </div>
</div>
                        <button class="recommended-button" onclick=" loadRecommendedJobs()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Recommended for You
    </button>
                    <button onclick="refreshFilters()" class="refresh-button">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <div id="jobGrid" class="job-grid">
            <!-- Job cards will be populated by JavaScript -->
        </div>
        
        <div id="paginationContainer" class="pagination-container">
            <div class="pagination-info">
                <span id="paginationInfo">Showing 1-6 of 8 jobs</span>
            </div>
            <div class="pagination-controls">
                <button id="prevButton" onclick="changePage(-1)" class="pagination-button" disabled>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Previous
                </button>
                <div id="pageNumbers" class="page-numbers">
                </div>
                <button id="nextButton" onclick="changePage(1)" class="pagination-button">
                    Next
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="no-results">
            <svg class="no-results-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.08-2.33M15 17H9v-2.5A6.5 6.5 0 0115.5 8H18a3 3 0 013 3v6a3 3 0 01-3 3z"></path>
            </svg>
            <h3>No jobs found</h3>
            <p>Try adjusting your search or filter criteria</p>
        </div>
    </div>
    </section>


<!-- discover test -->
<section class="career-heading" id="discover">
  <h2>Discover Your Best-Fit IT Career</h2>
  <p class="subtitle">
    Get a career recommendation by taking our rule-based skills assessment test – designed
    for students, fresh graduates, and job seekers in the IT field.
  </p>
  <div class="career-container">
    <div class="career-grid">
      <div class="card">
        <img src="../image/smartskill.png" alt="Skills">
        <h3>Smart Skill-Based Evaluation</h3>
        <p>
          Take a structured test based on your technical knowledge, logical thinking, and work
          preferences. The system analyzes your answers using a rule-based approach to match your
          profile with the most suitable IT roles.
        </p>
      </div>

      <div class="card">
        <img src="../image/rule.png" alt="Rules">
        <h3>Decision Tree Career Matching</h3>
        <p>
          Our system uses a decision tree model to guide you through a structured series of 
          questions based on your skills and interests. This helps determine the most suitable IT 
          career path — such as Software Developer, Cybersecurity Analyst, or Data Analyst — 
          by following logical paths, removing the need for manual guesswork or subjective judgment.
        </p>
      </div>

      <div class="card">
        <img src="../image/accurate.png" alt="Report">
        <h3>Instant, Accurate Job Fit Report</h3>
        <p>
          Once you complete the test, receive a detailed report showing your top-matching IT roles. The
          report includes why you're a fit, which skills matched, and what you can do to improve or qualify
          further.
        </p>
      </div>

      <div class="card">
        <img src="../image/guide.png" alt="Roadmap">
        <h3>IT Job Opportunities and Hiring at Philkoei International, Inc.</h3>
        <p>
         Philkoei International, Inc. actively posts and hires for various IT positions, 
         offering professionals valuable opportunities to advance their careers while 
         contributing to the company’s growth and technological innovation.
        </p>
      </div>
    </div>

  </div>
</section>
<!-- who clieent -->
<section id="whotaketest" class="section-whocards">
    <h2>Who Should Take the Test?</h2>
    <div class="whocards-container">
      <div class="whocard">
        <div class="card-icon"><img src="../image/reading-book.png" alt="Student"></div>
        <div class="card-text">IT students/college graduating</div>
      </div>

      <div class="whocard">
        <div class="card-icon"><img src="../image/graduates.png" alt="Graduate"></div>
        <div class="card-text">Fresh graduates unsure of which path to pursue</div>
      </div>

      <div class="whocard">
        <div class="card-icon"><img src="../image/career.png" alt="Career Shifter"></div>
        <div class="card-text">Career shifters interested in the tech industry</div>
      </div>
    </div>
  </section>

<!-- carusel -->
  <section class="section-carousel" id="tips">
    <div class="carousel">
      <h2>Get Ready, Get Set, Get Hired!</h2>
      <p>Explore essential tips on applying, writing standout resumes and job letters, and nailing your interviews—everything you need to get hired is right here.</p>
      <div class="slides">
        <div class="carouselcard active">
          <img src="../image/site.jpg" alt="Interview Tips">
          <div class="carouselcard-body">
            <h3>Interview Tips</h3>
            <p>Feel confident and ready on the big day? Check out our easy tips to impress and land the job you want!</p>
          </div>
        </div>
        <div class="carouselcard">
          <img src="../image/resume.jpg" alt="Resume Guide">
          <div class="carouselcard-body">
            <h3>Resume Building Guide</h3>
            <p>Your resume is your ticket to getting noticed—learn how to make it stand out and show employers why you’re the perfect fit!</p>
          </div>
        </div>
        <div class="carouselcard">
          <img src="../image/howto.png" alt="How to Apply">
          <div class="carouselcard-body">
            <h3>HOW TO APPLY</h3>
            <p>Don’t know what to say in your job letter? Follow our simple guide to write one that grabs attention and gets you interviews.</p>
          </div>
        </div>
      </div>
      <div class="dots">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>
    <div class="learn-more-container">
  <a href="resumetips.php" class="learn-more-btn">
    Learn More <span class="arrow">→</span>
  </a>
</div>


    </div>
  </section>
  


<section id="services" class="ourservices">
    <div class="services-txt">
        <h2>FEATURES</h2>
        <h1>Our Features & Services</h1>
    </div>
    <div class="main-container">
        <div class="service-box-container">
          
            <div class="service-box">
                <img src="../image/fscore.png" alt="Feature Image">
                <h3>Skill-Based Matching</h3>
                <p>Our platform leverages a powerful Decision Tree algorithm...</p>
                <button class="feature-btn" onclick="openModal('modal2')">See More</button>
            </div>
              <div class="service-box">
                <img src="../image/ftest.png" alt="Feature Image">
                <h3>Hiring and Job Opportunities at Philkoei International, Inc.</h3>
                <p>Philkoei International, Inc. is dedicated to attracting skilled and talented individuals...</p>
                <button class="feature-btn" onclick="openModal('modal1')">See More</button>
            </div>
            <div class="service-box">
                <img src="../image/fauto.png" alt="Feature Image">
                <h3>From Resume to Interview Success</h3>
                <p>Our service helps you every step...</p>
                <button class="feature-btn" onclick="openModal('modal3')">See More</button>
            </div>
        </div>
    </div>
</section>

<div id="modal1" class="modal-feature">
  <div class="modal-content-feature">
    <span class="close" onclick="closeModal('modal1')">&times;</span>
    <img src="../image/ftest.png" alt="Feature Image">
    <div class="feature-txt">
      <h1>Hiring and Job Opportunities at Philkoei International, Inc.</h1>
      <p><br>Philkoei International, Inc. is dedicated to attracting skilled and talented individuals through its efficient hiring and job posting system. The company ensures that every applicant is evaluated based on their competencies, qualifications, and professional background. By maintaining a transparent and organized recruitment process, Philkoei International, Inc. aims to match the right people to the right roles, fostering growth and excellence within the organization.<br></p>
    </div>
  </div>
</div>

<div id="modal2" class="modal-feature">
  <div class="modal-content-feature">
    <span class="close" onclick="closeModal('modal2')">&times;</span>
    <img src="../image/fscore.png" alt="Feature Image">
    <div class="feature-txt">
      <h1>Skill-Based Matching</h1>
      <p><br>Our platform leverages a powerful Decision Tree algorithm to objectively assess candidates' skills, qualifications, and experience, matching them to roles where they are most likely to succeed. This approach minimizes bias and ensures fair, data-driven evaluations, resulting in better hiring outcomes.<br></p>
    </div>
  </div>
</div>

<div id="modal3" class="modal-feature">
  <div class="modal-content-feature">
    <span class="close" onclick="closeModal('modal3')">&times;</span>
    <img src="../image/fauto.png" alt="Feature Image">
    <div class="feature-txt">
      <h1>Your Career Companion: From Resume to Interview Success</h1>
      <p><br>Our service helps you every step of the way from choosing the right job, creating your resume, to preparing for interviews. We offer easy-to-follow tools and tips that guide you in applying with confidence. Whether you're just starting out or changing careers, we're here to support you in landing the job you want.<br></p>
    </div>
  </div>
</div>


<div id="feedbackModal" class="modal-feedback">
  <div class="modal-content-feedback">
    <span id="closeModal" class="close">&times;</span>
    <h2>Feedback successfully sent!</h2>
    <p>Please wait for the approval of admins.</p>
  </div>
</div>

  <!-- CONTACT US -->
  <section class="contact" id="contact"> 
  <h1 class="contact-heading">CONTACT<span> US </span></h1>
  <div class="row">
    <form action="#contact" method="post">
    <input type="text" name="cntname" class="box" value="" placeholder="Name" required>
    <input type="email" name="cntemail" class="box" value="" placeholder="Email" required>
    <input type="subject" name="cntsubject" class="box" value="" placeholder="Subject" required>
    <input type="phone" name="cntphone" class="box" value="" placeholder="Phone" required>
    <textarea name="cntmessage" class="box" placeholder="Message" required cols="30" rows="10"></textarea>
      <input type="submit" value="Send Message" class="btn-contact">
    </form>
    <div class="image">
  <img src="../image/contactus.png" alt="contact">
</div>
</div>
  </div>
</section>

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
<script>

const jobsData = <?php echo json_encode($jobs, JSON_PRETTY_PRINT); ?>;
let filteredJobs = [...jobsData];
let currentPage = 1;
const jobsPerPage = 6;
let currentFilter = 'all';


function updateDropdownDisplay(selectedValue) {
    const dropdownButton = document.getElementById('dropdownButton');
    const selectedText = document.getElementById('dropdownSelectedText');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    if (!dropdownButton || !selectedText) {
        console.log('Dropdown elements not found');
        return;
    }
    
    console.log('Updating dropdown to:', selectedValue);
    

    dropdownItems.forEach(item => {
        item.classList.remove('active');
    });
    

    const selectedItem = document.querySelector(`.dropdown-item[data-value="${selectedValue}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    

    const textMap = {
        'all': 'All Types',
        'full-time': 'Full Time',
        'part-time': 'Part Time',
        'internship': 'Internship'
    };
    
    selectedText.textContent = textMap[selectedValue] || 'All Types';

    if (selectedValue !== 'all') {
        dropdownButton.classList.add('active-filter');
    } else {
        dropdownButton.classList.remove('active-filter');
    }
}


function initializeDropdown() {
    console.log('Initializing dropdown...');
    

    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownContent = document.querySelector('.dropdown-content');
    
    if (dropdownButton && dropdownContent) {
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownContent.classList.toggle('show');
        });
        

        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownContent.contains(e.target)) {
                dropdownContent.classList.remove('show');
            }
        });
        

        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                dropdownContent.classList.remove('show');
            });
        });
        

        updateDropdownDisplay('all');
        currentFilter = 'all';
        
    } else {
        console.log('Dropdown elements not found for initialization');
    }
}


function filterJobs(type) {
    currentFilter = type;
    updateDropdownDisplay(type);
    applyFilters();
}


function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();

    filteredJobs = jobsData.filter(job => {

        const normalizedJobType = job.type.toLowerCase().replace(/\s+/g, '');
        const normalizedFilter = currentFilter.toLowerCase().replace(/-/g, '');
        
        const matchesType = currentFilter === 'all' || normalizedJobType === normalizedFilter;
        const matchesSearch = job.title.toLowerCase().includes(searchTerm);
        
        return matchesType && matchesSearch;
    });

    currentPage = 1;
    renderJobs(filteredJobs);
}


function refreshFilters() {
    currentFilter = 'all';
    currentPage = 1;
    document.getElementById('searchInput').value = '';
    filteredJobs = [...jobsData];
    updateDropdownDisplay('all');
    renderJobs(filteredJobs);
}


function changePage(offset) {
    const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
    const newPage = currentPage + offset;
    
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderJobs(filteredJobs);
    }
}


function goToPage(page) {
    currentPage = page;
    renderJobs(filteredJobs);
}


function createJobCard(job) {
    const typeClasses = {
        'full time': 'badge-full-time',
        'part time': 'badge-part-time',
        'internship': 'badge-internship'
    };

    const typeLabels = {
        'full time': 'Full-Time',
        'part time': 'Part-Time',
        'internship': 'Internship'
    };

    return `
        <div class="job-card">
            <h3 class="job-title">${job.title}</h3>
            <div class="job-company">${job.company}</div>
            <div class="job-location">${job.location}</div>
            <span class="job-type-badge ${typeClasses[job.type]}">
                ${typeLabels[job.type]}
            </span>
            <div class="job-salary">${job.salary}</div>
            <p class="job-description">${job.summary || 'No description available'}</p>
            <button class="view-details-btn" 
                onclick="window.location.href='viewjob.php?<?php echo $isLoggedIn ? 'user_id=' . $userid . '&' : ''; ?>postid=${job.id}'">
                View Details
            </button>
        </div>
    `;
}


function renderJobs(jobs) {
    const jobGrid = document.getElementById('jobGrid');
    const noResults = document.getElementById('noResults');
    const paginationContainer = document.getElementById('paginationContainer');
    
    if (!jobGrid || !noResults || !paginationContainer) return;
    
    if (jobs.length === 0) {
        jobGrid.innerHTML = '';
        noResults.classList.add('show');
        paginationContainer.style.display = 'none';
        return;
    }

    noResults.classList.remove('show');
    paginationContainer.style.display = 'flex';

    const totalPages = Math.ceil(jobs.length / jobsPerPage);
    const startIndex = (currentPage - 1) * jobsPerPage;
    const endIndex = startIndex + jobsPerPage;
    const currentJobs = jobs.slice(startIndex, endIndex);

    jobGrid.innerHTML = currentJobs.map(job => createJobCard(job)).join('');
    updatePaginationControls(jobs.length, totalPages);
}

function updatePaginationControls(totalJobs, totalPages) {
    const paginationInfo = document.getElementById('paginationInfo');
    const pageNumbers = document.getElementById('pageNumbers');
    const prevButton = document.getElementById('prevButton');
    const nextButton = document.getElementById('nextButton');

    if (!paginationInfo || !pageNumbers || !prevButton || !nextButton) return;

    const startItem = (currentPage - 1) * jobsPerPage + 1;
    const endItem = Math.min(currentPage * jobsPerPage, totalJobs);
    paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${totalJobs} jobs`;

    prevButton.disabled = currentPage === 1;
    nextButton.disabled = currentPage === totalPages;

    pageNumbers.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement('button');
        pageButton.className = `page-number ${i === currentPage ? 'active' : ''}`;
        pageButton.textContent = i;
        pageButton.onclick = () => goToPage(i);
        pageNumbers.appendChild(pageButton);
    }
}

async function loadRecommendedJobs() {
    try {
        const response = await fetch('../api/filterJobs.php');
        const data = await response.json();
        
        const jobGrid = document.getElementById('jobGrid');
        const noResults = document.getElementById('noResults');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationInfo = document.getElementById('paginationInfo');
        
        if (!jobGrid || !noResults || !paginationContainer || !paginationInfo) return;
        
        if (data.success && data.jobs && data.jobs.length > 0) {
            jobGrid.innerHTML = '';
            
            data.jobs.forEach(job => {
                const jobCard = document.createElement('div');
                jobCard.className = 'job-card';

                const formattedSalary = job.postsalary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                jobCard.innerHTML = `
                    <h3 class="job-title">${job.postjobrole}</h3>
                    <div class="job-company">Philkoei International, Inc.</div>
                    <div class="job-location">${job.postaddress}</div>
                    <span class="job-type-badge badge-full-time">
                        ${job.posttype}
                    </span>
                    <div class="job-salary">₱${formattedSalary} / month</div>
                    <p class="job-description">${job.postsummary}</p>
                    <button class="view-details-btn" 
                        onclick="window.location.href='viewjob.php?<?php echo $isLoggedIn ? 'user_id=' . $userid . '&' : ''; ?>postid=${job.postid}'">
                        View Details
                    </button>
                `;
                
                jobGrid.appendChild(jobCard);
            });
            paginationInfo.textContent = `Showing ${data.jobs.length} recommended jobs`;
            paginationContainer.style.display = 'block';
            noResults.style.display = 'none';
            
        } else {
            jobGrid.innerHTML = '';
            paginationContainer.style.display = 'none';
            noResults.style.display = 'block';
            noResults.innerHTML = `
                <h3>No recommended jobs found</h3>
                <p>Complete your career assessment to get personalized job recommendations</p>
                <a href="yourtest.php" class="assessment-btn">Take Assessment</a>
            `;
        }
    } catch (error) {
        console.error('Error fetching recommended jobs:', error);
        const jobGrid = document.getElementById('jobGrid');
        if (!jobGrid) return;
        
        jobGrid.innerHTML = '';
        const noResults = document.getElementById('noResults');
        if (!noResults) return;
        
        noResults.style.display = 'block';
        noResults.innerHTML = `
            <h3>Unable to load recommendations</h3>
            <p>Please try again later or complete your assessment</p>
            <a href="yourtest.php" class="assessment-btn">Take Assessment</a>
        `;
    }
}


function loadTestStatistics() {
    console.log('Loading test statistics...');

}


function refreshStatistics() {
    console.log('Refreshing statistics...');
    loadTestStatistics();
}


function openModal(modalId) {
    document.querySelectorAll('.modal-feature').forEach(function(modal) {
        modal.style.display = 'none';
    });

    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}


let carouselIndex = 0;

function updateCarousel() {
    const cards = document.querySelectorAll('.carouselcard');
    const dots = document.querySelectorAll('.dot');
    
    cards.forEach((card, i) => {
        card.classList.remove('active');
        if (i === carouselIndex) card.classList.add('active');
    });
    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === carouselIndex);
    });
}


document.addEventListener('DOMContentLoaded', function() {
    console.log('Jobs data loaded:', jobsData);
    console.log('Jobs data length:', jobsData.length);
    loadTestStatistics();
    initializeDropdown();
    setInterval(loadTestStatistics, 120000);
    const btn = document.querySelector('.dropdown-button');
    const dropdown = document.querySelector('.dropdown-content');

    if (btn && dropdown) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }
    if (jobsData.length > 0) {
        filteredJobs = [...jobsData];
        renderJobs(filteredJobs);
    }
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

    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');

    if (burger && sidebar) {
        burger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            burger.classList.toggle('active');
        });
    }

    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    const logoutBtnSidebar = document.getElementById('logoutBtn');
    const logoutBtnDropdown = document.getElementById('logoutDropdownBtn');

    function openLogoutModal(e) {
        e.preventDefault();
        if (logoutModal) logoutModal.style.display = 'flex';
    }

    if (logoutBtnSidebar) logoutBtnSidebar.addEventListener('click', openLogoutModal);
    if (logoutBtnDropdown) logoutBtnDropdown.addEventListener('click', openLogoutModal);

    if (cancelLogout) {
        cancelLogout.addEventListener('click', function() {
            if (logoutModal) logoutModal.style.display = 'none';
        });
    }

    const form = document.querySelector("form");
    const modal = document.getElementById("feedbackModal");
    const closeModalBtn = document.getElementById("closeModal");

    if (form) {
        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            const response = await fetch("save_contact.php", {
                method: "POST",
                body: formData
            });

            if (response.ok) {
                if (modal) modal.style.display = "block";
                setTimeout(() => {
                    if (modal) modal.style.display = "none";
                    window.location.href = "home.php";
                }, 3000);
            }
        });
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            if (modal) modal.style.display = "none";
        });
    }
    const openTakeTestModal = document.getElementById('openTakeTestModal');
    const takeTestOverlay = document.getElementById('takeTestOverlay');
    const closeTakeTestModal = document.getElementById('closeTakeTestModal');

    if (openTakeTestModal && takeTestOverlay) {
        openTakeTestModal.addEventListener('click', function(e) {
            e.preventDefault();
            takeTestOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        if (closeTakeTestModal) {
            closeTakeTestModal.addEventListener('click', function() {
                takeTestOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }

        takeTestOverlay.addEventListener('click', function(event) {
            if (event.target === takeTestOverlay) {
                takeTestOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyFilters();
        });
    }

    document.querySelectorAll('.close').forEach(function(button) {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-feature');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-feature')) {
            event.target.style.display = 'none';
        }
    };

    const cards = document.querySelectorAll('.carouselcard');
    const dots = document.querySelectorAll('.dot');
    
    function updateCarousel() {
        cards.forEach((card, i) => {
            card.classList.remove('active');
            if (i === carouselIndex) card.classList.add('active');
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === carouselIndex);
        });
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            carouselIndex = i;
            updateCarousel();
        });
    });

    setInterval(() => {
        carouselIndex = (carouselIndex + 1) % cards.length;
        updateCarousel();
    }, 5000);
});
</script>
</body>
</html>
