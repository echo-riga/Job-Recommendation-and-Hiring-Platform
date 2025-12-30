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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/profile2.css?v=15">
<?php
include 'check_maintenance.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

$userid = $_SESSION['userid'];

$userid = $_SESSION['userid'];
$query = "SELECT firstname, lastname, email, contact, bday, educlvl, course, school, image FROM users WHERE userid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email, $contact, $bday, $educlvl, $course, $school, $image);
$stmt->fetch();
$stmt->close();

$fullname = htmlspecialchars(trim("$firstname $lastname"));
$profileEmail = htmlspecialchars($email);
$contact = htmlspecialchars($contact ?? '');
$bday = htmlspecialchars($bday ?? '');
$educlvl = htmlspecialchars($educlvl ?? '');
$course = htmlspecialchars($course ?? '');
$school = htmlspecialchars($school ?? '');
$profileImage = !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : '';
$initials = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
        
        $stmt = $con->prepare("UPDATE users SET image=NULL WHERE userid=?");
        $stmt->bind_param("i", $userid);
    } elseif (!empty($_FILES['photo']['tmp_name'])) {
        
        $imageData = file_get_contents($_FILES['photo']['tmp_name']);
        
        $stmt = $con->prepare("UPDATE users SET image=? WHERE userid=?");
        $stmt->bind_param("si", $imageData, $userid); 
        
    }

    if (isset($stmt)) {
        if ($stmt->execute()) {
            header("Location: profile2.php?update=success");
             exit();
        } else {
             echo "Error updating record: " . $con->error;
        }
        $stmt->close();
    }
}
?>
    

<?php include 'header.php'; ?>

   <div class="dashboard-container" id="dashboardContainer">
        
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fas fa-chevron-right" id="toggleIcon"></i>
        </button>
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>User Dashboard</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-section="test-history">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; vertical-align: middle;">
                            <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"></path>
                            <rect x="9" y="7" width="6" height="6"></rect>
                            <path d="M12 1v6"></path>
                        </svg>
                        Test History
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="applied-jobs">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; vertical-align: middle;">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        Applied Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="personal-details">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; vertical-align: middle;">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Personal Details
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="edit-profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; vertical-align: middle;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        Edit Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="change-password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; vertical-align: middle;">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <circle cx="12" cy="16" r="1"></circle>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Change Password
                    </a>
                </li>
            </ul>
        </nav>

        <main class="main-content" id="mainContent">
<form id="profileForm" method="POST" enctype="multipart/form-data" action="profile2.php">
  <div class="profile-header">
    <div class="profile-image-container" onclick="openProfileModal()">
      <div class="avatar-circle" id="profileAvatar">
  <?php if (!empty($profileImage)): ?>
    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image" class="profile-image">
  <?php else: ?>
    <?= htmlspecialchars($initials) ?>
  <?php endif; ?>
</div>

      <div class="camera-overlay">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
          <circle cx="12" cy="13" r="4"></circle>
        </svg>
      </div>
    </div>

  <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;" onchange="handlePhotoUpload(event)">
  <input type="hidden" name="remove_photo" id="remove_photo" value="0">
</form>
               <div class="profile-info">
                  <h3 id="userName"><?= $fullname ?></h3>
                  <p>
                    <i class="fas fa-envelope"></i>
                    <span id="userEmail"><?= $profileEmail ?></span>
                  </p>
                </div>
            </div>

            <div class="content-area">
                <!-- Test History Section -->
                <div class="content-section active" id="test-history">
                    <div class="card">
                        <h3>Job Recommendation Result</h3>
                        <div style="display: flex; gap: 20px; margin-top: 20px;">
                            <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                <h4 style="margin: 0 0 10px; color: #1f2937;">Recommended Position</h4>
                                <div style="text-align: center;">
                                    <p style="margin: 0 0 15px; font-weight: bold; color: #2563eb;"><strong>Top 1: Software Developer – 90%</strong></p>
                                    <div style="font-size: 48px; color: #2563eb; margin: 10px 0;">💼</div>
                                    <h3 style="margin: 10px 0; color: #1f2937;">Software Developer</h3>
                                    <div style="font-size: 24px; font-weight: bold; color: #10b981; margin: 10px 0;">90% Match</div>
                                    <p style="color: #6b7280; margin: 10px 0;">Based on your skills and preferences</p>
                                </div>
                            </div>
                            <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                <h4 style="margin: 0 0 15px; color: #1f2937; text-align: center;">Skill Breakdown</h4>
                                <div style="text-align: center;">
                                    <canvas id="skillChart" width="200" height="200" style="display: block; margin: 0 auto;"></canvas>
                                    <div id="chartLegend" style="margin-top: 20px; text-align: left; display: inline-block;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>Test History</h3>
                        <div class="table-responsive">
                            <table class="historytable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Suggested Job</th>
                                        <th>Match Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2024-01-15</td>
                                        <td>Software Developer</td>
                                        <td>90%</td>
                                    </tr>
                                    <tr>
                                        <td>2024-01-10</td>
                                        <td>Data Analyst</td>
                                        <td>85%</td>
                                    </tr>
                                    <tr>
                                        <td>2024-01-05</td>
                                        <td>UX Designer</td>
                                        <td>78%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Personal Details Section -->
                <div class="content-section" id="personal-details">
                    <div class="card">
                        <h3>Personal Information</h3>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Full Name
                            </label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($fullname) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                Email
                            </label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($email) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                Contact
                            </label>
                            <input type="tel" class="form-input" value="<?= htmlspecialchars($contact) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Birthday
                            </label>
                            <input type="date" class="form-input"  value="<?= $bday ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                                Educational Background
                            </label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($educlvl) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                                Course/Strand
                            </label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($course) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                                Institution/School Name
                            </label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($school) ?>" disabled>
                        </div>
                    </div>
                </div>

            <!-- Edit Profile Section -->
<div class="content-section" id="edit-profile">
  <div class="card">
    <h3>Edit Profile</h3>

  <form id="editProfileForm" method="POST" action="update_profile.php">
      <div class="form-group">
        <label class="form-label">First Name</label>
        <input 
          type="text" 
          class="form-input" 
          id="editFirstName" 
          name="firstname"
          value="<?= htmlspecialchars($firstname) ?>" 
          oninput="generateFullName()" 
          required
        >
      </div>

      <div class="form-group">
        <label class="form-label">Last Name</label>
        <input 
          type="text" 
          class="form-input" 
          id="editLastName" 
          name="lastname"
          value="<?= htmlspecialchars($lastname) ?>" 
          oninput="generateFullName()" 
          required
        >
      </div>

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input 
          type="text" 
          class="form-input" 
          id="editFullName" 
          name="fullname"
          value="<?= htmlspecialchars($fullname) ?>" 
          disabled
        >
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input 
          type="email" 
          class="form-input" 
          id="editEmail" 
          name="email"
          value="<?= htmlspecialchars($email) ?>" 
          required
        >
      </div>

      <div class="form-group">
        <label class="form-label">Contact</label>
        <input 
          type="tel" 
          class="form-input" 
          id="editContact" 
          name="contact"
          value="<?= htmlspecialchars($contact) ?>"
        >
      </div>

     <div class="form-group">
  <label class="form-label">Birthday</label>
  <input type="date" class="form-input" name="bday" id="editBirthday"
         value="<?= htmlspecialchars($bday) ?>"
         onchange="calculateAge()">
