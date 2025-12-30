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
  <link rel="stylesheet" href="../css/archive.css">
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
  <a href="applicants.php" class="admin-label no-underline">Job Applicants</a> > 
  <span style="font-weight: bold;">Archived Job Applicants</span>
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
$postID = isset($_GET['postid']) ? (int)$_GET['postid'] : 0;
$jobRole = "No Job Post Selected"; 
if ($postID > 0) {
    $titleStmt = $con->prepare("SELECT postjobrole FROM jobposting WHERE postid = ?");
    $titleStmt->bind_param("i", $postID);
    $titleStmt->execute();
    $titleResult = $titleStmt->get_result();
    if ($titleResult->num_rows > 0) {
        $jobData = $titleResult->fetch_assoc();
        $jobRole = htmlspecialchars($jobData['postjobrole']);
    }
    $titleStmt->close();

    // *** MODIFICATION START: Change to fetch from archived_applicants table ***
    $applicantQuery = "
        SELECT 
            a.applicationid,
            a.userid,
            DATE(a.date_applied) as date_applied_only,
            a.status,
            u.firstname,
            u.lastname,
            u.email,
            u.image 
        FROM 
            archived_applicants a 
        INNER JOIN 
            users u ON a.userid = u.userid
        WHERE 
            a.postid = ?
        ORDER BY 
            a.date_applied DESC";
            
    $stmt = $con->prepare($applicantQuery);
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $applicantResult = $stmt->get_result();
    $applicantsFound = false;

} else {
    $applicantResult = false;
    $applicantsFound = false;
}

?>

