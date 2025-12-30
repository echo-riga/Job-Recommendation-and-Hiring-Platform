<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ADMIN JOBFITSYSTEM</title>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <link rel="stylesheet" href="../css/applicants.css?v=2">
</head>
<style>
    /* Job match styling */
.job-match-highlight {
    background-color: #f0f9ff !important; /* Light blue background for matches */
    border-left: 4px solid #3b82f6;
}

.match-badge {
    display: inline-block;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7em;
    margin-left: 8px;
    font-weight: bold;
}

.no-match-badge {
    display: inline-block;
    background-color: #6b7280;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7em;
    margin-left: 8px;
    font-weight: bold;
}

.match-badge i,
.no-match-badge i {
    margin-right: 2px;
    font-size: 0.8em;
}

/* Ensure user cell can accommodate the badge */
.user-cell {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

/* Loading states */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px;
    color: #6b7280;
}

/* Match statistics */
.match-statistics {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
}

.match-statistics h4 {
    margin: 0 0 8px 0;
    color: #0369a1;
    font-size: 14px;
}

.match-statistics .stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    font-size: 13px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
}

.stat-value {
    font-weight: bold;
    color: #059669;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.btn-retry {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-retry:hover {
    background-color: #2563eb;
}
</style>
<body>
<?php
session_start();
include 'database.php';
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header('Location: admin_login.php');
  exit();
}

include 'admin_profile_modal.php'; 
$adminName = "Administrator";
$avatarInitials = "AD";
$adminImageBase64 = null; 
$adminEmail = $_SESSION['admin'];
$stmt = $con->prepare("SELECT adminname, adminpassword, adminimage FROM admin WHERE adminemail = ?");
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$res = $stmt->get_result();
$adminData = null;
if ($res->num_rows === 1) {
    $adminData = $res->fetch_assoc();
    $adminName = $adminData['adminname'];
    $nameParts = explode(" ", trim($adminName));
    $avatarInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $avatarInitials .= strtoupper(substr($nameParts[1], 0, 1));
    }
    if (!empty($adminData['adminimage'])) {
        $adminImageBase64 = 'data:image/jpeg;base64,' . base64_encode($adminData['adminimage']);
    }
}
$stmt->close();

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

$message = '';
$message_type = ''; // 'success' or 'error'

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

?>

<button class="burger" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
  <aside class="sidebar" aria-label="Sidebar navigation">
    <div class="logo">
        <img src="<?php echo $systemLogoBase64; ?>" alt="JOBFIT logo" class="logo-img">
    </div>
    <div class="title">
        <?php echo $systemName; ?>
    </div>
    <nav>
<a  href="dashboard.php">
  <i class="fas fa-home"></i> Dashboard
</a>
<a href="analytics.php">
  <i class="fas fa-chart-bar"></i> Test Analytics
</a>

<a href="users.php">
  <i class="fas fa-users-cog"></i> Users  
</a>
<a class="active" aria-current="page" href="posting.php">
 <i class="fas fa-briefcase"></i> Job Posting
</a>
<a href="settings.php">
  <i class="fas fa-cog"></i> Settings
</a>
</nav>
    <a href="#" id="logoutBtn" class="logout">Logout</a>
  </aside>

<div class="logout-modal" id="logoutModal">
  <div class="logout-modal-content">
    <i class="fas fa-sign-out-alt"></i>
    <h2>LOG OUT</h2>
    <p>Are you sure you want to logout?</p>
    <div class="logout-modal-buttons">
      <button class="btn-no" id="cancelLogout">NO</button>
      <button class="btn-yes" onclick="window.location.href='?logout=true'">YES</button>
    </div>
  </div>
</div>

<main class="content">
<section id="dashboard-header">
  <div class="top-header">
    <div class="header-left">
      <div class="admin-label">
  Homepage > 
  <a href="posting.php" class="admin-label no-underline">Job Posting</a> > 
  <span style="font-weight: bold;">Job Applicants</span>
</div>

    </div>

    <div class="user-profile" onclick="toggleDropdown()">
        <?php if ($adminImageBase64): ?>
                <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle">
            <?php else: ?>
                <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
            <?php endif; ?>
            <div class="user-info">
              <div class="user-name"><?php echo htmlspecialchars($adminName); ?></div>
              <div class="user-role">Administrator</div>
            </div>
      <div class="dropdown-icon">▼</div>

      <div class="user-dropdown" id="user-dropdown">
      <a href="#" id="viewProfileModalBtn">View Profile</a>
      <a href="settings.php">Settings</a>
      <a href="#" id="logoutDropdownBtn" class="logout">Logout</a>
      </div>
    </div>
  </div>
</section>


<?php
// FIXED: Allow postid 0 by checking >= 0 instead of > 0
$postID = isset($_GET['postid']) ? (int)$_GET['postid'] : -1;
$jobRole = "No Job Post Selected"; 

// FIXED: Check if postID is >= 0 instead of > 0
if ($postID >= 0) {
    // Get the job role from the posting
    $titleStmt = $con->prepare("SELECT postjobrole FROM jobposting WHERE postid = ?");
    $titleStmt->bind_param("i", $postID);
    $titleStmt->execute();
    $titleResult = $titleStmt->get_result();
    if ($titleResult->num_rows > 0) {
        $jobData = $titleResult->fetch_assoc();
        $jobRole = htmlspecialchars($jobData['postjobrole']);
    } else {
        $jobRole = "Job Post Not Found";
    }
    $titleStmt->close();

    // Enhanced query to include job recommendation matching
    $applicantQuery = "
        SELECT 
            a.applicationid,
            a.userid,
            DATE(a.date_applied) as date_applied_only,
            a.status,
            u.firstname,
            u.lastname,
            u.email,
            u.image,
            jr.job1,
            jr.job1_confidence,
            jr.job2,
            jr.job2_confidence,
            jr.job3,
            jr.job3_confidence,
            jr.job4,
            jr.job4_confidence,
            jr.job5,
            jr.job5_confidence,
            jr.created_at as recommendation_date,
            CASE 
                WHEN jr.job1 = ? THEN jr.job1_confidence
                WHEN jr.job2 = ? THEN jr.job2_confidence
                WHEN jr.job3 = ? THEN jr.job3_confidence
                WHEN jr.job4 = ? THEN jr.job4_confidence
                WHEN jr.job5 = ? THEN jr.job5_confidence
                ELSE 0 
            END as match_confidence,
            CASE 
                WHEN jr.job1 = ? THEN 1
                WHEN jr.job2 = ? THEN 2
                WHEN jr.job3 = ? THEN 3
                WHEN jr.job4 = ? THEN 4
                WHEN jr.job5 = ? THEN 5
                ELSE 6 
            END as match_position
        FROM 
            application a
        INNER JOIN 
            users u ON a.userid = u.userid
        LEFT JOIN 
            job_recommendations jr ON a.userid = jr.user_id
        WHERE 
            a.postid = ?
        ORDER BY 
            match_confidence DESC,
            match_position ASC,
            jr.created_at DESC,
            a.date_applied DESC";
            
    $stmt = $con->prepare($applicantQuery);
    
    // Bind parameters - the job role multiple times for the CASE statements
    $bindParams = array_merge(
        array_fill(0, 10, $jobRole), // 10 placeholders for job role matching
        [$postID] // final placeholder for postid
    );
    
    $types = str_repeat('s', 10) . 'i'; // 10 strings + 1 integer
    $stmt->bind_param($types, ...$bindParams);
    
    $stmt->execute();
    $applicantResult = $stmt->get_result();
    $applicantsFound = false;

} else {
    $applicantResult = false;
    $applicantsFound = false;
    $jobRole = "Invalid Job Post ID";
}