</div>


      <div class="form-group">
       <label class="form-label">Educational Background</label>
            <select class="form-input" name="educlvl" id="editEducation">
                 <option value="select" <?= $educlvl === 'select' ? 'selected' : '' ?>>--select option--</option>
                <option value="High School" <?= $educlvl === 'High School' ? 'selected' : '' ?>>High School</option>
                <option value="College/University" <?= $educlvl === 'College/University' ? 'selected' : '' ?>>College/University</option>
                <option value="Postgraduate" <?= $educlvl === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
            </select>
      </div>

      <div class="form-group">
        <label class="form-label">Course/Strand</label>
        <input 
          type="text" 
          class="form-input" 
          id="editcourse" 
          name="course"
          value="<?= htmlspecialchars($course) ?>"
        >
      </div>

      <div class="form-group">
        <label class="form-label">Institution/School Name</label>
        <input 
          type="text" 
          class="form-input" 
          id="editschool" 
          name="school"
          value="<?= htmlspecialchars($school) ?>"
        >
      </div>

      <div style="display: flex; gap: 15px; margin-top: 30px;">
        <button 
          type="button" 
          class="btn btn-primary" 
          onclick="showSaveConfirmation()"
        >Save Changes</button>

        <button 
          type="button" 
          class="btn btn-danger" 
          onclick="showResetConfirmation()"
        >Reset</button>
      </div>
    </form>
  </div>
</div>


   <!-- Change Password Section -->
<div class="content-section" id="change-password">
  <div class="card">
    <h3>Change Password</h3>
    <form id="changePasswordForm">

      <div class="form-group">
        <label class="form-label">Current Password</label>
        <div class="password-input-container">
          <input type="password" class="form-input" id="current">
          <button type="button" class="password-toggle" onclick="togglePassword('current')">
            <svg id="eye-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>
      </div>

      <!-- New Password -->
      <div class="form-group">
        <label class="form-label">New Password</label>
        <div class="password-input-container">
         <input type="password" class="form-input" id="new" oninput="checkPasswordStrength()">

          <button type="button" class="password-toggle" onclick="togglePassword('new')">
            <svg id="eye-new" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>
      </div>

      <div class="password-strength">
  <div class="strength-bar">
    <div class="strength-fill" id="strengthFill"></div>
  </div>
  <p id="strengthText" style="margin: 5px 0; font-size: 14px;"></p>
</div>


      <!-- Confirm New Password -->
      <div class="form-group">
        <label class="form-label">Confirm New Password</label>
        <div class="password-input-container">
          <input type="password" class="form-input" id="confirm">
          <button type="button" class="password-toggle" onclick="togglePassword('confirm')">
            <svg id="eye-confirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>
        <div class="password-match" id="passwordMatch" style="margin-top: 8px; font-size: 14px; display: none;">
          <span id="matchIcon">❌</span>
          <span id="matchText">Passwords do not match</span>
        </div>
      </div>
                            
                            <div class="password-requirements">
                                <h4 style="margin-bottom: 10px; color: #374151;">Password Requirements:</h4>
                                <div class="requirement" id="req-length">
                                    <div class="requirement-icon">✓</div>
                                    <span>At least 8 characters</span>
                                </div>
                                <div class="requirement" id="req-uppercase">
                                    <div class="requirement-icon">✓</div>
                                    <span>Uppercase letter</span>
                                </div>
                                <div class="requirement" id="req-lowercase">
                                    <div class="requirement-icon">✓</div>
                                    <span>Lowercase letter</span>
                                </div>
                                <div class="requirement" id="req-number">
                                    <div class="requirement-icon">✓</div>
                                    <span>Number</span>
                                </div>
                                <div class="requirement" id="req-special">
                                    <div class="requirement-icon">✓</div>
                                    <span>Special character</span>
                                </div>
                            </div>
                            
                            <div style="margin-top: 30px;">
                                <button type="button" class="btn btn-primary" onclick="showPasswordChangeConfirmation()">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Applied Jobs Section -->
                <div class="content-section" id="applied-jobs">
                    <div class="card">
                        <div class="title-row">
                        <h3>Applied Jobs</h3>
                        <button class="add-job-btn" onclick="window.location.href='browse.php?userid=<?php echo $_SESSION['userid']; ?>';"> + apply job
                      </button>
                        </div>
                        <div class="filter-controls">
                            <input type="text" class="form-input search-input" placeholder="Search by job Position..." id="jobSearch">
                            <div class="filter-buttons">
                                <button class="filter-btn active" data-status="all">All</button>
                                <button class="filter-btn" data-status="pending">Pending</button>
                                <button class="filter-btn" data-status="initial-interview">Initial Interview</button>
                                <button class="filter-btn" data-status="technical-interview">Technical Interview</button>  
                                <button class="filter-btn" data-status="job-offer">Job Offer</button>
                                <button class="filter-btn" data-status="job-offer-accepted">Job Offer Accepted</button>
                                <button class="filter-btn" data-status="job-offer-rejected">Job Offer Rejected</button>
                                <button class="filter-btn" data-status="failed">Failed</button>
                            </div>
                        </div>
                        
                        <!-- Results Info -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <div id="resultsInfo" style="color: #6b7280; font-size: 14px;">
                                Showing 1-5 of 15 applications
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label style="color: #6b7280; font-size: 14px;">Show:</label>
                                <select id="itemsPerPage" style="padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                                    <option value="5">5 per page</option>
                                    <option value="10">10 per page</option>
                                    <option value="15">15 per page</option>
                                    <option value="20">20 per page</option>
                                </select>
                            </div>
                        </div>
                      <?php include 'applied.php'; ?>

                  <div class="table-responsive">
                    <table class="applied">
    <thead>
      <tr>
        <th>Job Position</th>
        <th>Type</th>
        <th>Date Applied</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="applications-tbody">
      <?php if (!empty($applications)): ?>
        <?php foreach ($applications as $app): ?>
          <?php setlocale(LC_TIME, 'en_US.UTF-8'); ?>

          <?php
            // Robust normalize server-side so the label is correct regardless of capitalization/whitespace
            // Normalize and detect any variation of "Job Offer"