<section class="dashboard-overview">
    <div class="overview-header">
        <h2>Archived Job Applicants</h2>
        <p>Viewing archived application for: <span style="font-weight: bold;"><?php echo $jobRole; ?></span></p>
        <input type="hidden" id="currentViewingJobRole" value="<?php echo htmlspecialchars($jobRole); ?>">
        <input type="hidden" id="currentPostId" value="<?php echo $postID; ?>">
    </div>
    
    <div class="users-section">
        <a href="applicants.php?postid=<?php echo $postID; ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> BACK
        </a>


        <div class="bordered-section">
            
            <div class="section-header">
                <div>
                    <h3>Archived List</h3> 
                    <p>A list of all archived application.</p>
                </div>
            </div>
            
            <div class="search-filter-controls">
                <div class="search-box">
                    <input type="text" class="search-input" id="applicantSearch" placeholder="Search applicants by name or email...">
                </div>
                <div class="filters">
                    <select class="filter-select" id="jobPostSortOrder">
                        <option value="default">Default Order</option>
                        <option value="asc">Name (A-Z)</option>
                        <option value="desc">Name (Z-A)</option>
                        <option value="newest">Date Applied (Newest)</option>
                        <option value="oldest">Date Applied (Oldest)</option>
                    </select>
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
                    <tbody>
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
                                $fullName = $firstName . ' ' . $lastName;
                                $image = $applicant['image']; 
                                $status = htmlspecialchars($applicant['status']);

                                    // Convert full status to shortened display name
                                    switch ($status) {
                                        case 'Job Offer':
                                            $displayStatus = 'J Offer';
                                            break;
                                        case 'Job Offer Accepted':
                                            $displayStatus = 'J.O Acc.';
                                            break;
                                        case 'Job Offer Rejected':
                                            $displayStatus = 'J.O Rej.';
                                            break;
                                        case 'Initial Interview':
                                            $displayStatus = 'Init I.';
                                            break;
                                        case 'Technical Interview':
                                            $displayStatus = 'Tech I.';
                                            break;
                                        default:
                                            $displayStatus = $status;
                                            break;
                                    }


                                echo "<tr data-appid='" . $appID . "' data-userid='" . $userID . "'>";
                                echo "<td>" . $appID . "</td>"; 
                                echo "<td>" . $userID . "</td>";
                                
                                echo "<td><div class='user-cell'>";
                                if (!empty($image)) {
                                    $userProfileImage = 'data:image/jpeg;base64,' . base64_encode($image);
                                    echo "<img src='" . $userProfileImage . "' alt='User Avatar' class='avatar-circle-small'>";
                                } else {
                                    echo "<div class='avatar-circle-small' style='background-color: #2f80ed;'>";
                                    echo strtoupper(substr($applicant['firstname'], 0, 1)); 
                                    echo "</div>";
                                }
                                echo htmlspecialchars($fullName);
                                echo "</div></td>";

                                echo "<td>" . $email . "</td>";
                                echo "<td>" . $dateApplied . "</td>";

                                // Apply the colored status tag (Hardcoded 'Archived')
                                echo "<td><span class='status-tag status-pending'>" . htmlspecialchars($displayStatus) . " - Archived</span></td>";

                                echo "<td><a href='view_resume_archived.php?appid=" . $appID . "' class='view-resume' title='View Resume' target='_blank'><i class='fas fa-file'></i> View</a></td>";

                                // *** MODIFICATION START: Update Action Icons for Archive Page ***
                                echo "<td class='actions-cell archived-actions'>";
                                // 1. View Applicant Icon (REMOVED)

                                // 2. Retrieve Icon
                                echo "<i class='fas fa-redo-alt action-icon retrieve-applicant' data-appid='" . $appID . "' data-name='" . $fullName . "' title='Retrieve Application to Active List'></i>";

                                // 3. Delete Permanently Icon
                                echo "<i class='fas fa-trash action-icon delete-permanent-applicant' data-appid='" . $appID . "' data-name='" . $fullName . "' title='Permanently Delete Application'></i>"; 
                                echo "</td>"; 
                                // *** MODIFICATION END ***

                                echo "</tr>";
                            }
                            $stmt->close();
                        }

                        if (!$applicantsFound) {
                            $colspan = 8; // Change colspan to 8 since there are now 8 columns
                            $display = ($postID > 0) ? "No archived applicants found for this job post." : "Error: No job post ID provided.";
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

  <div class="delete-user-modal" id="deletePermanentApplicantModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-trash delete-warning-icon"></i> 
        </div>
        <h2>Permanently Delete Application</h2>
        <p class="delete-warning-text" style="font-weight: bold;">WARNING: This action is irreversible.</p>
        <p class="delete-confirmation-text">Are you absolutely sure you want to permanently delete the application for <span id="deletePermanentApplicantFullName" style="font-weight: bold;">[Applicant Name]</span> (ID: <span id="deletePermanentApplicantAppId" style="font-weight: bold;">[ID]</span>)?</p>
        
        <input type="hidden" id="appToDeletePermanentId" value="">

        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelDeletePermanentApplicant">Cancel</button>
            <button class="btn-delete" id="confirmDeletePermanentApplicant">Delete Permanently</button>
        </div>
    </div>
</div>

<div class="delete-user-modal" id="retrieveApplicantModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-redo-alt confirm-icon"></i> 
        </div>
        <h2>Retrieve Application</h2>
        <p class="retrieve-warning-text">Retrieving this application will move it back to the active list with a 'Pending' status.</p>
        <p class="delete-confirmation-text">Are you sure you want to retrieve the application for <span id="retrieveApplicantFullName" style="font-weight: bold;">[Applicant Name]</span> (ID: <span id="retrieveApplicantAppId" style="font-weight: bold;">[ID]</span>)?</p>
        
        <input type="hidden" id="appToRetrieveId" value="">

        <div class="modal-buttons">
            <button class="btn-no" id="cancelRetrieveApplicant">Cancel</button>
            <button class="btn-yes" id="confirmRetrieveApplicant" >Retrieve</button>
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




<script>
/**
 * Generates initials from a full name (used for avatars without an image).
 * @param {string} fullName - The full name of the user.
 * @returns {string} The capital initials (e.g., 'JS' for 'John Smith').
 */
function generateInitials(fullName) {
    if (!fullName) return '';
    const parts = fullName.split(/\s+/);
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

/**
 * Toggles the action dropdown for a table row.
 * @param {HTMLElement} targetIcon - The icon that was clicked.
 */
function toggleActionDropdown(targetIcon) {
    const wrapper = targetIcon.closest('.action-dropdown-wrapper');
    if (!wrapper) return;

    const dropdown = wrapper.querySelector('.action-dropdown');
    if (!dropdown) return;

    // Close all other open dropdowns
    document.querySelectorAll('.action-dropdown').forEach(d => {
      if (d !== dropdown) {
        d.style.display = 'none';
      }
    });

    // Toggle the current dropdown
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    if (!isVisible) {
      function closeOnOutsideClick(event) {
        if (!wrapper.contains(event.target)) {
          dropdown.style.display = 'none';
          document.removeEventListener('click', closeOnOutsideClick);
        }
      }
      setTimeout(() => {
        document.addEventListener('click', closeOnOutsideClick);
      }, 0);
    }
}

/**
 * Helper function to calculate age.
 * @param {string} dateOfBirth - Date of birth in 'YYYY-MM-DD' format.
 * @returns {number|string} The calculated age or empty string if invalid/missing DOB.
 */
function calculateAge(dateOfBirth) {
    // Return empty string if DOB is null, empty, or '0000-00-00'
    if (!dateOfBirth || dateOfBirth === '0000-00-00') return '';
    const dob = new Date(dateOfBirth);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}

// Global function for User Profile Dropdown Toggle
function toggleDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    if (!isVisible) {
        document.addEventListener('click', function closeOnClick(event) {
            // Check if the click is outside the dropdown and the profile icon
            if (
                !event.target.closest('.user-profile') &&
                !event.target.closest('#user-dropdown')
            ) {
                dropdown.style.display = 'none';
                document.removeEventListener('click', closeOnClick);
            }
        }, { once: false });
    }
}




// ======================================================================
// 3. MAIN DOM CONTENT LOADED INITIALIZATION 🚀
// ======================================================================

document.addEventListener('DOMContentLoaded', function () {
    
    // --- 3.1. DOM Element Selectors ---
    
    // Modals
    const logoutModal = document.getElementById('logoutModal');
    // const viewUserProfileModal = document.getElementById('viewUserProfileModal'); // REMOVED
    const adminProfileModal = document.getElementById('adminUserProfileModal');
    const nullFieldInstruction = document.getElementById('nullFieldInstruction'); // Instruction for null fields
    const retrieveApplicantModal = document.getElementById('retrieveApplicantModal'); // NEW
    const deletePermanentApplicantModal = document.getElementById('deletePermanentApplicantModal'); // NEW
    
    // Buttons/Toggles
    const cancelLogout = document.getElementById('cancelLogout');
    const logoutBtnSidebar = document.getElementById('logoutBtn');
    const logoutBtnDropdown = document.getElementById('logoutDropdownBtn');
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');
    // const closeUserProfileModalBtn = document.getElementById('closeUserProfileModal'); // REMOVED
    // const profileModalCloseBtn = document.getElementById('profileModalCloseBtn'); // REMOVED
    const viewProfileModalBtn = document.getElementById('viewProfileModalBtn'); // Open Admin Profile Modal
    const closeAdminUserProfileModal = document.getElementById('closeAdminUserProfileModal'); // Admin Profile Modal close X
    const adminProfileModalCloseBtn = document.getElementById('adminProfileModalCloseBtn'); // Admin Profile Modal close button (Cancel/Close)
    const editProfileBtn = document.getElementById('editProfileBtn');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const adminImageInput = document.getElementById('adminImageInput');
    const applicantsTable = document.getElementById('applicantsTable');
    
    // Table Controls
    const applicantSearch = document.getElementById('applicantSearch');
    // **FIX 1: CORRECTED the ID to match the HTML.**
    const applicantSortOrder = document.getElementById('jobPostSortOrder');
    const applicantRowsPerPage = document.getElementById('applicantRowsPerPage');
    const applicantTableBody = document.querySelector('#applicantsTable tbody');
    const applicantPaginationInfo = document.getElementById('applicantPaginationInfo');
    const applicantPrevPage = document.getElementById('applicantPrevPage');
    const applicantNextPage = document.getElementById('applicantNextPage');
    const applicantPageNumbers = document.getElementById('applicantPageNumbers');

    // --- New Action Modal Handlers (Retrieve and Permanent Delete) ---
    const appToRetrieveId = document.getElementById('appToRetrieveId');
    const retrieveApplicantFullName = document.getElementById('retrieveApplicantFullName');
    const retrieveApplicantAppId = document.getElementById('retrieveApplicantAppId');
    const cancelRetrieveApplicant = document.getElementById('cancelRetrieveApplicant');
    const confirmRetrieveApplicant = document.getElementById('confirmRetrieveApplicant');

    const appToDeletePermanentId = document.getElementById('appToDeletePermanentId');
    const deletePermanentApplicantFullName = document.getElementById('deletePermanentApplicantFullName');
    const deletePermanentApplicantAppId = document.getElementById('deletePermanentApplicantAppId');
    const cancelDeletePermanentApplicant = document.getElementById('cancelDeletePermanentApplicant');
    const confirmDeletePermanentApplicant = document.getElementById('confirmDeletePermanentApplicant');

    cancelRetrieveApplicant?.addEventListener('click', () => {
        if (retrieveApplicantModal) retrieveApplicantModal.style.display = 'none';
    });
    
    cancelDeletePermanentApplicant?.addEventListener('click', () => {
        if (deletePermanentApplicantModal) deletePermanentApplicantModal.style.display = 'none';
    });
    
    // --- Confirmation Logic for Retrieve ---
    confirmRetrieveApplicant?.addEventListener('click', async () => {
        const appId = appToRetrieveId.value;
        if (!appId) return;

        try {
            const response = await fetch('archive_applicant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=retrieve&appid=${appId}`
            });
            const data = await response.json();

            if (data.success) {
                window.location.reload(); 
            } else {
                alert('Retrieve Failed: ' + data.message);
            }
        } catch (error) {
            console.error('Error retrieving applicant:', error);
            alert('An error occurred during the retrieve operation.');
        }
    });

    // --- Confirmation Logic for Permanent Delete ---
    confirmDeletePermanentApplicant?.addEventListener('click', async () => {
        const appId = appToDeletePermanentId.value;
        if (!appId) return;

        try {
            const response = await fetch('archive_applicant.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_permanent&appid=${appId}`
            });
            const data = await response.json();

            if (data.success) {
                window.location.reload(); 
            } else {
                alert('Deletion Failed: ' + data.message);
            }
        } catch (error) {
            console.error('Error deleting applicant permanently:', error);
            alert('An error occurred during the permanent delete operation.');
        }
    });

    // Admin Profile Fields/Summary Elements
    const profileFields = {
      adminName: document.getElementById('viewAdminFullName'),
      adminEmail: document.getElementById('viewAdminEmail'),
    };
    const profileSummaryElements = {
        profileFullNameModal: document.getElementById('profileFullNameModal'),
        profileEmailSummaryModal: document.getElementById('profileEmailSummaryModal'),
        profileAvatarModal: document.getElementById('profileAvatarModal')
    };

    // Admin Profile State Variables
    let isEditing = false;
    let originalAdminName = profileFields.adminName?.value || '';
    let originalAdminEmail = profileFields.adminEmail?.value || '';
    let originalAdminImageBase64 = document.getElementById('originalAdminImageBase64')?.value || '';
    let tempAdminImageFile = null;

    
    // --- 3.2. Core Utility Functions (Admin Profile & Modals) ---

    /**
     * Toggles the readOnly state of admin profile fields and button visibility.
     * @param {boolean} enable - True to enable editing, false to disable.
     */
    function toggleEditMode(enable) {
        isEditing = enable;
        if (profileFields.adminName) profileFields.adminName.readOnly = !enable;
        if (profileFields.adminEmail) profileFields.adminEmail.readOnly = !enable;

        if (editProfileBtn) editProfileBtn.style.display = enable ? 'none' : 'inline-block';
        if (saveProfileBtn) saveProfileBtn.style.display = enable ? 'inline-block' : 'none';
        if (changePhotoBtn) changePhotoBtn.style.display = enable ? 'inline-block' : 'none';

        if (adminProfileModalCloseBtn) adminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

        if (profileFields.adminName) profileFields.adminName.classList.toggle('editable-field', enable);
        if (profileFields.adminEmail) profileFields.adminEmail.classList.toggle('editable-field', enable);
    }

    /**
     * Restores the original image/initials in the modal and main profile area.
     */
    function restoreOriginalImageInModal() {
        const avatarElement = profileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

        const updateAvatar = (element, sizeClass) => {
            if (!element) return;
            // First, remove existing image/initials
            if (element.tagName === 'IMG') {
                const tempDiv = document.createElement('div');
                element.replaceWith(tempDiv);
                element = tempDiv;
            }
            element.innerHTML = '';
            element.className = sizeClass;

            if (originalAdminImageBase64) {
                const imgElement = document.createElement('img');
                imgElement.src = originalAdminImageBase64;
                imgElement.alt = "Admin Avatar";
                imgElement.classList.add(sizeClass);
                element.replaceWith(imgElement);
                return imgElement;
            } else {
                element.classList.add(sizeClass);
                element.textContent = generateInitials(originalAdminName);
                return element;
            }
        };

        if (avatarElement) {
            profileSummaryElements.profileAvatarModal = updateAvatar(avatarElement, 'avatar-circle-large');
        }
        if (mainAvatarElement) {
            updateAvatar(mainAvatarElement, 'avatar-circle');
        }
    }

    
    const closeAdminProfile = () => {
        if (adminProfileModal.style.display === 'flex' && isEditing) {
            if (profileFields.adminName) profileFields.adminName.value = originalAdminName;
            if (profileFields.adminEmail) profileFields.adminEmail.value = originalAdminEmail;
            restoreOriginalImageInModal();
            tempAdminImageFile = null;
        }
        if (adminProfileModal) adminProfileModal.style.display = 'none';
        toggleEditMode(false);
    };
    
    // --- Logout Modal Handlers ---
    const openLogoutModal = (e) => {
      e.preventDefault();
      if (logoutModal) logoutModal.style.display = 'flex';
    };

    logoutBtnSidebar?.addEventListener('click', openLogoutModal);
    logoutBtnDropdown?.addEventListener('click', openLogoutModal);

    cancelLogout?.addEventListener('click', function () {
      if (logoutModal) logoutModal.style.display = 'none';
    });

    // --- Sidebar Toggle ---
    burger?.addEventListener('click', () => {
      sidebar?.classList.toggle('active');
      burger.classList.toggle('active');
    });
    
    // --- View Applicant Modal Closing Logic (REMOVED) ---
    // const closeApplicantModal ... (REMOVED)
    // closeUserProfileModalBtn ... (REMOVED)
    // profileModalCloseBtn ... (REMOVED)

    // Close when clicking outside the applicant modal content
    window.addEventListener('click', function(event) {
        // if (event.target == viewUserProfileModal) { ... } // REMOVED
        if (event.target == retrieveApplicantModal) {
            retrieveApplicantModal.style.display = 'none';
        }
        if (event.target == deletePermanentApplicantModal) {
            deletePermanentApplicantModal.style.display = 'none';
        }
    });

    // --- Admin Profile Modal Handlers ---
    viewProfileModalBtn?.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('user-dropdown').style.display = 'none'; 
      originalAdminName = profileFields.adminName.value;
      originalAdminEmail = profileFields.adminEmail.value;
      originalAdminImageBase64 = document.getElementById('originalAdminImageBase64').value; 
      tempAdminImageFile = null; 
      restoreOriginalImageInModal();
      toggleEditMode(false); 
      if (adminProfileModal) adminProfileModal.style.display = 'flex';
    });

    closeAdminUserProfileModal?.addEventListener('click', closeAdminProfile);
    adminProfileModalCloseBtn?.addEventListener('click', closeAdminProfile);

    editProfileBtn?.addEventListener('click', function () {
      toggleEditMode(true);
    });

    saveProfileBtn?.addEventListener('click', async function () {
        const newAdminName = profileFields.adminName.value.trim();
        const newAdminEmail = profileFields.adminEmail.value.trim();

        if (!newAdminName || !newAdminEmail) {
            alert('Full Name and Email Address cannot be empty.');
            return;
        }
        if (!/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/.test(newAdminEmail)) {
            alert('Please enter a valid email address.');
            return;
        }

        const formData = new FormData();
        formData.append('adminname', newAdminName);
        formData.append('adminemail', newAdminEmail);

        if (tempAdminImageFile) {
            formData.append('adminImage', tempAdminImageFile);
        } else if (originalAdminImageBase64 === '') {
             formData.append('adminImage', '');
        }

        try {
            const response = await fetch('update_admin_profile.php', {
                method: 'POST',
                body: formData 
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload(); 
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('An error occurred while updating profile.');
        }
    });

    changePhotoBtn?.addEventListener('click', function () {
        if (adminImageInput) adminImageInput.click();
    });

    adminImageInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            tempAdminImageFile = this.files[0]; 
            const reader = new FileReader();
            reader.onload = function (e) {
                let avatarElement = profileSummaryElements.profileAvatarModal;
                if (!avatarElement) return;
                if (avatarElement.tagName === 'DIV') { 
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    imgElement.alt = "Admin Avatar";
                    imgElement.classList.add('avatar-circle-large');
                    avatarElement.replaceWith(imgElement);
                    profileSummaryElements.profileAvatarModal = imgElement;
                } else { 
                    avatarElement.src = e.target.result;
                }
            };
            reader.readAsDataURL(tempAdminImageFile); 
        }
    });
    
    // --- Applicants Table Universal Listener (UPDATED to include new actions) ---
    applicantsTable?.addEventListener('click', function (e) {
        
        // Listener for the View Applicant Icon (REMOVED)
        
        // Listener for Retrieve Icon (NEW)
        const retrieveApplicantIcon = e.target.closest('.retrieve-applicant');
        if (retrieveApplicantIcon) {
            e.preventDefault();
            const appId = retrieveApplicantIcon.getAttribute('data-appid');
            const fullName = retrieveApplicantIcon.getAttribute('data-name');

            if (appToRetrieveId) appToRetrieveId.value = appId;
            if (retrieveApplicantFullName) retrieveApplicantFullName.textContent = fullName;
            if (retrieveApplicantAppId) retrieveApplicantAppId.textContent = appId;
            
            if (retrieveApplicantModal) retrieveApplicantModal.style.display = 'flex';
        }

        // Listener for Delete Permanent Icon (NEW)
        const deletePermanentApplicantIcon = e.target.closest('.delete-permanent-applicant');
        if (deletePermanentApplicantIcon) {
            e.preventDefault();
            const appId = deletePermanentApplicantIcon.getAttribute('data-appid');
            const fullName = deletePermanentApplicantIcon.getAttribute('data-name');

            if (appToDeletePermanentId) appToDeletePermanentId.value = appId;
            if (deletePermanentApplicantFullName) deletePermanentApplicantFullName.textContent = fullName;
            if (deletePermanentApplicantAppId) deletePermanentApplicantAppId.textContent = appId;
            
            if (deletePermanentApplicantModal) deletePermanentApplicantModal.style.display = 'flex';
        }
    });


    // --- Table Search, Filter, Pagination Logic (FIXED SECTION) ---
    let allApplicantData = [];
    let filteredData = [];
    let currentPage = 1;
    let rowsPerPage = parseInt(applicantRowsPerPage?.value) || 5;
    const MAX_PAGE_BUTTONS = 5;

    function collectAllApplicantData() {
        allApplicantData = [];
        if (!applicantTableBody) return;
        const rows = applicantTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.cells.length < 8) return; 
            
            // Check for the "No Applicants Found" row and skip it
            if (row.cells.length === 1 && row.cells[0].hasAttribute('colspan')) return;
            
            const fullName = row.cells[2].textContent.trim();
            const email = row.cells[3].textContent.trim().toLowerCase();
            const dateAppliedText = row.cells[4].textContent.trim();
            
            const dateApplied = new Date(dateAppliedText);

            allApplicantData.push({
                rowElement: row,
                fullName: fullName,
                email: email,
                dateApplied: dateApplied,
                // We create a combined text string to make searching easier
                searchText: (fullName.toLowerCase() + ' ' + email)
            });
        });
    }

    function renderApplicantTable() {
        if (!applicantTableBody || !applicantPaginationInfo || !applicantPrevPage || !applicantNextPage || !applicantPageNumbers) return;

        const searchTerm = applicantSearch.value.toLowerCase().trim();
        
        // **FIX 2: REMOVED the broken status filter logic.**
        // Now, we only filter based on the search term.
        filteredData = allApplicantData.filter(data => {
            return data.searchText.includes(searchTerm);
        });

        // 2. Sorting
        const sortOrder = applicantSortOrder.value;
        filteredData.sort((a, b) => {
            if (sortOrder === 'asc') return a.fullName.localeCompare(b.fullName);
            if (sortOrder === 'desc') return b.fullName.localeCompare(a.fullName);
            if (sortOrder === 'newest') return b.dateApplied.getTime() - a.dateApplied.getTime();
            if (sortOrder === 'oldest') return a.dateApplied.getTime() - b.dateApplied.getTime();
            return 0; // default order
        });

        // 3. Pagination Setup
        const totalRows = filteredData.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedData = filteredData.slice(start, end);

        // 4. Render Table Body
        applicantTableBody.innerHTML = '';
        if (paginatedData.length === 0) {
            const noDataRow = document.createElement('tr');
            const noDataCell = document.createElement('td');
            noDataCell.colSpan = 8;
            noDataCell.style.textAlign = 'center';
            noDataCell.textContent = 'No applicants found matching your criteria.';
            noDataRow.appendChild(noDataCell);
            applicantTableBody.appendChild(noDataRow);
        } else {
            paginatedData.forEach(data => {
                applicantTableBody.appendChild(data.rowElement);
            });
        }

        // 5. Update Pagination Info
        const startCount = totalRows === 0 ? 0 : start + 1;
        const endCount = Math.min(end, totalRows);
        applicantPaginationInfo.textContent = `Showing ${startCount} to ${endCount} of ${totalRows} applicants`;

        // 6. Update Pagination Buttons
        applicantPrevPage.disabled = currentPage === 1;
        applicantNextPage.disabled = currentPage === totalPages;

        applicantPageNumbers.innerHTML = '';
        let startPage = Math.max(1, currentPage - Math.floor(MAX_PAGE_BUTTONS / 2));
        let endPage = Math.min(totalPages, startPage + MAX_PAGE_BUTTONS - 1);
        if (endPage - startPage + 1 < MAX_PAGE_BUTTONS) {
            startPage = Math.max(1, endPage - MAX_PAGE_BUTTONS + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'pagination-button page-number';
            if (i === currentPage) pageBtn.classList.add('active');
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                renderApplicantTable();
            });
            applicantPageNumbers.appendChild(pageBtn);
        }
    }

    // Event Listeners for Table Controls
    applicantSearch?.addEventListener('input', () => {
        currentPage = 1;
        renderApplicantTable();
    });

    applicantSortOrder?.addEventListener('change', () => {
        currentPage = 1;
        renderApplicantTable();
    });

    applicantRowsPerPage?.addEventListener('change', (e) => {
        rowsPerPage = parseInt(e.target.value) || 5;
        currentPage = 1;
        renderApplicantTable();
    });

    applicantPrevPage?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderApplicantTable();
        }
    });

    applicantNextPage?.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredData.length / rowsPerPage) || 1;
        if (currentPage < totalPages) {
            currentPage++;
            renderApplicantTable();
        }
    });

    // Initialize the table data and render the initial view
    collectAllApplicantData();
    renderApplicantTable();
});
</script>

</body>
</html>