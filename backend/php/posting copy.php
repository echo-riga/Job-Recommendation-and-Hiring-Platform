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
  <link rel="stylesheet" href="../css/posting.css">
  <link rel="stylesheet" href="../css/posting_modal.css">

  <style>
  </style>
  </head>
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
$message_type = ''; 

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
<a class="active" aria-current="page">
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
        <div class="admin-label">Homepage > <span style="font-weight: bold;">Job Posting</span></div>
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

<section class="dashboard-overview">
    <div class="overview-header">
      <h2>Job Posting</h2>
      <p>Manage Job Postings and applicants</p>
    </div>

    <div class="users-section">
      <div class="bordered-section">
        <div class="section-header">
            <div>
                <h3>Job Postings List</h3>
                <p>A list of all active and closed job posts.</p>
            </div>
            <div class="button-group">
                <button class="add-button" id="openAddJobPostModalBtn">
                    <i class="fas fa-plus"></i> Add Job Post
                </button>
                <button class="report-button" id="generateReportBtn">
                    <i class="fas fa-file-alt"></i> Generate Report
                </button>
            </div>

        </div>
        <div class="search-filter-controls">
            <div class="search-box">
                <input type="text" class="search-input" id="jobPostSearch" placeholder="Search job postings...">
            </div>
            <div class="filters">
                <select class="filter-select" id="jobPostStatusFilter">
                    <option value="">Status</option>
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
                <select class="filter-select" id="jobPostSortOrder">
                    <option value="default">Default Order</option>
                    <option value="asc">Title (A-Z)</option>
                    <option value="desc">Title (Z-A)</option>
                    <option value="newest">Posting Date (Newest)</option>
                    <option value="oldest">Posting Date (Oldest)</option>
                </select>
            </div>
        </div>
        <div class="table-container">
          <table id="jobPostTable"> <thead>
              <tr>
                <th>ID</th> 
                <th>Job Position</th>
                <th>Employment Type</th>
                <th>Posting Date</th>
                <th>Deadline</th>
                <th>Applicants</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              
              $jobPostQuery = "
                  SELECT 
                      jp.postid, 
                      jp.posttype,
                      jp.postjobrole, 
                      DATE(jp.postdate) as post_date_only,
                      jp.postdeadline, 
                      jp.poststatus,
                      COUNT(a.postid) as applicant_count
                  FROM 
                      jobposting1 jp
                  LEFT JOIN 
                      application a ON jp.postid = a.postid
                  GROUP BY
                      jp.postid
                  ORDER BY
                      jp.postid DESC";

              
              
              $jobPostResult = mysqli_query($con, $jobPostQuery);
              $jobPostFound = false;

              if ($jobPostResult && mysqli_num_rows($jobPostResult) > 0) {
                  $jobPostFound = true;
                  while ($post = mysqli_fetch_assoc($jobPostResult)) {
                      $postID = htmlspecialchars($post['postid']);
                      $type = htmlspecialchars($post['posttype']);
                      $jobRole = htmlspecialchars($post['postjobrole']);
                      $postDate = date('F j, Y', strtotime($post['post_date_only']));
                      $deadline = date('F j, Y', strtotime($post['postdeadline']));
                      $status = htmlspecialchars($post['poststatus']);
                      
                      $applicantCount = (int)$post['applicant_count'];
                      
                      $statusClass = (strtolower($status) === 'open' ? 'active' : 'inactive');

                      echo "<tr data-postid='" . $postID . "' data-posttype='" . $type . "' data-jobrole='" . $jobRole . "' data-poststatus='" . $status . "'>";
                      echo "<td>" . $postID . "</td>"; 
                      echo "<td><strong>" . $jobRole . "</strong></td>";
                      echo "<td>" . $type . "</td>";
                      echo "<td>" . $postDate . "</td>";
                      echo "<td>" . $deadline . "</td>";
                      echo "<td><a href='applicants.php?postid=" . $postID . "' class='applicants-count'>" . $applicantCount . " applicant/s</a></td>";
                      
                      echo "<td><span class='status-tag " . $statusClass . "'>" . $status . "</span></td>";
                      
                      echo "<td class='actions-cell'>";
                      echo "<i class='fas fa-eye action-icon view-post' data-id='" . $postID . "'></i>";
                      echo "<div class='action-dropdown-wrapper'>";
                      echo "<i class='fas fa-pen-to-square action-icon edit-post' data-id='" . $postID . "'></i>";
                      echo "<div class='action-dropdown'>";
                      echo "<a href='#' class='dropdown-item edit-post-info' data-id='" . $postID . "'>Edit Post</a>";
                      echo "<div class='dropdown-divider'></div>";
                    
                      $current_status_lower = strtolower($status);
                      if ($current_status_lower === 'open') {
                          echo "<a href='#' class='dropdown-item post-status-action action-close-post' data-id='" . $postID . "' data-new-status='Closed' style='color: #ef4444;'>Close</a>";
                      } else {
                          echo "<a href='#' class='dropdown-item post-status-action action-open-post' data-id='" . $postID . "' data-new-status='Open' style='color: #22c55e;'>Open</a>";
                      }
                      echo "</div>"; 
                      echo "</div>"; 
                      echo "<i class='fas fa-trash-alt action-icon delete-post' data-id='" . $postID . "' data-job-role='" . $jobRole . "'></i>";                      
                      echo "</tr>";
                  }
              }

              if (!$jobPostFound) {
                  echo "<tr><td colspan='8' style='text-align: center;'>No Job Posts Found</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
        <div class="pagination-controls">
            <div class="pagination-info" id="jobPostPaginationInfo">Showing 0 to 0 of 0 job posts</div> 

            <div class="pagination-buttons">
                <button class="pagination-button" id="jobPostPrevPage"><i class="fas fa-chevron-left"></i></button>
                <div class="page-numbers" id="jobPostPageNumbers"></div>
                <button class="pagination-button" id="jobPostNextPage"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="pagination-rows-per-page">
                <select id="jobPostRowsPerPage" class="rows-per-page-select">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                </select>
            </div>
        </div>
      </div>
</section>

</main>


<div class="modal user-profile-modal" id="viewJobPostModal">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>Job Post Details</h2>
            <span class="close-button" id="closeJobPostModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="profile-name" id="postModalJobRole">Job Position Placeholder</div>
                <div class="profile-email" id="postModalPostType">Employment Type Placeholder</div>
                <p class="profile-status" id="postModalStatus"></p>
                <div class="profile-actions">
                    <button class="btn-edit-profile" id="editJobPostBtn">Edit Post</button>
                </div>
            </div>
            
            <form id="editJobPostForm"> 
            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">Post Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label for="viewPostId">Post ID</label>
                        <input type="text" id="viewPostId" name="postid" readonly> 
                    </div>
                    <div class="form-field">
                        <label for="viewPostJobRole">Job Position</label>
                        <input type="text" id="viewPostJobRole" name="postjobrole" readonly>
                    </div>
                    <div class="form-field">
                        <label for="viewPostType">Employment Type</label>
                        <select id="viewPostType" name="posttype" disabled>
                            <option value="Full Time">Full Time</option>
                            <option value="Part Time">Part Time</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="viewPostWorkSetup">Work Setup</label>
                        <select id="viewPostWorkSetup" name="postworksetup" disabled>
                            <option value="Onsite">Onsite</option>
                            <option value="Work From Home">Work From Home</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="viewPostExperience">Years of Experience</label>
                        <input type="text" id="viewPostExperience" name="postexperience" readonly>
                    </div>
                    <div class="form-field">
                        <label for="viewPostSalary">Salary</label>
                        <input type="number" step="1" min="0" id="viewPostSalary" name="postsalary" readonly>
                    </div>
                    <div class="form-field">
                        <label for="viewPostApplicantLimit">Applicant Limit</label>
                        <input type="number" step="1" min="0" id="viewPostApplicantLimit" name="postapplicantlimit" readonly placeholder="0 for no limit">
                    </div>
                    <div class="form-field">
                        <label for="displayPostDeadline">Application Deadline</label>
                        <input type="text" id="displayPostDeadline" readonly style="display: block;">
                        
                        <input type="date" id="viewPostDeadline" name="postdeadline" readonly style="display: none;"> 
                    </div>
                    <div class="form-field">
                        <label for="viewPostAddress">Location</label>
                        <input type="text" id="viewPostAddress" name="postaddress" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-file-alt"></i></div>
                <div class="section-title">Details & Requirements</div>
                
                <div class="section-fields">
                    <div class="form-field full-width">
                        <label for="viewPostSummary">Job Summary</label>
                        <div id="viewPostSummaryDisplay" class="view-mode-details"></div>
                        <div id="quill-editor-view-summary" class="quill-editor-view" style="display:none;"></div>
                        <textarea id="viewPostSummary" name="postsummary" style="display:none;"></textarea>
                    </div>
                </div>

                <div class="section-fields">
                    <div class="form-field full-width">
                        <label for="viewPostResponsibilities">Responsibilities</label>
                        <div id="viewPostResponsibilitiesDisplay" class="view-mode-details"></div>
                        <div id="quill-editor-view-responsibilities" class="quill-editor-view" style="display:none;"></div>
                        <textarea id="viewPostResponsibilities" name="postresponsibilities" style="display:none;"></textarea>
                    </div>
                </div>

                <div class="section-fields">
                    <div class="form-field full-width">
                        <label for="viewPostSpecification">Specifications</label>
                        <div id="viewPostSpecificationDisplay" class="view-mode-details"></div>
                        <div id="quill-editor-view-specification" class="quill-editor-view" style="display:none;"></div>
                        <textarea id="viewPostSpecification" name="postspecification" style="display:none;"></textarea>
                    </div>
                </div>

            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="section-title">Dates</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label for="viewPostDate">Posting Date</label>
                        <input type="text" id="viewPostDate" readonly> 
                    </div>
                    
                </div>
            </div>
            </form> </div>
        <div class="modal-footer">
            <button class="btn-close" id="postModalCloseBtn">Close</button>
            <button class="btn-save-profile" id="saveJobPostBtn" style="display: none;">Save Changes</button>
        </div>
    </div>
</div>
<div id="addJobPostModal" class="jobpost-modal-overlay">
    <div class="jobpost-modal-content">
        <div class="jobpost-modal-header">
            <h2>Add New Job Post</h2>
            <span class="jobpost-close-btn" id="closeAddJobPostModal">&times;</span>
        </div>
        <form id="addJobPostForm">
     <div class="jobpost-modal-body">
        <div class="jobpost-layout-container">
            
            <div class="jobpost-top-div">
                
                <div class="jobpost-form-group">
                    <label for="postJobRole">Job Position</label>
                    <input type="text" id="postJobRole" name="postjobrole" required maxlength="255">
                </div>
                <div class="jobpost-form-group">
                    <label for="postType">Employment Type</label>
                    <select id="postType" name="posttype" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="Full Time">Full Time</option>
                        <option value="Part Time">Part Time</option>
                        <option value="Internship">Internship</option>
                    </select>
                </div>
                <div class="jobpost-form-group">
                    <label for="postWorkSetup">Work Setup</label>
                    <select id="postWorkSetup" name="postworksetup" required>
                        <option value="" disabled selected>Select Setup</option>
                        <option value="Onsite">Onsite</option>
                        <option value="Work From Home">Work From Home</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>

                <div class="jobpost-form-group">
                    <label for="postSalary">Salary (e.g., 50000)</label>
                    <input type="number" step="1" min="0" id="postSalary" name="postsalary" required>
                </div>
                <div class="jobpost-form-group">
                    <label for="postExperience">Years of Experience</label>
                    <input type="text" id="postExperience" name="postexperience" required maxlength="255" placeholder="e.g., 2-3 years">
                </div>
                <div class="jobpost-form-group">
                    <label for="postApplicantLimit">Applicant Limit (Optional)</label>
                    <input type="number" step="1" min="1" id="postApplicantLimit" name="postapplicantlimit" placeholder="e.g., 50">
                </div>

                <div class="jobpost-form-group">
                    <label for="postDeadline">Application Deadline</label>
                    <input type="date" id="postDeadline" name="postdeadline">
                </div>
                <div class="jobpost-form-group jobpost-span-2"> <label for="postAddress">Address (Location)</label>
                    <input type="text" id="postAddress" name="postaddress" required maxlength="255">
                </div>
            </div>

            <div class="jobpost-bottom-div">
                
                <div class="jobpost-form-group jobpost-quill-group">
                    <label for="quill-editor-summary">Job Summary</label>
                    <div id="quill-editor-summary"></div>
                    <textarea id="postSummary" name="postsummary" style="display:none;" required></textarea>
                </div>
                
                <div class="jobpost-form-group jobpost-quill-group">
                    <label for="quill-editor-responsibilities">Responsibilities</label>
                    <div id="quill-editor-responsibilities"></div>
                    <textarea id="postResponsibilities" name="postresponsibilities" style="display:none;" required></textarea>
                </div>
                
                <div class="jobpost-form-group jobpost-quill-group">
                    <label for="quill-editor-specification">Specifications (Description)</label>
                    <div id="quill-editor-specification"></div>
                    <textarea id="postSpecification" name="postspecification" style="display:none;" required></textarea>
                </div>
                </div>
        </div>
    </div>
    
    <div class="jobpost-modal-footer">
        <button type="button" class="btn-cancel" id="postCancelBtn">Cancel</button>
        <button type="submit" class="btn-post" id="postJobBtn">Post Job</button>
    </div>
</form>
    </div>
</div>

<div class="modal confirm-modal" id="setDeadlineModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-calendar-alt confirm-icon"></i>
        </div>
        <h2>Set New Application Deadline</h2>
        <form id="updateDeadlineForm">
            <input type="hidden" id="deadlinePostId" name="postid">
            <div class="form-field" style="margin-bottom: 20px;">
                <label for="newPostDeadline">New Deadline</label>
                <input type="date" id="newPostDeadline" name="postdeadline" required>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-no" id="cancelSetDeadline">Cancel</button>
                <button type="submit" class="btn-yes" id="confirmSetDeadline">Save</button>
            </div>
        </form>
    </div>
</div>


<div class="modal confirm-modal" id="closeStatusConfirmModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-exclamation-triangle confirm-icon" style="color: #f59e0b;"></i>
        </div>
        <h2>Confirm Post Closure</h2>
        <p id="closeStatusMessage">
            Are you sure you want to change the status of this job post to 
            <strong>Closed</strong>? The post will no longer accept new applications.
        </p>
        <div class="modal-buttons">
            <button type="button" class="btn-no" id="cancelCloseStatus">Cancel</button>
            <button type="button" class="btn-yes" id="confirmCloseStatus">Close Post</button>
        </div>
    </div>
</div>


<div class="delete-user-modal" id="deletePostingModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-exclamation-triangle delete-warning-icon"></i>
        </div>
        <h2>Delete Posting</h2>
        <p class="delete-warning-text">This action cannot be undone.</p>
        <p class="delete-confirmation-text">Are you sure you want to delete the posting for <span id="deletePostingJobRole"></span>?</p>
        
        <input type="hidden" id="postToDeleteId" value="">

        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelDeletePosting">Cancel</button>
            <button class="btn-delete" id="confirmDeletePosting">Delete Posting</button>
        </div>
    </div>
</div>



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
  window.open('postingreport.php', '_blank');
});
</script>

<script src="../js/posting.js"></script>

</body>
</html>