$raw_status = isset($app['status']) ? $app['status'] : '';
$is_job_offer = preg_match('/job\s*offer/i', $raw_status);
$button_label = $is_job_offer ? 'View Job' : 'View';
            $resumeLabel = !empty($app['resume']) ? 'Uploaded Resume' : 'No file uploaded';
            $app_fullname = !empty($app['fullname']) ? $app['fullname'] : 'Applicant';
            $app_email = !empty($app['email']) ? $app['email'] : '';
          ?>

          <tr>
            <td><?= htmlspecialchars($app['job_role']); ?></td>
            <td><?= htmlspecialchars($app['job_type']); ?></td>
            <td><?= !empty($app['date_applied']) ? strftime("%B %e, %Y", strtotime($app['date_applied'])) : 'N/A'; ?></td>
            <td><?= !empty($app['post_deadline']) ? strftime("%B %e, %Y", strtotime($app['post_deadline'])) : 'N/A'; ?></td>
            <td><?= htmlspecialchars($raw_status); ?></td>
            <td>
              <button 
                type="button" 
                class="actionbtn"
                 data-job-role="<?= htmlspecialchars($app['job_role']) ?>"
                data-job-type="<?= htmlspecialchars($app['job_type']) ?>"
                data-date-applied="<?= htmlspecialchars($app['date_applied']) ?>"
                data-deadline="<?= htmlspecialchars($app['post_deadline']) ?>"
                data-status="<?= htmlspecialchars($app['status']) ?>"
                data-worksetup="<?= htmlspecialchars($app['post_worksetup']) ?>"
                data-cv-name="<?= !empty($app['resume']) ? 'Uploaded Resume' : 'No file uploaded' ?>"
                data-application-id="<?= htmlspecialchars($app['applicationid']) ?>"
                data-job-summary="<?= $app['post_summary'];?>"
                data-job-specification="<?= $app['post_specification'];?>"
                data-job-salary="<?= htmlspecialchars($app['post_salary']) ?>"
                data-job-address="<?= htmlspecialchars($app['post_address']) ?>"
              >
                <?= htmlspecialchars($button_label); ?>
              </button>
            </td>
          </tr>

        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No applications found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

                        <!-- Pagination Controls -->
                        <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <div id="paginationInfo" style="color: #6b7280; font-size: 14px;">
                                Page 1 of 3
                            </div>
                            <div class="pagination-controls" style="display: flex; gap: 5px;">
                                <button id="firstPageBtn" class="pagination-btn" onclick="goToPage(1)" title="First Page">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="11,17 6,12 11,7"></polyline>
                                        <polyline points="18,17 13,12 18,7"></polyline>
                                    </svg>
                                </button>
                                <button id="prevPageBtn" class="pagination-btn" onclick="previousPage()" title="Previous Page">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15,18 9,12 15,6"></polyline>
                                    </svg>
                                </button>
                                <div id="pageNumbers" style="display: flex; gap: 5px;">
                                    <!-- Page numbers will be generated by JavaScript -->
                                </div>
                                <button id="nextPageBtn" class="pagination-btn" onclick="nextPage()" title="Next Page">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9,18 15,12 9,6"></polyline>
                                    </svg>
                                </button>
                                <button id="lastPageBtn" class="pagination-btn" onclick="goToLastPage()" title="Last Page">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="13,17 18,12 13,7"></polyline>
                                        <polyline points="6,17 11,12 6,7"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<!-- Profile Photo Modal -->
<div class="modal" id="profileModal">
  <div class="modal-content">
    <div class="modal-header">
        <button class="close-btn" onclick="closeModal('profileModal')">&times;</button>
      <h3 class="modal-title">Profile Photo</h3>
    
    </div>
    <div style="text-align: center;">
    <div class="avatar-circle" 
     id="modalAvatar" 
     style="margin: 0 auto 20px;"
     data-initials="<?= htmlspecialchars(strtoupper(substr($firstname,0,1).substr($lastname,0,1))) ?>">
        <?php if (!empty($profileImage)): ?>
    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image" class="profile-image">
  <?php else: ?>
    <?= htmlspecialchars(strtoupper(substr($firstname,0,1).substr($lastname,0,1))) ?>
  <?php endif; ?>
      </div>
      <div style="display: flex; gap: 15px; justify-content: center;">
        <button type="button" class="btn btn-primary" onclick="uploadPhoto()">Upload Photo</button>
        <button type="button" class="btn btn-secondary" onclick="removePhoto()">Remove Photo</button>
      </div>
    </div>
  </div>
</div>

    <!-- Save Confirmation Modal -->
    <div class="modal" id="saveConfirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close-btn" onclick="closeModal('saveConfirmModal')">&times;</button>
                <h3 class="modal-title">Save Changes</h3>
                
            </div>
            <p>Do you want to save changes?</p>
            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeModal('saveConfirmModal')">No</button>
            <button class="btn btn-primary" onclick="onConfirmSaveYes()">Yes</button>
            </div>
        </div>
    </div>

    <!-- Save Success Modal -->
    <div class="modal" id="saveSuccessModal">
        <div class="modal-content">
            <div class="modal-header">
               <button class="close-btn" onclick="closeModal('saveSuccessModal')">&times;</button>
                <h3 class="modal-title">Success</h3>
               
            </div>
            <p style="text-align: center; font-size: 18px;">Changes Saved Successfully ✅</p>
            <div style="text-align: center; margin-top: 20px;">
                   <button class="btn btn-primary" onclick="onSuccessOk()">OK</button>
            </div>
        </div>
    </div>

<!-- Reset Confirmation Modal -->
<div class="modal" id="resetConfirmModal">
  <div class="modal-content">
    <div class="modal-header">
        <button class="close-btn" onclick="closeModal('resetConfirmModal')">&times;</button>
      <h3 class="modal-title">⚠️ Confirm Reset</h3>
    
    </div>
    <p>Do you want to reset all information?</p>
    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
      <button class="btn btn-secondary" onclick="closeModal('resetConfirmModal')">No</button>
      <form method="POST" action="reset_profile.php" style="display:inline;">
        <button class="btn btn-danger" type="submit">Yes</button>
      </form>
    </div>
  </div>
</div>
<?php if (isset($_GET['reset']) && $_GET['reset'] == 1): ?>
  <script>alert('Your profile information has been cleared successfully.');</script>
<?php endif; ?>



    <!-- Password Change Confirmation Modal -->
    <div class="modal" id="passwordConfirmModal">
        <div class="modal-content">
            <div class="modal-header">
                  <button class="close-btn" onclick="closeModal('passwordConfirmModal')">&times;</button>
                <h3 class="modal-title">Change Password</h3>
            </div>
            <p>Are you sure you want to change your password? You will be logged out after changing.</p>
            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeModal('passwordConfirmModal')">Cancel</button>
                <button class="btn btn-primary" onclick="changePassword()">Yes</button>
            </div>
        </div>
    </div>

    <!-- Password Success Modal -->
    <div class="modal" id="passwordSuccessModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Password Changed</h3>
            </div>
            <p style="text-align: center; font-size: 18px;">Password changed successfully! You will be logged out in 3 seconds...</p>
        </div>
    </div>

 <!-- Age Validation Modal -->
<div class="modal" id="ageValidationModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">⚠️ Invalid Age</h3>
    </div>
    <p style="text-align: center; font-size: 18px;">You must be 15 years or older to use this platform.</p>
    <div style="text-align: center; margin-top: 20px;">
      <button class="btn btn-secondary" onclick="clearBirthday()">Cancel</button>
    </div>
  </div>
</div>