// FIXED: Allow postid 0 for archived count
$archivedCount = 0;
if ($postID >= 0) {
    $countStmt = $con->prepare("SELECT COUNT(*) AS archived_count FROM archived_applicants WHERE postid = ?");
    $countStmt->bind_param("i", $postID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();

    if ($countResult->num_rows > 0) {
        $countData = $countResult->fetch_assoc();
        $archivedCount = (int)$countData['archived_count'];
    }
    $countStmt->close();
}
?>

<section class="dashboard-overview">
    <div class="overview-header">
        <h2>Job Applicants</h2>
        <p>Viewing applicants for: <span style="font-weight: bold;"><?php echo $jobRole; ?></span></p>
        <input type="hidden" id="currentViewingJobRole" value="<?php echo htmlspecialchars($jobRole); ?>">
    </div>
    
    <div class="users-section">
        <a href="posting.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> BACK
    </a>
        <div class="bordered-section">
            
            <div class="section-header">
                <div>
                    <h3>Applicants List</h3> 
                    <p>A list of all users who applied for this job post.</p>
                </div>
                <div class="button-group">
                    <button class="report-button generateApplicantReportBtn" data-postid="<?php echo $postID; ?>">
                        <i class="fas fa-file-alt"></i> Generate Report
                    </button>
                </div>
            </div>
            
            <div class="search-filter-controls">
                <div class="search-box">
                    <input type="text" class="search-input" id="applicantSearch" placeholder="Search applicants by name or email...">
                </div>
                <div class="filters">
                    <select class="filter-select" id="applicantStatusFilter"> 
                        <option value="all">Status</option>
                        <option value="pending">Pending</option>
                        <option value="initial interview">Initial Interview</option>
                        <option value="technical interview">Technical Interview</option>
                        <option value="Job Offer">Job Offer</option>
                        <option value="Job Offer Accepted">Job Offer Accepted</option>
                        <option value="Job Offer Rejected">Job Offer Rejected</option>
                        <option value="Failed">Failed</option>
                    </select>
                    <select class="filter-select" id="applicantSortOrder">
                        <option value="default">Default Order</option>
                        <option value="newest">Applied Date (Newest)</option>
                        <option value="oldest">Applied Date (Oldest)</option>
                        <option value="asc">Name (A-Z)</option>
                        <option value="desc">Name (Z-A)</option>
                    </select>
                    <input type="text" class="filter-select date-range-input" id="applicantDateRange" placeholder="Select Date Range" readonly>
                    
                    <a href="archive.php?postid=<?php echo $postID; ?>" id="archivedBtn" class="archive-btn">ARCHIVED (<span style="color: red; font-weight: bold;"><?php echo $archivedCount; ?></span>)</a>                      
                </div>
            </div>
            
            <div class="table-container">
                <table id="applicantsTable"> 
                    <thead>
                        <tr>
                            <th>APP ID</th> 
                            <th>USER ID</th>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>Date Applied</th>
                            <th>STATUS</th>
                            <th>RESUME</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="applicantsTableBody">
                        <?php
                        if ($applicantResult && $applicantResult->num_rows > 0) {
                            $applicantsFound = true;
                            while ($applicant = $applicantResult->fetch_assoc()) {
                                $appID = htmlspecialchars($applicant['applicationid']);
                                $userID = htmlspecialchars($applicant['userid']);
                                $firstName = htmlspecialchars($applicant['firstname']);
                                $lastName = htmlspecialchars($applicant['lastname']);
                                $email = htmlspecialchars($applicant['email']);
                                $dateApplied = date('F j, Y', strtotime($applicant['date_applied_only']));
                                $dateAppliedOnly = htmlspecialchars($applicant['date_applied_only']);
                                $status = htmlspecialchars($applicant['status']);
                                $fullName = $firstName . ' ' . $lastName;
                                $image = $applicant['image'];
                                
                                // Get match information
                                $matchConfidence = $applicant['match_confidence'];
                                $matchPosition = $applicant['match_position'];
                                $hasRecommendation = !empty($applicant['job1']); // Check if user has any job recommendations
                                
                                // Determine if this applicant is a match
                                $isJobMatch = $matchConfidence > 0;
                                $matchPercentage = round($matchConfidence * 100, 1);
                                
                                // Status class logic
                                $statusLower = strtolower($status);
                                if ($statusLower === 'job offer') {
                                    $statusClass = 'status-job-offer';
                                    $statusDisplay = 'Job Offer';
                                } elseif ($statusLower === 'failed') {
                                    $statusClass = 'status-failed';
                                    $statusDisplay = 'Failed';
                                } elseif ($statusLower === 'initial interview') {
                                    $statusClass = 'status-initial-interview';
                                    $statusDisplay = 'Initial Interview';
                                } elseif ($statusLower === 'technical interview') {
                                    $statusClass = 'status-technical-interview';
                                    $statusDisplay = 'Technical Interview';
                                } elseif ($statusLower === 'job offer accepted') {
                                    $statusClass = 'status-job-accepted';
                                    $statusDisplay = 'Job Offer Accepted';
                                } elseif ($statusLower === 'job offer rejected') {
                                    $statusClass = 'status-job-rejected';
                                    $statusDisplay = 'Job Offer Rejected';
                                } else {
                                    $statusClass = 'status-pending';
                                    $statusDisplay = 'Pending';
                                }
                                
                                // Add match indicator class for styling
                                $rowClass = $isJobMatch ? 'job-match-highlight' : '';
                                
                                echo "<tr class='$rowClass' data-appid='$appID' data-userid='$userID' data-date='$dateAppliedOnly' data-match-confidence='$matchConfidence'>";
                                echo "<td>$appID</td>";
                                echo "<td>$userID</td>";
                                
                                echo "<td><div class='user-cell'>";
                               if (!empty($image)) {
    $userProfileImage = 'data:image/jpeg;base64,' . base64_encode($image);
    echo "<img src='$userProfileImage' alt='User Avatar' class='avatar-circle-small'>";
} else {
    echo "<div class='avatar-circle-small' style='background-color: #2f80ed;'>";
    echo strtoupper(substr($applicant['firstname'], 0, 1));
    echo "</div>";
}
                                echo htmlspecialchars($fullName);
                                
                                // Show match badge if applicable
                                if ($isJobMatch) {
                                    echo "<span class='match-badge' title='Job Fit Score: $matchPercentage%'><i class='fas fa-star'></i> $matchPercentage% Match</span>";
                                } elseif ($hasRecommendation) {
                                    echo "<span class='no-match-badge' title='No specific match for this job role'><i class='fas fa-info-circle'></i> Other Role</span>";
                                }
                                
                                echo "</div></td>";

                                echo "<td>$email</td>";
                                echo "<td>$dateApplied</td>";

                                echo "<td><span class='status-tag $statusClass'>$statusDisplay</span></td>";

                                echo "<td><a href='view_resume.php?appid=$appID' class='view-resume' title='View Resume' target='_blank'><i class='fas fa-file'></i> View</a></td>";

                                // Actions
                                echo "<td class='actions-cell'>";
                                echo "<i class='fas fa-eye action-icon view-applicant' data-appid='$appID' data-userid='$userID' title='View Applicant Details'></i>";
                                
                                echo "<div class='action-dropdown-wrapper'>";
                                echo "<i class='fas fa-pen-to-square action-icon status-dropdown-toggle' data-appid='$appID' title='Update Application Status'></i>";
                                echo "<div class='action-dropdown'>";
                                
                                // Status update logic
                                $status_lower = strtolower($status);
                                $span_class = "'dropdown-item status-info-item' style='color: #6b7280; cursor: default;'";
                                
                                if ($status_lower === 'job offer') {
                                    echo "<span class=$span_class>Marked for Job Offer, awaiting user response</span>";
                                } elseif ($status_lower === 'failed') {
                                    echo "<span class=$span_class>Already Marked as Failed</span>";
                                } elseif ($status_lower === 'job offer accepted') {
                                    echo "<span class=$span_class>User accepted the job offer</span>";
                                } elseif ($status_lower === 'job offer rejected') {
                                    echo "<span class=$span_class>User rejected the job offer</span>";
                                } else {
                                    if ($status_lower === 'pending') {
                                        echo "<a href='#' class='dropdown-item open-status-modal' data-appid='$appID' data-new-status='Initial Interview' data-name='$fullName' data-email='$email' style='color: #b45309;'>Move to Initial Interview</a>";
                                        echo "<div class='dropdown-divider'></div>";
                                    }
                                    if ($status_lower === 'initial interview') {
                                        echo "<a href='#' class='dropdown-item open-status-modal' data-appid='$appID' data-new-status='Technical Interview' data-name='$fullName' data-email='$email' style='color: #2563eb;'>Move to Technical Interview</a>";
                                        echo "<div class='dropdown-divider'></div>";
                                    }
                                    if ($status_lower === 'technical interview') {
                                        echo "<a href='#' class='dropdown-item update-applicant-status' data-appid='$appID' data-new-status='Job Offer' style='color: #22c55e;'>Mark for Job Offer</a>";
                                        echo "<div class='dropdown-divider'></div>";
                                    }
                                    echo "<a href='#' class='dropdown-item update-applicant-status' data-appid='$appID' data-new-status='Failed' style='color: #ef4444;'>Mark as Failed</a>";
                                }
                                echo "</div></div>";

                                echo "<i class='fas fa-archive action-icon archive-applicant' data-appid='$appID' title='Archive Application'></i>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            if ($stmt) {
                                $stmt->close();
                            }
                        }

                        // FIXED: Updated colspan and message logic
                        if (!$applicantsFound) {
                            $colspan = 8; 
                            $display = ($postID >= 0) ? "No applicants found for this job post." : "Error: No job post ID provided.";
                            echo "<tr><td colspan='" . $colspan . "' style='text-align: center;'>" . $display . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-controls">
                <div class="pagination-info" id="applicantPaginationInfo">Showing 0 to 0 of 0 applicants</div> 

                <div class="pagination-buttons">
                    <button class="pagination-button" id="applicantPrevPage"><i class="fas fa-chevron-left"></i></button>
                    <div class="page-numbers" id="applicantPageNumbers">
                    </div>
                    <button class="pagination-button" id="applicantNextPage"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="pagination-rows-per-page">
                    <select id="applicantRowsPerPage" class="rows-per-page-select">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="20">20 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>
  </main>

  <div class="modal user-profile-modal" id="viewUserProfileModal">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>Applicant Profile</h2>
            <span class="close-button" id="closeUserProfileModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="avatar-circle-large" id="profileAvatar">JS</div>
                <div class="profile-name" id="profileFullName">John Smith</div>
                <div class="profile-email" id="profileEmailSummary">john.smith@example.com</div>
                <p class="profile-status" id="profileStatus"></p>
            </div>
            <p class="null-field-instruction" id="nullFieldInstruction">
                <i class="fas fa-exclamation-circle"></i> Red highlights indicate fields not yet set by the user.
            </p>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user"></i></div>
                <div class="section-title">Personal Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label for="viewUserId">User ID</label>
                        <input type="text" id="viewUserId" readonly>
                    </div>
                    <div class="form-field">
                        <label>Full Name</label>
                        <input type="text" id="viewFullName" readonly>
                    </div>
                    <div class="form-field-group">
                        <div class="form-field">
                            <label>Date of Birth</label>
                            <input type="text" id="viewDOB" readonly>
                        </div>
                        <div class="form-field">
                            <label>Age (Automated)</label>
                            <input type="text" id="viewAge" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-envelope"></i></div>
                <div class="section-title">Contact Information</div>
                <div class="section-fields">
                    <div class="form-field-group">
                        <div class="form-field">
                            <label>Email Address</label>
                            <input type="email" id="viewEmail" readonly>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Phone Number</label>
                        <input type="text" id="viewContact" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="section-title">Educational Background</div>
                <div class="section-fields">

                    <div class="form-field">
                        <label for="viewEduLvl">Educational Level</label>
                        <input type="text" id="viewEduLvl" class="form-control" readonly>
                    </div>

                    <div class="form-field">
                        <label for="viewCourse">Course</label>
                        <input type="text" id="viewCourse" class="form-control" readonly>
                    </div>

                    <div class="form-field">
                        <label for="viewSchool">School</label>
                        <input type="text" id="viewSchool" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Joined Date</label>
                        <input type="text" id="viewSignupDate" readonly>
                    </div>
                </div>
            </div>
            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">JOB FIT TEST SCORE</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Job role match</label>
                        <input type="text" id="rolematch" readonly>
                    </div>
                    <div class="form-field">
                         <label>percentage</label>
                        <input type="text" id="rolepercentage" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="profileModalCloseBtn">Close</button>
        </div>
    </div>
</div>


<div class="delete-user-modal" id="archiveApplicantModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-archive delete-warning-icon"></i> 
        </div>
        <h2>Archive Application</h2>
        <p class="delete-warning-text">Archiving this application will move it out of the active list.</p>
        <p class="delete-confirmation-text">Are you sure you want to archive the application for <span id="archiveApplicantFullName" style="font-weight: bold;">[Applicant Name]</span> (ID: <span id="archiveApplicantAppId" style="font-weight: bold;">[ID]</span>)?</p>
        
        <input type="hidden" id="appToArchiveId" value="">
        <input type="hidden" id="userToArchiveId" value="">

        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelArchiveApplicant">Cancel</button>
            <button class="btn-delete" id="confirmArchiveApplicant">Archive</button>
        </div>
    </div>
</div>


<div class="modal delete-user-modal" id="rejectApplicantModal">
    <div class="modal-content delete-user-modal-content" style="max-width: 500px;">
        <div class="delete-icon-container" style="background-color: #fef2f2;">
            <i class="fas fa-ban delete-warning-icon" style="color: #ef4444;"></i> 
        </div>
        <h2>Failed Application</h2>
        
        <p class="delete-warning-text" style="color: #ef4444; font-weight: bold;">This reason will be sent to the applicant via email.</p>

        <p class="delete-confirmation-text">
            Please select the reason for marking the application as 'Failed' for <span id="rejectApplicantFullName" style="font-weight: bold;">[Applicant Name]</span> (ID: <span id="rejectApplicantAppId" style="font-weight: bold;">[ID]</span>).
        </p>
        
        <div class="form-field" style="margin-top: 20px; text-align: left;">
            <label for="rejectionReasonSelect" style="display: block; font-weight: bold; margin-bottom: 5px;">Reason for Failure:</label>
            <select id="rejectionReasonSelect" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="" disabled selected>Select a rejection reason</option>
                <option value="Does not meet job qualifications">Does not meet job qualifications</option>
                <option value="Insufficient skills for the position">Insufficient skills for the position</option>
                <option value="Incomplete or missing resume details">Incomplete or missing resume details</option>
                <option value="Resume not relevant to the job position">Resume not relevant to the job position</option>
                <option value="Failed the initial interview">Failed the initial interview</option>
                <option value="Failed the technical interview">Failed the technical interview</option> <option value="Did not attend the scheduled interview">Did not attend the scheduled interview</option>
                <option value="Poor communication skills during the interview">Poor communication skills during the interview</option>
                <option value="Lack of required experience">Lack of required experience</option>
                <option value="Position already filled">Position already filled</option>
                <option value="Other reason">Other reason:</option>
            </select>
        </div>
        
        <div class="form-field" id="otherReasonField" style="margin-top: 15px; text-align: left; display: none;">
            <label for="otherReasonText" style="display: block; font-weight: bold; margin-bottom: 5px;">Other reason details:</label>
            <textarea id="otherReasonText" class="form-control" placeholder="Enter specific rejection details here..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
        </div>

        <input type="hidden" id="appToRejectId" value="">
        <input type="hidden" id="userToRejectEmail" value="">
        <input type="hidden" id="userToRejectName" value="">

        <div class="modal-buttons" style="margin-top: 25px;">
            <button class="btn-cancel" id="cancelRejectApplicant">Cancel</button>
            <button class="btn-delete" id="confirmRejectApplicant" style="background-color: #ef4444; color: white;">Save</button>
        </div>
    </div>
</div>



<div class="popup-overlay -approve-email" id="jobOfferModal" style="display: none;">
   <div class="popup-box -email-content">
       <div class="popup-header approve-header"> <h2>Mark for Job Offer & Send Email</h2>
           <span class="close-button" id="closeJobOfferModal">&times;</span>
       </div>
       
       <form id="jobOfferForm" data-status-form="job-offer">
           <div class="popup-body">
               <input type="hidden" id="appToJobOfferId" name="appid">
               <input type="hidden" id="jobRoleToJobOffer" name="jobRole">
               <input type="hidden" name="newStatus" value="Job Offer">

               <p class="approve-instruction">
                ⚠️ This email will be sent directly to <strong id="jobOfferApplicantFullName"></strong>.  
                You can edit the email and include the job offer details before sending.
                </p>

               <div class="form-field-email">
                   <label for="job-offer-recipient-email">Recipient Email</label>
                   <input type="email" id="job-offer-recipient-email" name="recipient_email" class="form-control readonly" readonly required>
               </div>
               
               <div class="form-field-email">
                   <label for="job-offer-subject">Subject</label>
                   <input type="text" id="job-offer-subject" name="subject" class="form-control" required>
               </div>
               
               <div class="form-field-email">
                   <label for="job-offer-message">Custom Email Message</label>
                   <div id="quill-editor-job-offer"></div> <textarea id="job-offer-message-hidden" name="customMessage" style="display:none;"></textarea> </div>
           </div>
           
           <div class="popup-footer">
               <button type="button" class="btn-cancel" id="cancelJobOfferModal">Cancel</button>
               <button type="submit" id="confirmJobOffer" class="save-button primary-button">Send Email & Mark for Job Offer</button>
           </div>
       </form>
   </div>
</div>


<div class="popup-overlay -status-email" id="initialInterviewModal" style="display: none;">
   <div class="popup-box -email-content">
       <div class="popup-header initial-interview-header">
           <h2>Move to Initial Interview & Schedule</h2>
           <span class="close-button" data-modal-target="initialInterviewModal">&times;</span>
       </div>
       
       <form data-status-form="initial interview" id="initialInterviewForm">
           <div class="popup-body">
               <input type="hidden" id="appToInitialInterviewId" name="appid">
               <input type="hidden" id="jobRoleToInitialInterview" name="jobRole">
               <input type="hidden" name="newStatus" value="Initial Interview">

               <p class="initial-instruction">
                ⚠️ This email will be sent directly to <strong data-full-name="initialInterview"></strong>.  
                You can edit the email and include the interview schedule before sending.
                </p>

               <div class="form-field-email">
                   <label for="initial-interview-recipient-email">Recipient Email</label>
                   <input type="email" id="initial-interview-recipient-email" name="recipient_email" class="form-control readonly" readonly required>
               </div>
               
               <div class="form-field-email">
                   <label for="initial-interview-subject">Subject</label>
                   <input type="text" id="initial-interview-subject" name="subject" class="form-control" required>
               </div>
               
               <div class="form-field-email">
                   <label for="initial-interview-message">Custom Email Message</label>
                   <div id="quill-editor-initial-interview"></div>
                   <textarea id="initial-interview-message-hidden" name="customMessage" style="display:none;"></textarea>
               </div>
           </div>
           
           <div class="popup-footer">
               <button type="button" class="btn-cancel" data-modal-target="initialInterviewModal">Cancel</button>
               <button type="submit" class="save-button initial-button">Send Email & Move to Initial Interview</button>
           </div>
       </form>
   </div>
</div>

<div class="popup-overlay -status-email" id="technicalInterviewModal" style="display: none;">
   <div class="popup-box -email-content">
       <div class="popup-header final-interview-header"> <h2>Move to Technical Interview & Schedule</h2>
           <span class="close-button" data-modal-target="technicalInterviewModal">&times;</span>
       </div>
       
       <form data-status-form="technical interview" id="technicalInterviewForm">
           <div class="popup-body">
               <input type="hidden" id="appToTechnicalInterviewId" name="appid">
               <input type="hidden" id="jobRoleToTechnicalInterview" name="jobRole">
               <input type="hidden" name="newStatus" value="Technical Interview">

               <p class="final-instruction"> ⚠️ This email will be sent directly to <strong data-full-name="technicalInterview"></strong>.  
                You can edit the email and include the interview schedule before sending.
                </p>

               <div class="form-field-email">
                   <label for="technical-interview-recipient-email">Recipient Email</label>
                   <input type="email" id="technical-interview-recipient-email" name="recipient_email" class="form-control readonly" readonly required>
               </div>
               
               <div class="form-field-email">
                   <label for="technical-interview-subject">Subject</label>
                   <input type="text" id="technical-interview-subject" name="subject" class="form-control" required>
               </div>
               
               <div class="form-field-email">
                   <label for="technical-interview-message">Custom Email Message</label>
                   <div id="quill-editor-technical-interview"></div> <textarea id="technical-interview-message-hidden" name="customMessage" style="display:none;"></textarea> </div>
           </div>
           
           <div class="popup-footer">
               <button type="button" class="btn-cancel" data-modal-target="technicalInterviewModal">Cancel</button>
               <button type="submit" class="save-button final-button">Send Email & Move to Technical Interview</button>
           </div>
       </form>
   </div>
</div>




<script>
const currentPostId = <?php echo $postID; ?>;
const currentJobRole = "<?php echo htmlspecialchars($jobRole); ?>";

// Function to fetch and display applicants ordered by match probability
async function loadApplicantsByJobMatch() {
    try {
        showLoadingState();
        
        const response = await fetch(`../api/get_applicants_by_job.php?post_id=${currentPostId}`);
        const data = await response.json();
        
        if (data.success) {
            displayApplicants(data.applicants);
           
        } else {
            console.error('Error loading applicants:', data.error);
            showErrorState(data.error);
        }
    } catch (error) {
        console.error('Fetch error:', error);
        showErrorState('Failed to load applicants');
    }
}
function displayApplicants(applicants) {
    const tbody = document.querySelector('#applicantsTable tbody');
    
    if (applicants.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No applicants found for this job post.</td></tr>';
        return;
    }
    
    let html = '';
    
    applicants.forEach(applicant => {
        const dateApplied = new Date(applicant.date_applied + 'T00:00:00').toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Status class logic
        const statusLower = applicant.status.toLowerCase();
        let statusClass = 'status-pending';
        let statusDisplay = 'Pending';
        
        const statusMap = {
            'job offer': ['status-job-offer', 'Job Offer'],
            'failed': ['status-failed', 'Failed'],
            'initial interview': ['status-initial-interview', 'Initial Interview'],
            'technical interview': ['status-technical-interview', 'Technical Interview'],
            'job offer accepted': ['status-job-accepted', 'Job Offer Accepted'],
            'job offer rejected': ['status-job-rejected', 'Job Offer Rejected']
        };
        
        if (statusMap[statusLower]) {
            [statusClass, statusDisplay] = statusMap[statusLower];
        }
        
        // Match badge
        let matchBadge = '';
        if (applicant.is_match) {
            matchBadge = `<span class='match-badge' title='Job Fit Score: ${applicant.match_percentage}%'><i class='fas fa-star'></i> ${applicant.match_percentage}% Match</span>`;
        } else if (applicant.has_recommendation) {
            matchBadge = `<span class='no-match-badge' title='No specific match for this job role'><i class='fas fa-info-circle'></i> Other Role</span>`;
        }
        
       // User avatar - use base64 data from API response
let userAvatar = '';
if (applicant.avatar) {
    // Use the base64 avatar data directly from the API
    userAvatar = `<img src="${applicant.avatar}" alt="User Avatar" class="avatar-circle-small">`;
} else {
    // Fallback to initials if no avatar
    const initials = applicant.first_name ? applicant.first_name.charAt(0).toUpperCase() : 'U';
    userAvatar = `<div class="avatar-circle-small" style="background-color: #2f80ed;">
        ${initials}
    </div>`;
}
        // Row class for highlighting matches
        const rowClass = applicant.is_match ? 'job-match-highlight' : '';
        
        html += `
            <tr class="${rowClass}" 
                data-appid="${applicant.application_id}" 
                data-userid="${applicant.user_id}" 
                data-date="${applicant.date_applied}" 
                data-match-confidence="${applicant.match_confidence}"
                data-applicant-data='${JSON.stringify(applicant).replace(/'/g, "&#39;")}'>
                <td>${applicant.application_id}</td>
                <td>${applicant.user_id}</td>
                <td>
                    <div class="user-cell">
                        ${userAvatar}
                        ${applicant.name}
                        ${matchBadge}
                    </div>
                </td>
                <td>${applicant.email}</td>
                <td>${dateApplied}</td>
                <td><span class="status-tag ${statusClass}">${statusDisplay}</span></td>
                <td><a href="view_resume.php?appid=${applicant.application_id}" class="view-resume" title="View Resume" target="_blank"><i class="fas fa-file"></i> View</a></td>
                <td class="actions-cell">
                    <i class="fas fa-eye action-icon view-applicant" 
                       data-appid="${applicant.application_id}" 
                       data-userid="${applicant.user_id}" 
                       title="View Applicant Details"></i>
                    
                    <div class="action-dropdown-wrapper">
                        <i class="fas fa-pen-to-square action-icon status-dropdown-toggle" 
                           data-appid="${applicant.application_id}" 
                           title="Update Application Status"></i>
                        <div class="action-dropdown">
                            ${generateStatusDropdown(applicant.status, applicant.application_id, applicant.name, applicant.email)}
                        </div>
                    </div>
                    
                    <i class="fas fa-archive action-icon archive-applicant" 
                       data-appid="${applicant.application_id}" 
                       title="Archive Application"></i>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // CRITICAL: Re-attach event listeners after updating the table
    attachArchiveEventListeners();
    attachEventListeners(); // Your existing function
    updatePaginationInfo(applicants.length);
    
    // Debug: Check archive icons
    console.log('=== DEBUG: Checking archive icons ===');
    document.querySelectorAll('.archive-applicant').forEach((icon, index) => {
        const appId = icon.getAttribute('data-appid');
        console.log(`Archive icon ${index}: data-appid = "${appId}"`);
    });
}

function attachArchiveEventListeners() {
    // Archive applicant click handler
    document.querySelectorAll('.archive-applicant').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = this.getAttribute('data-appid');
            console.log('Archive clicked - App ID:', appId);
            
            if (!appId || appId === "0") {
                console.error('Invalid application ID:', appId);
                alert('Error: Could not get application ID.');
                return;
            }
            
            const row = this.closest('tr');
            const nameCell = row.querySelector('td:nth-child(3)');
            const fullName = nameCell ? nameCell.textContent.trim() : 'N/A';
            
            // Set the modal values
            if (document.getElementById("archiveApplicantFullName")) {
                document.getElementById("archiveApplicantFullName").textContent = fullName;
            }
            if (document.getElementById("archiveApplicantAppId")) {
                document.getElementById("archiveApplicantAppId").textContent = appId;
            }
            if (document.getElementById("appToArchiveId")) {
                document.getElementById("appToArchiveId").value = appId;
            }
            
            const archiveModal = document.getElementById('archiveApplicantModal');
            if (archiveModal) {
                archiveModal.style.display = 'flex';
            }
        });
    });
}

function generateStatusDropdown(currentStatus, appId, fullName, email) {
    const statusLower = currentStatus.toLowerCase();
    let html = '';
    
    if (statusLower === 'job offer') {
        html = `<span class="dropdown-item status-info-item" style="color: #6b7280; cursor: default;">
            Marked for Job Offer, awaiting user response
        </span>`;
    } else if (statusLower === 'failed') {
        html = `<span class="dropdown-item status-info-item" style="color: #6b7280; cursor: default;">
            Already Marked as Failed
        </span>`;
    } else if (statusLower === 'job offer accepted') {
        html = `<span class="dropdown-item status-info-item" style="color: #6b7280; cursor: default;">
            User accepted the job offer
        </span>`;
    } else if (statusLower === 'job offer rejected') {
        html = `<span class="dropdown-item status-info-item" style="color: #6b7280; cursor: default;">
            User rejected the job offer
        </span>`;
    } else {
        if (statusLower === 'pending') {
            html += `<a href="#" class="dropdown-item open-status-modal" data-appid="${appId}" data-new-status="Initial Interview" data-name="${fullName}" data-email="${email}" style="color: #b45309;">
                Move to Initial Interview
            </a>
            <div class="dropdown-divider"></div>`;
        }
        if (statusLower === 'initial interview') {
            html += `<a href="#" class="dropdown-item open-status-modal" data-appid="${appId}" data-new-status="Technical Interview" data-name="${fullName}" data-email="${email}" style="color: #2563eb;">
                Move to Technical Interview
            </a>
            <div class="dropdown-divider"></div>`;
        }
        if (statusLower === 'technical interview') {
            html += `<a href="#" class="dropdown-item update-applicant-status" data-appid="${appId}" data-new-status="Job Offer" style="color: #22c55e;">
                Mark for Job Offer
            </a>
            <div class="dropdown-divider"></div>`;
        }
        html += `<a href="#" class="dropdown-item update-applicant-status" data-appid="${appId}" data-new-status="Failed" style="color: #ef4444;">
            Mark as Failed
        </a>`;
    }
    
    return html;
}

// Function to update match statistics in the header
function updateMatchStatistics(data) {
    const overviewHeader = document.querySelector('.overview-header');
    if (overviewHeader && data.total_applicants > 0) {
        const statsHtml = `
            <div class="match-statistics">
                <h4>Job Fit Analytics</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span>Total Applicants:</span>
                        <span class="stat-value">${data.total_applicants}</span>
                    </div>
                    <div class="stat-item">
                        <span>Matched Candidates:</span>
                        <span class="stat-value">${data.matched_applicants}</span>
                    </div>
                    <div class="stat-item">
                        <span>Match Rate:</span>
                        <span class="stat-value">${data.match_rate}%</span>
                    </div>
                    <div class="stat-item">
                        <span>Job Role:</span>
                        <span class="stat-value">${data.job_name}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Insert after the main header
        const mainHeader = overviewHeader.querySelector('h2').parentElement;
        mainHeader.insertAdjacentHTML('afterend', statsHtml);
    }
}

// Function to show loading state
function showLoadingState() {
    const tbody = document.querySelector('#applicantsTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 40px;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <div class="spinner"></div>
                    <p>Loading applicants sorted by job fit score...</p>
                </div>
            </td>
        </tr>
    `;
}

// Function to show error state
function showErrorState(message) {
    const tbody = document.querySelector('#applicantsTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px;"></i>
                <p>${message}</p>
                <button onclick="loadApplicantsByJobMatch()" class="btn-retry" style="margin-top: 10px;">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </td>
        </tr>
    `;
}

function attachEventListeners() {
    // View applicant details
    document.querySelectorAll('.view-applicant').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = this.getAttribute('data-appid');
            const userId = this.getAttribute('data-userid');
            const row = this.closest('tr');
            
            const applicantDataJson = row.getAttribute('data-applicant-data');
            
            if (applicantDataJson) {
                try {
                    const applicantData = JSON.parse(applicantDataJson.replace(/&#39;/g, "'"));
                    populateApplicantModal(applicantData);
                    document.getElementById('viewUserProfileModal').style.display = 'block';
                } catch (parseError) {
                    console.error('Error parsing applicant data:', parseError);
                    alert('Error loading applicant data');
                }
            }
        });
    });

    // Status dropdown toggles
    document.querySelectorAll('.status-dropdown-toggle').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const wrapper = this.closest('.action-dropdown-wrapper');
            const dropdown = wrapper.querySelector('.action-dropdown');
            
            // Close all other dropdowns
            document.querySelectorAll('.action-dropdown').forEach(d => {
                if (d !== dropdown) d.style.display = 'none';
            });
            
            // Toggle current dropdown
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown-wrapper')) {
            document.querySelectorAll('.action-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });
}

// Add this after your attachArchiveEventListeners function
function setupArchiveModal() {
    const confirmArchiveBtn = document.getElementById('confirmArchiveApplicant');
    const cancelArchiveBtn = document.getElementById('cancelArchiveApplicant');
    const archiveModal = document.getElementById('archiveApplicantModal');
    
    if (confirmArchiveBtn) {
        confirmArchiveBtn.addEventListener('click', async function() {
            const appId = document.getElementById('appToArchiveId').value;
            console.log('Confirming archive for app ID:', appId);
            
            if (!appId) {
                alert('Error: No application ID found.');
                return;
            }
            
            const formData = new FormData();
            formData.append('appid', appId);
            
            try {
                const response = await fetch('archive_applicant.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Close modal and reload page
                    if (archiveModal) archiveModal.style.display = 'none';
                    window.location.reload();
                } else {
                    alert('Failed to archive: ' + result.message);
                }
            } catch (error) {
                console.error('Archive error:', error);
                alert('An error occurred while archiving.');
            }
        });
    }
    
    if (cancelArchiveBtn && archiveModal) {
        cancelArchiveBtn.addEventListener('click', function() {
            archiveModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    if (archiveModal) {
        window.addEventListener('click', function(event) {
            if (event.target === archiveModal) {
                archiveModal.style.display = 'none';
            }
        });
    }
}
// Function to update pagination info
function updatePaginationInfo(totalApplicants) {
    const paginationInfo = document.getElementById('applicantPaginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing ${totalApplicants} applicants sorted by job fit score`;
    }
}
// Add this debug function to check the DOM elements
function debugModalFields() {
    console.log('=== DEBUG MODAL FIELDS ===');
    
    const roleMatchField = document.getElementById('rolematch');
    const rolePercentageField = document.getElementById('rolepercentage');
    
    console.log('rolematch element exists:', !!roleMatchField);
    console.log('rolepercentage element exists:', !!rolePercentageField);
    
    if (roleMatchField) {
        console.log('rolematch current value:', roleMatchField.value);
        console.log('rolematch visibility:', window.getComputedStyle(roleMatchField).display);
    }
    
    if (rolePercentageField) {
        console.log('rolepercentage current value:', rolePercentageField.value);
        console.log('rolepercentage visibility:', window.getComputedStyle(rolePercentageField).display);
    }
    
    // Check if modal is properly displayed
    const modal = document.getElementById('viewUserProfileModal');
    console.log('Modal display style:', modal.style.display);
    console.log('Modal computed display:', window.getComputedStyle(modal).display);
}
function populateApplicantModal(applicantData) {
    console.log('Populating modal with job fit data:', applicantData);
    
    // Get the current job role from the hidden input
    const currentJobRole = document.getElementById('currentViewingJobRole').value;
    
    // Personal Information
    document.getElementById('viewUserId').value = applicantData.user_id || 'N/A';
    document.getElementById('viewFullName').value = applicantData.name || 'N/A';
    
    // Handle birthday and age
    const birthday = applicantData.birthday && applicantData.birthday !== '0000-00-00' ? applicantData.birthday : 'Not set';
    document.getElementById('viewDOB').value = birthday;
    
    const age = applicantData.age || (birthday !== 'Not set' ? calculateAge(birthday) : 'N/A');
    document.getElementById('viewAge').value = age;
    
    // Contact Information
    document.getElementById('viewEmail').value = applicantData.email || 'N/A';
    document.getElementById('viewContact').value = applicantData.contact || 'Not set';
    
    // Educational Background
    document.getElementById('viewEduLvl').value = applicantData.education_level || 'Not set';
    document.getElementById('viewCourse').value = applicantData.course || 'Not set';
    document.getElementById('viewSchool').value = applicantData.school || 'Not set';
    
    // System Information
    const joinDate = applicantData.date_applied ? formatDate(applicantData.date_applied) : 'N/A';
    document.getElementById('viewSignupDate').value = joinDate;
    
    // JOB FIT TEST SCORE Section - DYNAMIC JOB ROLE
    const roleMatchField = document.getElementById('rolematch');
    const rolePercentageField = document.getElementById('rolepercentage');
    
    console.log('Setting job fit fields for job role:', currentJobRole);
    
    // RESET ALL STYLING FIRST
    if (roleMatchField) {
        roleMatchField.style.backgroundColor = '';
        roleMatchField.style.borderLeft = '';
        roleMatchField.style.color = '';
    }
    if (rolePercentageField) {
        rolePercentageField.style.backgroundColor = '';
        rolePercentageField.style.borderLeft = '';
        rolePercentageField.style.color = '';
    }
    
    if (applicantData.is_match && applicantData.match_percentage > 0) {
        // HAS MATCH - Show positive styling (NO RED)
        roleMatchField.value = `${currentJobRole}`; // ← DYNAMIC
        rolePercentageField.value = `${applicantData.match_percentage}% Match Score`;
        
        // Add visual styling - YELLOW for matches
        roleMatchField.style.backgroundColor = '#fffbeb';
        roleMatchField.style.borderLeft = '4px solid #f59e0b';
        rolePercentageField.style.backgroundColor = '#fffbeb';
        rolePercentageField.style.borderLeft = '4px solid #f59e0b';
        
        console.log('Job fit fields SET to MATCH:', {
            jobRole: currentJobRole,
            matchPercentage: applicantData.match_percentage
        });
    } else {
        // NO MATCH - Highlight in RED
        roleMatchField.value = `No match for ${currentJobRole}`; 
        rolePercentageField.value = 'N/A';
        
        // RED highlighting for no match
        roleMatchField.style.backgroundColor = '#fee2e2';
        roleMatchField.style.borderLeft = '4px solid #ef4444';
        roleMatchField.style.color = '#dc2626';
        rolePercentageField.style.backgroundColor = '#fee2e2';
        rolePercentageField.style.borderLeft = '4px solid #ef4444';
        rolePercentageField.style.color = '#dc2626';
        
        console.log('Job fit fields SET to NO MATCH for:', currentJobRole);
    }
    
    // Update profile summary
    document.getElementById('profileFullName').textContent = applicantData.name || 'N/A';
    document.getElementById('profileEmailSummary').textContent = applicantData.email || 'N/A';
    
    // Update profile status - DYNAMIC JOB ROLE
    const profileStatus = document.getElementById('profileStatus');
    if (applicantData.is_match && applicantData.match_percentage > 0) {
        profileStatus.innerHTML = `<span style="color: #10b981; font-weight: bold;">
            <i class="fas fa-star"></i> ${currentJobRole} Match: ${applicantData.match_percentage}% 
        </span>`;
    } else {
        profileStatus.innerHTML = `<span style="color: #ef4444; font-weight: bold;">
            <i class="fas fa-times-circle"></i> No match for ${currentJobRole}
        </span>`;
    }
    
   // Update avatar
const profileAvatar = document.getElementById('profileAvatar');
profileAvatar.innerHTML = '';

if (applicantData.avatar) {
    // Use base64 avatar from API response
    const img = document.createElement('img');
    img.src = applicantData.avatar;
    img.alt = 'User Avatar';
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.borderRadius = '50%';
    img.style.objectFit = 'cover';
    profileAvatar.appendChild(img);
} else {
    // Fallback to initials
    const initials = applicantData.first_name ? applicantData.first_name.charAt(0).toUpperCase() : 'U';
    profileAvatar.textContent = initials;
    profileAvatar.style.backgroundColor = '#2f80ed';
    profileAvatar.style.display = 'flex';
    profileAvatar.style.alignItems = 'center';
    profileAvatar.style.justifyContent = 'center';
    profileAvatar.style.color = 'white';
    profileAvatar.style.fontWeight = 'bold';
    profileAvatar.style.fontSize = '24px';
}
    // Final verification
    console.log('VERIFICATION - Current job fit field values for:', currentJobRole);
    console.log('rolematch:', document.getElementById('rolematch').value);
    console.log('rolepercentage:', document.getElementById('rolepercentage').value);
}

// Helper function to calculate age from birthday
function calculateAge(birthday) {
    const birthDate = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Enhanced function to fetch applicant details for the modal
async function fetchApplicantDetails(userId, appId) {
    try {
        // Show loading state in modal
        const modalBody = document.querySelector('.modal-body');
        const originalContent = modalBody.innerHTML;
        modalBody.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; height: 200px;">
                <div class="spinner"></div>
                <p style="margin-left: 10px;">Loading applicant details...</p>
            </div>
        `;

        const response = await fetch(`../api/get_applicants_by_job.php?post_id=${currentPostId}`);
        const data = await response.json();
        
        if (data.success) {
            // Find the specific applicant in the response
            const applicant = data.applicants.find(app => 
                app.user_id == userId && app.application_id == appId
            );
            
            if (applicant) {
                populateApplicantModal(applicant);
            } else {
                throw new Error('Applicant not found in response');
            }
        } else {
            throw new Error(data.error || 'Failed to fetch applicant details');
        }
    } catch (error) {
        console.error('Error fetching applicant details:', error);
        
        // Show error state in modal
        const modalBody = document.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                <h3>Error Loading Applicant Details</h3>
                <p>${error.message}</p>
                <button onclick="fetchApplicantDetails(${userId}, ${appId})" class="btn-retry" style="margin-top: 16px;">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
    }
}

// OVERRIDE View applicant details - SOLUTION 1
function attachEventListeners() {
    // View applicant details - OVERRIDE VERSION
    document.querySelectorAll('.view-applicant').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // CRITICAL: Stop the applicants.js handler
            
            const appId = this.getAttribute('data-appid');
            const userId = this.getAttribute('data-userid');
            const row = this.closest('tr');
            
            // Get applicant data from data attribute
            const applicantDataJson = row.getAttribute('data-applicant-data');
            
            console.log('=== OVERRIDE HANDLER FIRED ===');
            console.log('Raw JSON from data attribute:', applicantDataJson);

            if (applicantDataJson) {
                try {
                    const applicantData = JSON.parse(applicantDataJson.replace(/&#39;/g, "'"));
                    console.log('Using data attribute for modal:', applicantData);
                    populateApplicantModal(applicantData);
                    
                    // Show the modal
                    document.getElementById('viewUserProfileModal').style.display = 'block';
                } catch (parseError) {
                    console.error('Error parsing applicant data:', parseError);
                    alert('Error loading applicant data');
                }
            } else {
                console.error('No applicant data found in data attribute');
                alert('Applicant data not available');
            }
        });
    });
}


// Add this to prevent other scripts from overwriting our values
document.addEventListener('DOMContentLoaded', function() {
    // Protect our job fit fields from being overwritten
    const roleMatchField = document.getElementById('rolematch');
    const rolePercentageField = document.getElementById('rolepercentage');
    
    if (roleMatchField) {
        roleMatchField.addEventListener('input', function(e) {
            console.log('Attempt to change rolematch detected:', e.target.value);
        });
    }
    
    if (rolePercentageField) {
        rolePercentageField.addEventListener('input', function(e) {
            console.log('Attempt to change rolepercentage detected:', e.target.value);
        });
    }
});
// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load applicants data
    loadApplicantsByJobMatch();
    
    // Close modal event listeners
    document.getElementById('profileModalCloseBtn').addEventListener('click', function() {
        document.getElementById('viewUserProfileModal').style.display = 'none';
    });

    document.getElementById('closeUserProfileModal').addEventListener('click', function() {
        document.getElementById('viewUserProfileModal').style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('viewUserProfileModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});


// CRITICAL: Override the applicants.js view handler completely
document.addEventListener('DOMContentLoaded', function() {
    // Remove any existing click listeners from view-applicant icons
    document.querySelectorAll('.view-applicant').forEach(icon => {
        icon.replaceWith(icon.cloneNode(true));
    });
    
    // Add our override handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-applicant')) {
            e.preventDefault();
            e.stopImmediatePropagation(); // CRITICAL: Stop all other handlers
            
            const appId = e.target.getAttribute('data-appid');
            const userId = e.target.getAttribute('data-userid');
            const row = e.target.closest('tr');
            
            // Get applicant data from data attribute
            const applicantDataJson = row.getAttribute('data-applicant-data');
            
            console.log('=== FINAL OVERRIDE HANDLER ===');
            console.log('Using data attribute:', applicantDataJson);

            if (applicantDataJson) {
                try {
                    const applicantData = JSON.parse(applicantDataJson.replace(/&#39;/g, "'"));
                    populateApplicantModal(applicantData);
                    document.getElementById('viewUserProfileModal').style.display = 'block';
                } catch (parseError) {
                    console.error('Error parsing applicant data:', parseError);
                    alert('Error loading applicant details');
                }
            } else {
                alert('Applicant data not available');
            }
            
            return false; // Prevent any other handlers
        }
    }, true); // Use capture phase to get there first
});

</script>


<?php if ($message): ?>
  <div id="message" class="custom-alert <?= $message_type ?> show">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<script>
  if ("<?= $message ?>") {
    setTimeout(() => {
      document.getElementById('message')?.classList.remove('show');
    }, 3000);
  }

  document.getElementById('generateReportBtn').addEventListener('click', function() {
  window.open('usersreport.php', '_blank');
});

// Search and Filtering Functionality
let allApplicants = [];
let filteredApplicants = [];
let currentPage = 1;
let rowsPerPage = 10;

// Initialize search and filtering
function initializeSearchAndFilter() {
    // Store all applicants when loading
    document.querySelectorAll('#applicantsTable tbody tr').forEach(row => {
        if (row.cells.length > 1) { // Skip empty rows
            const applicantData = {
                element: row,
                appId: row.getAttribute('data-appid'),
                userId: row.getAttribute('data-userid'),
                name: row.cells[2].textContent.trim(),
                email: row.cells[3].textContent.trim(),
                dateApplied: row.getAttribute('data-date'),
                status: row.cells[5].querySelector('.status-tag').textContent.trim(),
                matchConfidence: parseFloat(row.getAttribute('data-match-confidence')) || 0
            };
            allApplicants.push(applicantData);
        }
    });

    filteredApplicants = [...allApplicants];
    
    // Set up event listeners
    setupSearchAndFilterEvents();
    updatePagination();
    displayCurrentPage();
}

// Set up event listeners for search and filters
function setupSearchAndFilterEvents() {
    // Search input
    const searchInput = document.getElementById('applicantSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Status filter
    const statusFilter = document.getElementById('applicantStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', handleFilter);
    }

    // Sort order
    const sortOrder = document.getElementById('applicantSortOrder');
    if (sortOrder) {
        sortOrder.addEventListener('change', handleSort);
    }

    // Date range filter
    const dateRangeInput = document.getElementById('applicantDateRange');
    if (dateRangeInput) {
        $(dateRangeInput).datepicker({
            range: 'period',
            onSelect: function(dateText, inst, range) {
                handleDateRangeFilter(dateText, range);
            }
        });
    }

    // Rows per page
    const rowsPerPageSelect = document.getElementById('applicantRowsPerPage');
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            updatePagination();
            displayCurrentPage();
        });
    }

    // Pagination buttons
    const prevPageBtn = document.getElementById('applicantPrevPage');
    const nextPageBtn = document.getElementById('applicantNextPage');
    
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', goToPrevPage);
    }
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', goToNextPage);
    }
}

// Search functionality
function handleSearch() {
    const searchTerm = document.getElementById('applicantSearch').value.toLowerCase().trim();
    
    applyFilters(searchTerm);
}

// Filter by status
function handleFilter() {
    applyFilters();
}

// Sort functionality
function handleSort() {
    const sortValue = document.getElementById('applicantSortOrder').value;
    
    switch(sortValue) {
        case 'newest':
            filteredApplicants.sort((a, b) => new Date(b.dateApplied) - new Date(a.dateApplied));
            break;
        case 'oldest':
            filteredApplicants.sort((a, b) => new Date(a.dateApplied) - new Date(b.dateApplied));
            break;
        case 'asc':
            filteredApplicants.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'desc':
            filteredApplicants.sort((a, b) => b.name.localeCompare(a.name));
            break;
        case 'match-high':
            filteredApplicants.sort((a, b) => b.matchConfidence - a.matchConfidence);
            break;
        case 'match-low':
            filteredApplicants.sort((a, b) => a.matchConfidence - b.matchConfidence);
            break;
        default:
            // Default order (by application ID or original order)
            filteredApplicants.sort((a, b) => parseInt(a.appId) - parseInt(b.appId));
    }
    
    currentPage = 1;
    updatePagination();
    displayCurrentPage();
}

// Date range filtering
function handleDateRangeFilter(dateText, range) {
    applyFilters();
}

// Main filter application function
function applyFilters(searchTerm = null) {
    if (searchTerm === null) {
        searchTerm = document.getElementById('applicantSearch').value.toLowerCase().trim();
    }
    
    const statusFilter = document.getElementById('applicantStatusFilter').value;
    const dateRange = document.getElementById('applicantDateRange').value;
    
    filteredApplicants = allApplicants.filter(applicant => {
        // Search filter
        let matchesSearch = true;
        if (searchTerm) {
            matchesSearch = applicant.name.toLowerCase().includes(searchTerm) || 
                           applicant.email.toLowerCase().includes(searchTerm);
        }
        
        // Status filter
        let matchesStatus = true;
        if (statusFilter !== 'all') {
            matchesStatus = applicant.status.toLowerCase() === statusFilter.toLowerCase();
        }
        
        // Date range filter
        let matchesDateRange = true;
        if (dateRange) {
            const dates = dateRange.split(' - ');
            if (dates.length === 2) {
                const startDate = new Date(dates[0]);
                const endDate = new Date(dates[1]);
                const appliedDate = new Date(applicant.dateApplied);
                
                matchesDateRange = appliedDate >= startDate && appliedDate <= endDate;
            }
        }
        
        return matchesSearch && matchesStatus && matchesDateRange;
    });
    
    // Apply current sort
    handleSort();
}

// Display current page
function displayCurrentPage() {
    const tbody = document.getElementById('applicantsTableBody');
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const currentApplicants = filteredApplicants.slice(startIndex, endIndex);
    
    // Clear table
    tbody.innerHTML = '';
    
    if (currentApplicants.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No applicants match your search criteria.</td></tr>';
        return;
    }
    
    // Add applicants for current page
    currentApplicants.forEach(applicant => {
        tbody.appendChild(applicant.element.cloneNode(true));
    });
    
    // Re-attach event listeners for the new elements
    setTimeout(() => {
        attachEventListeners();
        attachArchiveEventListeners();
    }, 0);
}

// Pagination functions
function updatePagination() {
    const totalPages = Math.ceil(filteredApplicants.length / rowsPerPage);
    const paginationInfo = document.getElementById('applicantPaginationInfo');
    const pageNumbers = document.getElementById('applicantPageNumbers');
    const prevBtn = document.getElementById('applicantPrevPage');
    const nextBtn = document.getElementById('applicantNextPage');
    
    // Update pagination info
    if (paginationInfo) {
        const start = ((currentPage - 1) * rowsPerPage) + 1;
        const end = Math.min(currentPage * rowsPerPage, filteredApplicants.length);
        paginationInfo.textContent = `Showing ${start} to ${end} of ${filteredApplicants.length} applicants`;
    }
    
    // Update page numbers
    if (pageNumbers) {
        pageNumbers.innerHTML = '';
        
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // First page
        if (startPage > 1) {
            const firstPageBtn = document.createElement('button');
            firstPageBtn.className = 'pagination-number';
            firstPageBtn.textContent = '1';
            firstPageBtn.addEventListener('click', () => goToPage(1));
            pageNumbers.appendChild(firstPageBtn);
            
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                pageNumbers.appendChild(ellipsis);
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-number ${i === currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => goToPage(i));
            pageNumbers.appendChild(pageBtn);
        }
        
        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                pageNumbers.appendChild(ellipsis);
            }
            
            const lastPageBtn = document.createElement('button');
            lastPageBtn.className = 'pagination-number';
            lastPageBtn.textContent = totalPages;
            lastPageBtn.addEventListener('click', () => goToPage(totalPages));
            pageNumbers.appendChild(lastPageBtn);
        }
    }
    
    // Update button states
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        nextBtn.style.opacity = (currentPage === totalPages || totalPages === 0) ? '0.5' : '1';
    }
}

function goToPage(page) {
    currentPage = page;
    updatePagination();
    displayCurrentPage();
}

function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
        displayCurrentPage();
    }
}

function goToNextPage() {
    const totalPages = Math.ceil(filteredApplicants.length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
        displayCurrentPage();
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Clear all filters
function clearAllFilters() {
    document.getElementById('applicantSearch').value = '';
    document.getElementById('applicantStatusFilter').value = 'all';
    document.getElementById('applicantSortOrder').value = 'default';
    document.getElementById('applicantDateRange').value = '';
    
    applyFilters();
}

// Add clear filters button to your HTML and this event listener
document.addEventListener('DOMContentLoaded', function() {
    // Add clear filters button if it doesn't exist
    const filtersContainer = document.querySelector('.filters');
    if (filtersContainer && !document.getElementById('clearFiltersBtn')) {
        const clearBtn = document.createElement('button');
        clearBtn.id = 'clearFiltersBtn';
        clearBtn.className = 'clear-filters-btn';
        clearBtn.innerHTML = '<i class="fas fa-times"></i> Clear Filters';
        clearBtn.addEventListener('click', clearAllFilters);
        filtersContainer.appendChild(clearBtn);
    }
    
    // Initialize search and filtering after applicants are loaded
    setTimeout(() => {
        initializeSearchAndFilter();
    }, 1000);
});
</script>
<script src="../js/applicants.js"></script>
</body>
</html>