<!-- ================== JOB DETAILS MODAL ================== -->
<div class="jobmodal" id="jobDetailsModal" style="display:none;">
  <div class="jobmodal-content">
    <div class="jobmodal-header">
      <h3 class="jobmodal-title">Job Application Details</h3>
      <button class="close-btn">&times;</button>
    </div>

    <div class="info-section">
      <h4>Application Information</h4>
      <div class="info-grid application-info-grid">
        <div><strong>Application ID:</strong> <p id="applicationIdText"></p></div>
        <div><strong>Status:</strong> <p id="appStatusText"></p></div>
        <div><strong>Applied Date:</strong> <p id="appliedDateText"></p></div>
        <div><strong>Deadline Date:</strong> <p id="deadlineDateText"></p></div>
         <div><strong>Work Setup:</strong> <p id="workSetupText"></p></div>
      </div>
    </div>

    <div class="info-section">
      <h4>Job Position Information</h4>
      <div class="info-grid job-info-grid">
        <div><strong>Job Position:</strong> <p id="jobRoleText"></p></div>
        <div><strong>Employment Type:</strong> <p id="jobTypeText"></p></div>
        <div><strong>Salary:</strong> <p id="jobSalaryText"></p></div>
        <div><strong>Address:</strong> <p id="jobAddressText"></p></div>
      </div>
    </div>
    
    <div class="info-section">
      <h4>Job Summary</h4>
      <p id="jobsummaryText" class="job-long-text"></p>
    </div>

    <div class="info-section">
      <h4>Job Specifications</h4>
      <p id="jobspecificationText" class="job-long-text"></p>
    </div>

    <div class="info-section">
      <h4>Submitted Resume</h4>
      <p id="cvNameText"></p>
      <div class="view-btn-container">
        <button class="view-btn" id="viewCvBtn">View CV</button>
      </div>
    </div>
  </div>
</div>


<!-- ===================== ACCEPTANCE MODAL ===================== -->
<div class="custom-modal" id="acceptanceModal" tabindex="-1" role="dialog" aria-labelledby="acceptanceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="custom-modal-content">
      <div class="modal-header">
        <h4 class="customtitle" id="acceptanceModalLabel">Job Offer Confirmation</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Dear <strong id="applicantNameModal">Applicant</strong>,</p>
        <p>
          We are delighted to extend this offer for the position of 
          <strong><span id="jobTitleModal">[Job Title]</span></strong> at Philkoei International.
          Kindly confirm your decision regarding this employment opportunity.
        </p>
        <p>Do you wish to formally accept this job offer?</p>

        <input type="hidden" id="modal_application_id">
        <input type="hidden" id="modal_applicant_email">
        <input type="hidden" id="modal_applicant_name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="rejectOfferBtn">Reject Offer</button>
        <button type="button" class="btn btn-success" id="acceptOfferBtn">Accept Job Offer</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== REJECT CONFIRMATION MODAL ===================== -->
<div class="custom-modal reject-modal" id="rejectConfirmModal" tabindex="-1" role="dialog" aria-labelledby="rejectConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="custom-modal-content reject-content">
      <div class="modal-header reject-header">
        <h4 class="customtitle" id="rejectConfirmModalLabel">Confirm Rejection</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body reject-body">
        <p>Are you sure you want to <strong>reject</strong> this job offer?</p>
        <input type="hidden" id="reject_application_id">
      </div>
      <div class="modal-footer reject-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmRejectBtn">Yes, Reject</button>
      </div>
    </div>
  </div>
</div>

<!-- Congrats Modal -->
<div class="modal fade" id="acceptedModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <h4 class="text-success mb-3">🎉 Congratulations!</h4>
      <p>You have accepted this position. Please check your email for further details.</p>
      <button type="button" class="btn btn-success mt-3" data-dismiss="modal">OK</button>
    </div>
  </div>
</div>

<!-- Rejected Modal -->
<div class="modal fade" id="rejectedModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <h4 class="text-danger mb-3">❌ You Rejected This Position</h4>
      <p>Apply for more opportunities on our site to find the right job for you.</p>
      <button type="button" class="btn btn-danger mt-3" data-dismiss="modal">OK</button>
    </div>
  </div>
</div>


    <script>
async function loadTestHistory() {
    try {
        const response = await fetch('../api/get_test_history.php');
        const data = await response.json();
        
        if (data.success && data.test_history.length > 0) {
            populateTestHistoryTable(data.test_history);
            updateTestHistoryStats(data.test_history);
        } else {
            showNoTestHistory();
        }
    } catch (error) {
        console.error('Error loading test history:', error);
        showTestHistoryError();
    }
}

function populateTestHistoryTable(testHistory) {
    const tbody = document.querySelector('.historytable tbody');
    
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    testHistory.forEach(test => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(test.date)}</td>
            <td>${test.suggested_job}</td>
            <td>${test.match_score}</td>
        `;
        tbody.appendChild(row);
    });
}

function updateTestHistoryStats(testHistory) {
    if (testHistory.length > 0) {
        const latestTest = testHistory[0];
        
        const recommendedSection = document.querySelector('#test-history .card:first-child');
        if (recommendedSection) {
            console.log('Latest test result:', latestTest);
        }
    }
}


function showNoTestHistory() {
    const tbody = document.querySelector('.historytable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #6b7280;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 10px;">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                    </svg>
                    <div>No test history found</div>
                    <small>Take your first career assessment test to see your results here.</small>
                </td>
            </tr>
        `;
    }
}


function showTestHistoryError() {
    const tbody = document.querySelector('.historytable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #dc2626;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 10px;">
                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>Error loading test history</div>
                    <small>Please try refreshing the page.</small>
                </td>
            </tr>
        `;
    }
}


function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}


document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('test-history').classList.contains('active')) {
        loadTestHistory();
    }
    
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (this.getAttribute('data-section') === 'test-history') {
                setTimeout(loadTestHistory, 100); 
            }
        });
    });
});

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();    
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });


function openProfileModal() {
  document.getElementById('profileModal').classList.add('show');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
}

function uploadPhoto() {
  document.getElementById('photoInput').click();
}

function handlePhotoUpload(event) {
  const file = event.target.files[0];
  const profileAvatar = document.getElementById('profileAvatar');
  const modalAvatar = document.getElementById('modalAvatar');

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      profileAvatar.innerHTML = `<img src="${e.target.result}" alt="Profile Image" class="profile-image">`;
      modalAvatar.innerHTML = `<img src="${e.target.result}" alt="Profile Image" class="profile-image">`;
    };
    reader.readAsDataURL(file);
    document.getElementById('profileForm').submit();
  } else {
    const initials = profileAvatar.getAttribute('data-initials');
    if (initials) {
      profileAvatar.innerHTML = initials;
      modalAvatar.innerHTML = initials;
    }
  }

  closeModal('profileModal');
}


function removePhoto() {
  document.getElementById('remove_photo').value = '1';
  document.getElementById('profileForm').submit();
  closeModal('profileModal');
}

      function showSaveConfirmation() {
    document.getElementById('saveConfirmModal').classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
function onConfirmSaveYes() {
    closeModal('saveConfirmModal');
    document.getElementById('saveSuccessModal').classList.add('show');
}
function onSuccessOk() {
    closeModal('saveSuccessModal');
    document.getElementById('editProfileForm').submit();
}
function showResetConfirmation() {
    document.getElementById('resetConfirmModal').classList.add('show');
}
function resetProfileData() {
    const form = document.getElementById('editProfileForm');
    const inputs = form.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        if (input.type === 'date') {
            input.value = ''; 
        } else if (input.tagName.toLowerCase() === 'select') {
            input.selectedIndex = 0; 
        } else {
            input.value = ''; 
        }
    });

    form.submit();
}

function generateFullName() {
    const fn = document.getElementById('editFirstName').value.trim();
    const ln = document.getElementById('editLastName').value.trim();
    document.getElementById('editFullName').value = (fn + ' ' + ln).trim();
}

function calculateAge() {
    const birthdayInput = document.getElementById('editBirthday');
    const birthdayValue = birthdayInput.value;

    if (!birthdayValue) {
        return;
    }

    const birthday = new Date(birthdayValue);
    const today = new Date();


    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }
    if (age < 15) {
        document.getElementById('ageValidationModal').classList.add('show');
    }
}

function clearBirthday() {
    const birthdayInput = document.getElementById('editBirthday');
    birthdayInput.value = '';
    closeModal('ageValidationModal');
}

     function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const eyeIcon = document.getElementById(`eye-${inputId}`);

  if (input.type === 'password') {
    input.type = 'text';
    eyeIcon.outerHTML = `
      <svg id="eye-${inputId}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
      </svg>
    `;
  } else {
    input.type = 'password';
    eyeIcon.outerHTML = `
      <svg id="eye-${inputId}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
      </svg>
    `;
  }
}

        function checkPasswordStrength() {
            const password = document.getElementById('new').value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>_]/.test(password)
            };
            
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(`req-${req}`);
                if (requirements[req]) {
                    element.classList.add('met');
                } else {
                    element.classList.remove('met');
                }
            });

            const metRequirements = Object.values(requirements).filter(Boolean).length;
            
            if (metRequirements < 3) {
                strengthFill.className = 'strength-fill strength-weak';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#dc2626';
            } else if (metRequirements < 5) {
                strengthFill.className = 'strength-fill strength-medium';
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthFill.className = 'strength-fill strength-strong';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#10b981';
            }
            
            checkPasswordMatch();
        }

       function checkPasswordMatch() {
    const newPassword = document.getElementById('new').value;
    const confirmPassword = document.getElementById('confirm').value;
    const matchIndicator = document.getElementById('passwordMatch');
    const matchIcon = document.getElementById('matchIcon');
    const matchText = document.getElementById('matchText');

    if (confirmPassword.length > 0) {
      matchIndicator.style.display = 'flex';
      matchIndicator.style.alignItems = 'center';
      matchIndicator.style.gap = '5px';

      if (newPassword === confirmPassword) {
        matchIcon.textContent = '✅';
        matchText.textContent = 'Passwords match';
        matchText.style.color = '#10b981'; 
      } else {
        matchIcon.textContent = '❌';
        matchText.textContent = 'Passwords do not match';
        matchText.style.color = '#dc2626';
      }
    } else {
      matchIndicator.style.display = 'none';
    }
  }

  document.getElementById('new').addEventListener('input', checkPasswordMatch);
  document.getElementById('confirm').addEventListener('input', checkPasswordMatch);

  function showPasswordChangeConfirmation() {
    document.getElementById('passwordConfirmModal').classList.add('show');
  }
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function changePassword() {
    const current = document.getElementById('current').value.trim();
    const newPass = document.getElementById('new').value.trim();
    const confirmPass = document.getElementById('confirm').value.trim();

    closeModal('passwordConfirmModal');

    if (!current || !newPass || !confirmPass) {
        alert('Please fill out all fields.');
        return;
    }

    fetch('userpass.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            current: current,
            new: newPass,
            confirm: confirmPass
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('passwordSuccessModal').classList.add('show');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('jobDetailsModal');
  const tableBody = document.getElementById('applications-tbody');
  const closeButton = modal.querySelector('.close-btn');
  const infoSections = modal.querySelectorAll('.info-section');
  const viewCvBtn = document.getElementById('viewCvBtn');

  let currentApplicationId = null;

  const getRowValues = (button) => {
    const ds = button.dataset;
    // Note: We prioritize data attributes as they hold the complete, original data
    return {
      jobRole: ds.jobRole || 'N/A',
      jobType: ds.jobType || 'N/A',
      dateApplied: ds.dateApplied || 'N/A',
      deadline: ds.deadline || 'N/A',
      workSetup: ds.worksetup || 'N/A',
      status: ds.status || 'N/A',
      cvName: ds.cvName || 'No file uploaded',
      applicationId: ds.applicationId || null,
      jobSummary: ds.jobSummary || 'No summary provided.',
      jobSpecification: ds.jobSpecification || 'No specification listed.',
      jobSalary: ds.jobSalary || 'Not specified',
      jobAddress: ds.jobAddress || 'Not specified',
    };
  };

  const setText = (id, content) => {
      const element = document.getElementById(id);
      if (element) element.textContent = content || 'N/A';
  };
  
  const openJobDetailsModal = (button) => {
    const data = getRowValues(button);
    currentApplicationId = data.applicationId;

    // --- Application Information ---
    setText('applicationIdText', data.applicationId);
    setText('appStatusText', data.status.charAt(0).toUpperCase() + data.status.slice(1)); // Capitalize status
    setText('appliedDateText', data.dateApplied);
    setText('deadlineDateText', data.deadline);
    setText('workSetupText', data.workSetup);

    // --- Job Position Information ---
    setText('jobRoleText', data.jobRole);
    setText('jobTypeText', data.jobType);
    // Add currency/formatting to salary if applicable (simple check here)
    setText('jobSalaryText', data.jobSalary ? '₱' + data.jobSalary : 'Not specified'); 
    setText('jobAddressText', data.jobAddress);
    
    // --- Job summary & specification ---
const jobSummaryEl = document.getElementById('jobsummaryText');
const jobSpecEl = document.getElementById('jobspecificationText');

if (jobSummaryEl) jobSummaryEl.innerHTML = data.jobSummary || '<em>No summary provided.</em>';
if (jobSpecEl) jobSpecEl.innerHTML = data.jobSpecification || '<em>No specification listed.</em>';


    setText('cvNameText', data.cvName);
    
    const viewCvBtn = document.getElementById('viewCvBtn');
    if (data.cvName === 'No file uploaded' || !data.applicationId) {
      viewCvBtn.disabled = true;
      viewCvBtn.style.opacity = '0.6';
      viewCvBtn.style.cursor = 'not-allowed';
    } else {
      viewCvBtn.disabled = false;
      viewCvBtn.style.opacity = '1';
      viewCvBtn.style.cursor = 'pointer';
    }

    modal.style.display = 'flex';
  };

  const closeModal = () => {
    modal.style.display = 'none';
    currentApplicationId = null;
  };


  tableBody.addEventListener('click', (event) => {
    const btn = event.target.closest('.actionbtn');
    if (!btn) return;
    event.preventDefault();
    openJobDetailsModal(btn);
  });


  closeButton.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  viewCvBtn.addEventListener('click', () => {
    if (!currentApplicationId) {
      alert('Application ID missing. Cannot open resume.');
      return;
    }
    const cacheBuster = new Date().getTime();
    const url = `view_resume.php?applicationid=${encodeURIComponent(currentApplicationId)}&v=${cacheBuster}`;
    window.open(url, '_blank');
  });
});

let jobsData = [];
let filteredJobs = [...jobsData];
let currentPage = 1;
let itemsPerPage = 5;

document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.applied tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const viewButton = cells[5]?.querySelector('.actionbtn');

        if (viewButton) {
            const ds = viewButton.dataset;
            const job = {
                title: ds.jobRole,
                type: ds.jobType,
                dateApplied: ds.dateApplied,
                deadline: ds.deadline,
                postworksetup: ds.worksetup,
                status: ds.status.toLowerCase(),
                resumeId: ds.applicationId, 
                cvName: ds.cvName,
                
              
                postsummary: ds.jobSummary,
                postspecification: ds.jobSpecification,
                postSalary: ds.jobSalary,
                postAddress: ds.jobAddress
            };
            jobsData.push(job);
        } else if (cells.length === 6) {
             const job = {
                 title: cells[0].textContent.trim(),
                 type: cells[1].textContent.trim(),
                 dateApplied: cells[2].textContent.trim(),
                 deadline: cells[3].textContent.trim(),
                 status: cells[4].textContent.trim().toLowerCase(),
                 resumeId: null, 
                 cvName: 'No file uploaded',
                 // NEW: Placeholder for missing data
                 postsummary: 'N/A',
                 postspecification: 'N/A',
                 postSalary: 'N/A',
                 postworksetup:'N/A',
                 postAddress: 'N/A'
             };
             jobsData.push(job);
        }
    });

    filteredJobs = [...jobsData];

   document.getElementById('itemsPerPage').addEventListener('change', e => {
        itemsPerPage = parseInt(e.target.value);
        currentPage = 1;
        renderTable();
        renderPagination();
    });

    document.getElementById('jobSearch').addEventListener('input', filterJobs);
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterJobs();
        });
    });

    renderTable();
    renderPagination();
});

function filterJobs() {
    const searchTerm = document.getElementById('jobSearch').value.toLowerCase();
    const activeStatus = document.querySelector('.filter-btn.active').dataset.status;

    const normalize = str => str.toLowerCase().replace(/\s+/g, '-');

    filteredJobs = jobsData.filter(job => {
        const jobTitle = job.title?.toLowerCase() || '';
        const jobDesc = job.postsummary?.toLowerCase() || '';
        const jobAddr = job.postAddress?.toLowerCase() || '';

        const matchesSearch =
            jobTitle.includes(searchTerm) ||
            jobDesc.includes(searchTerm) ||
            jobAddr.includes(searchTerm);

        const jobStatus = normalize(job.status || '');
        const matchesStatus = activeStatus === 'all' || jobStatus === activeStatus;

        return matchesSearch && matchesStatus;
    });

    currentPage = 1;
    renderTable();
    renderPagination();
}


function renderTable() {
    const tbody = document.querySelector('.applied tbody');
    tbody.innerHTML = '';

    if (filteredJobs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="no-data">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                    </svg>
                    <div>No applications found</div>
                    <small>No results match your search/filter.</small>
                </td>
            </tr>`;
        document.getElementById('resultsInfo').textContent = '';
        document.getElementById('paginationInfo').textContent = '';
        document.querySelector('.pagination-controls').style.display = 'none';
        return;
    }

    document.querySelector('.pagination-controls').style.display = 'flex';
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredJobs.slice(startIndex, endIndex);

    pageData.forEach(job => {
        const row = document.createElement('tr');
        const cvName = job.resumeId ? 'Uploaded Resume' : 'No file uploaded';
        const statusClass = job.status.replace(/\s+/g, '-').toLowerCase(); // Handle multi-word statuses

        row.innerHTML = `
            <td>${job.title}</td>
            <td>${job.type}</td>
            <td>${job.dateApplied}</td>
            <td>${job.deadline}</td>
            <td><span class="status ${statusClass}">${job.status.charAt(0).toUpperCase() + job.status.slice(1)}</span></td>
            <td>
              <button
                type="button"
                class="actionbtn"
                data-job-role="${job.title}"
                data-job-type="${job.type}"
                data-date-applied="${job.dateApplied}"
                data-deadline="${job.deadline}"
                data-status="${job.status}"
                data-worksetup="${job.postworksetup}"
                data-cv-name="${cvName}"
                data-application-id="${job.resumeId || ''}"
                data-job-summary="${job.postsummary || ''}"
                data-job-specification="${job.postspecification || ''}"
                data-job-salary="${job.postSalary || ''}"
                data-job-address="${job.postAddress || ''}"
              >
                View
              </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    updateResultsInfo();
}


function updateResultsInfo() {
    const startIndex = (currentPage - 1) * itemsPerPage + 1;
    const endIndex = Math.min(currentPage * itemsPerPage, filteredJobs.length);
    const total = filteredJobs.length;
    document.getElementById('resultsInfo').textContent =
        `Showing ${startIndex}-${endIndex} of ${total} applications`;
}

function renderPagination() {
    const totalPages = Math.ceil(filteredJobs.length / itemsPerPage);
    const paginationInfo = document.getElementById('paginationInfo');
    paginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;

    document.getElementById('firstPageBtn').disabled = currentPage === 1;
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
    document.getElementById('lastPageBtn').disabled = currentPage === totalPages;

    const container = document.getElementById('pageNumbers');
    container.innerHTML = '';

    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        addPageButton(1);
        if (startPage > 2) addEllipsis();
    }

    for (let i = startPage; i <= endPage; i++) addPageButton(i);

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) addEllipsis();
        addPageButton(totalPages);
    }
}

function addPageButton(pageNum) {
    const btn = document.createElement('button');
    btn.className = `page-number-btn ${pageNum === currentPage ? 'active' : ''}`;
    btn.textContent = pageNum;
    btn.onclick = () => goToPage(pageNum);
    document.getElementById('pageNumbers').appendChild(btn);
}

function addEllipsis() {
    const span = document.createElement('span');
    span.textContent = '...';
    span.className = 'page-ellipsis';
    document.getElementById('pageNumbers').appendChild(span);
}

function goToPage(pageNum) {
    const totalPages = Math.ceil(filteredJobs.length / itemsPerPage);
    if (pageNum >= 1 && pageNum <= totalPages) {
        currentPage = pageNum;
        renderTable();
        renderPagination();
    }
}

function previousPage() {
    if (currentPage > 1) goToPage(currentPage - 1);
}

function nextPage() {
    const totalPages = Math.ceil(filteredJobs.length / itemsPerPage);
    if (currentPage < totalPages) goToPage(currentPage + 1);
}

function goToLastPage() {
    const totalPages = Math.ceil(filteredJobs.length / itemsPerPage);
    goToPage(totalPages);
}


        function drawPieChart() {
            const canvas = document.getElementById('skillChart');
            const ctx = canvas.getContext('2d');
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = 80;
            
            const data = [
                { label: 'Logical Reasoning', value: 30, color: '#2563eb' },
                { label: 'Problem Solving', value: 25, color: '#7c3aed' },
                { label: 'Technical Skills', value: 20, color: '#10b981' },
                { label: 'Communication', value: 15, color: '#f59e0b' },
                { label: 'Creativity', value: 10, color: '#ef4444' }
            ];
            
            let currentAngle = -Math.PI / 2;
            

            data.forEach(segment => {
                const sliceAngle = (segment.value / 100) * 2 * Math.PI;

                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
                ctx.closePath();
                ctx.fillStyle = segment.color;
                ctx.fill();
                
        
                const labelAngle = currentAngle + sliceAngle / 2;
                const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
                const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);
                
                ctx.fillStyle = 'white';
                ctx.font = 'bold 12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(`${segment.value}%`, labelX, labelY);
                
                currentAngle += sliceAngle;
            });
            

            const legendContainer = document.getElementById('chartLegend');
            legendContainer.innerHTML = '';
            
            data.forEach(segment => {
                const legendItem = document.createElement('div');
                legendItem.style.cssText = 'display: flex; align-items: center; margin: 8px 0; font-size: 14px;';
                
                const colorBox = document.createElement('div');
                colorBox.style.cssText = `width: 16px; height: 16px; background-color: ${segment.color}; margin-right: 10px; border-radius: 2px;`;
                
                const label = document.createElement('span');
                label.textContent = `${segment.label} (${segment.value}%)`;
                label.style.color = '#374151';
                
                legendItem.appendChild(colorBox);
                legendItem.appendChild(label);
                legendContainer.appendChild(legendItem);
            });
        }

        window.addEventListener('load', function() {
            drawPieChart();
            initializePagination();
        });
    </script>
<script>
(function(){
  document.addEventListener('DOMContentLoaded', function () {

    const tbody = document.getElementById('applications-tbody');
    const acceptBtn = document.getElementById('acceptOfferBtn');
    const rejectBtn = document.getElementById('rejectOfferBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');

    tbody.addEventListener('click', function(e) {
      const btn = e.target.closest('.actionbtn');
      if (!btn) return;

      const statusRaw = btn.getAttribute('data-status') || '';
      const status = statusRaw.trim().toLowerCase();

      const jobRole = btn.dataset.jobRole || '[Job Title]';
      const firstName = btn.dataset.applicantFirstname || '';
      const lastName = btn.dataset.applicantLastname || '';
      const applicantName = `${capitalize(firstName)} ${capitalize(lastName)}`.trim() || 'Applicant';
      const applicationId = btn.dataset.applicationId || '';
      const applicantEmail = btn.dataset.applicantEmail || '';

      if (status === 'job offer accepted') {
        closeAllModals();
        showTopModal('#acceptedModal');
        return;
      }

      if (status === 'job offer rejected') {
        closeAllModals();
        showTopModal('#rejectedModal');
        return;
      }


      const isJobOffer = (status === 'job offer');
      
      if (isJobOffer) {
        document.getElementById('jobTitleModal').textContent = jobRole;
        document.getElementById('applicantNameModal').textContent = applicantName;
        document.getElementById('modal_application_id').value = applicationId;
        document.getElementById('modal_applicant_email').value = applicantEmail;
        document.getElementById('modal_applicant_name').value = applicantName;

        closeAllModals();
        $('#acceptanceModal').modal('show');
        return;
      }


      if (typeof openJobDetailsModal === 'function') {
        openJobDetailsModal(btn);
      } else {
      }
    });


    function capitalize(str) {
      return str ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase() : '';
    }

    function closeAllModals() {
      $('.modal').modal('hide');
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open').css('padding-right', '');
    }

    function showTopModal(selector) {
      setTimeout(() => {
        const $modal = $(selector);
        $modal.appendTo('body'); 
        $modal.modal({ backdrop: 'static', keyboard: false });
        $modal.modal('show');
        const highestZ = Math.max(...Array.from(document.querySelectorAll('*'))
          .map(el => +window.getComputedStyle(el).zIndex || 0));
        $modal.css('z-index', highestZ + 10);
        $('.modal-backdrop').last().css('z-index', highestZ + 5);
      }, 300);
    }


    acceptBtn.addEventListener('click', function () {
      const btn = this;
      const originalText = btn.innerHTML; 
      const appId = document.getElementById('modal_application_id').value;
      if (!appId) return alert('Missing application ID.');


      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;

      updateStatus(appId, 'Job Offer Accepted', function(success) {
        if (success) {
          closeAllModals();
          showTopModal('#acceptedModal');
        } else {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      });
    });


    rejectBtn.addEventListener('click', function () {
      const appId = document.getElementById('modal_application_id').value;
      if (!appId) return;
      document.getElementById('reject_application_id').value = appId;
      closeAllModals();
      $('#rejectConfirmModal').modal('show');
    });

    confirmRejectBtn.addEventListener('click', function() {
      const btn = this; 
      const originalText = btn.innerHTML; 
      const appId = document.getElementById('reject_application_id').value;
      if (!appId) return;


      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;

      updateStatus(appId, 'Job Offer Rejected', function(success) {
        if (success) {
          closeAllModals();
          showTopModal('#rejectedModal');
        } else {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      });
    });

    function updateStatus(applicationId, newStatus, callback) {
      fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ application_id: applicationId, status: newStatus })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const row = document.querySelector(`button[data-application-id="${applicationId}"]`)?.closest('tr');
          if (row) {
            row.querySelector('td:nth-child(5)').textContent = newStatus;
            const viewBtn = row.querySelector('.actionbtn');
            if(viewBtn) viewBtn.setAttribute('data-status', newStatus);
          }
          if (callback) callback(true);
        } else {
          alert(data.error || 'Error updating status.');
          if (callback) callback(false);
        }
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred while updating status.');
        if (callback) callback(false);
      });
    }

    $('#acceptedModal .btn, #rejectedModal .btn').on('click', function() {
      closeAllModals();
      setTimeout(() => {
        location.reload(true);
      }, 300);
    });

    $('#acceptedModal, #rejectedModal').on('hidden.bs.modal', function () {
      closeAllModals();
    });

  });
})();

async function renderJobRecommendationResults() {
    try {
        const response = await fetch('../api/get_test_history.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        console.log('API Response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        if (data.success) {
            if (data.latest_test) {
                renderRecommendationSection(data.latest_test);
            } else {
                showNoTestData();
            }
            
            if (data.test_history && data.test_history.length > 0) {
                renderTestHistoryTable(data.test_history);
            } else {
                showNoTestHistory();
            }
        } else {
            throw new Error(data.message || 'Failed to load test history');
        }
    } catch (error) {
        console.error('Error loading test history:', error);
        showTestHistoryError(error.message);
    }
}
function renderRecommendationSection(bestTest) {
    if (!bestTest.top_job) {
        showNoTestData();
        return;
    }

    const topJob = bestTest.top_job;
    
    const recommendationCard = document.querySelector('#test-history .card:first-child');
    if (recommendationCard) {
        recommendationCard.innerHTML = `
            <h3>Job Recommendation Result</h3>
            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <!-- Recommended Position Card -->
                <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb;">
                    <h4 style="margin: 0 0 10px; color: #1f2937;">Recommended Position</h4>
                    <div style="text-align: center;">
                        <p style="margin: 0 0 15px; font-weight: bold; color: #2563eb;">
                            <strong>Top 1: ${topJob.job} – ${topJob.match_percentage}%</strong>
                        </p>
                        <div style="font-size: 48px; color: #2563eb; margin: 10px 0;">💼</div>
                        <h3 style="margin: 10px 0; color: #1f2937;">${topJob.job}</h3>
                        <div style="font-size: 24px; font-weight: bold; color: #10b981; margin: 10px 0;">${topJob.match_percentage}% Match</div>
                        <p style="color: #6b7280; margin: 10px 0;">Based on your best test result from ${formatDate(bestTest.test_date)}</p>
                        
                        <!-- Additional Recommendations -->
                        ${bestTest.all_recommendations.length > 1 ? `
                            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                <h5 style="margin: 0 0 10px; color: #374151;">Other Recommendations:</h5>
                                ${bestTest.all_recommendations.slice(1).map((job, index) => `
                                    <div style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 14px;">
                                        <span>${index + 2}. ${job.job}</span>
                                        <span style="color: #059669; font-weight: 500;">${job.match_percentage}%</span>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Skill Breakdown Card -->
                <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb;">
                    <h4 style="margin: 0 0 15px; color: #1f2937; text-align: center;">Skill Breakdown</h4>
                    <div style="text-align: center;">
                        <canvas id="skillChart" width="200" height="200" style="display: block; margin: 0 auto;"></canvas>
                        <div id="chartLegend" style="margin-top: 20px; text-align: left; display: inline-block;"></div>
                    </div>
                </div>
            </div>
        `;

        if (bestTest.skills) {
            renderSkillChart(bestTest.skills);
        }
    }
}


function renderSkillChart(skills) {
    const canvas = document.getElementById('skillChart');
    if (!canvas) {
        console.error('Skill chart canvas not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = 80;
    

    const data = [
        { label: 'Logical Reasoning', value: skills['Logical Reasoning'], color: '#2563eb' },
        { label: 'Problem Solving', value: skills['Problem Solving'], color: '#7c3aed' },
        { label: 'Technical Skills', value: skills['Technical Skills'], color: '#10b981' },
        { label: 'Communication', value: skills['Communication'], color: '#f59e0b' },
        { label: 'Creativity', value: skills['Creativity'], color: '#ef4444' }
    ];
    

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    let currentAngle = -Math.PI / 2;
    const total = data.reduce((sum, item) => sum + item.value, 0);
    

    data.forEach(segment => {
        const sliceAngle = (segment.value / total) * 2 * Math.PI;

        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
        ctx.closePath();
        ctx.fillStyle = segment.color;
        ctx.fill();
        

        const percentage = ((segment.value / total) * 100).toFixed(0);
        const labelAngle = currentAngle + sliceAngle / 2;
        const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
        const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);
        
        ctx.fillStyle = 'white';
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(percentage + '%', labelX, labelY);
        
        currentAngle += sliceAngle;
    });
    

    renderChartLegend(data);
}

function renderChartLegend(data) {
    const legendContainer = document.getElementById('chartLegend');
    if (!legendContainer) {
        console.error('Chart legend container not found');
        return;
    }
    
    legendContainer.innerHTML = '';
    
    data.forEach(segment => {
        const percentage = ((segment.value / data.reduce((sum, item) => sum + item.value, 0)) * 100).toFixed(0);
        
        const legendItem = document.createElement('div');
        legendItem.style.cssText = 'display: flex; align-items: center; margin: 8px 0; font-size: 14px;';
        
        const colorBox = document.createElement('div');
        colorBox.style.cssText = `width: 16px; height: 16px; background-color: ${segment.color}; margin-right: 10px; border-radius: 2px;`;
        
        const label = document.createElement('span');
        label.textContent = `${segment.label} (${percentage}%)`;
        label.style.color = '#374151';
        
        legendItem.appendChild(colorBox);
        legendItem.appendChild(label);
        legendContainer.appendChild(legendItem);
    });
}


function renderTestHistoryTable(testHistory) {
    const tbody = document.querySelector('.historytable tbody');
    
    if (!tbody) {
        console.error('Test history table body not found');
        return;
    }
    
    tbody.innerHTML = ''; 
    
    testHistory.forEach(test => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(test.date)}</td>
            <td>${test.suggested_job}</td>
            <td>${test.match_score}</td>
        `;
        tbody.appendChild(row);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showNoTestData() {
    const recommendationCard = document.querySelector('#test-history .card:first-child');
    if (recommendationCard) {
        recommendationCard.innerHTML = `
            <h3>Job Recommendation Result</h3>
            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb; text-align: center;">
                    <h4 style="margin: 0 0 10px; color: #1f2937;">No Test Results</h4>
                    <div style="text-align: center;">
                        <div style="font-size: 48px; color: #6b7280; margin: 10px 0;">📊</div>
                        <h3 style="margin: 10px 0; color: #6b7280;">Take Your First Test</h3>
                        <p style="color: #6b7280; margin: 10px 0;">Complete the career assessment to get your personalized job recommendations</p>
                        <a href="yourtest.php" class="btn btn-primary" style="margin-top: 15px;">Take Test Now</a>
                    </div>
                </div>
                <div style="flex: 1; background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px solid #e5e7eb; text-align: center;">
                    <h4 style="margin: 0 0 15px; color: #1f2937;">Skill Breakdown</h4>
                    <div style="text-align: center;">
                        <div style="font-size: 48px; color: #6b7280; margin: 20px 0;">🎯</div>
                        <p style="color: #6b7280;">Your skill analysis will appear here after taking the test</p>
                    </div>
                </div>
            </div>
        `;
    }
}

function showNoTestHistory() {
    const tbody = document.querySelector('.historytable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 40px; color: #6b7280;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 15px;">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                    </svg>
                    <div style="font-size: 18px; margin-bottom: 10px;">No Test History Found</div>
                    <small>Take your first career assessment test to see your results here.</small>
                    <div style="margin-top: 20px;">
                        <a href="yourtest.php" class="btn btn-primary">Take Test Now</a>
                    </div>
                </td>
            </tr>
        `;
    }
}

function showTestHistoryError(errorMessage = '') {
    const tbody = document.querySelector('.historytable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #dc2626;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 10px;">
                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>Error loading test history</div>
                    <small>${errorMessage || 'Please try refreshing the page.'}</small>
                </td>
            </tr>
        `;
    }
}


document.addEventListener('DOMContentLoaded', function() {
    renderJobRecommendationResults();
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (this.getAttribute('data-section') === 'test-history') {
                setTimeout(renderJobRecommendationResults, 100);
            }
        });
    });
});


renderJobRecommendationResults();
</script>

<script>
    const dashboardContainer = document.getElementById('dashboardContainer');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    

    function toggleSidebar() {
        if (!dashboardContainer) return;
        dashboardContainer.classList.toggle('sidebar-open');
        if (dashboardContainer.classList.contains('sidebar-open')) {
            toggleIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
        } else {
            toggleIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
        }
    }
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    document.querySelectorAll('.nav-menu .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992 && dashboardContainer.classList.contains('sidebar-open')) {
                setTimeout(toggleSidebar, 100); 
            }
        });
    });

    if (dashboardContainer) {
        dashboardContainer.addEventListener('click', function(e) {
            if (window.innerWidth <= 992 && 
                dashboardContainer.classList.contains('sidebar-open') && 
                e.target === dashboardContainer) 
            {
                toggleSidebar();
            }
        });
    }

</script>
    </body>
</html>