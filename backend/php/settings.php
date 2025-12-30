


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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>

  <link rel="stylesheet" href="../css/settings.css">

</head>
<?php

include 'database.php'; 
include 'phpsettings.php';
include 'my_admin_profile_modal.php';

$isMaintenanceMode = file_exists(__DIR__ . '/../../.maintenance');

?>

<body>

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
            <a href="posting.php">
                 <i class="fas fa-briefcase"></i> Job Posting
            </a>

            <a class="active" aria-current="page" >
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
                    <div class="admin-label">Homepage > <span style="font-weight: bold;">Settings</span></div>
                </div>

                <div class="user-profile" onclick="toggleDropdown()">
                        <?php if ($adminImageBase64): ?>
                            <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle">
                        <?php else: ?>
                            <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
                        <?php endif; ?>                    <div class="user-info">
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
        <div id="customAlert" class="custom-alert">Message</div>

        <section class="dashboard-overview">
            <div class="overview-header">
                <h2>Website Settings</h2>
                <p>Manage your website configuration and security settings</p>
            </div>

            <div class="settings-container">
                <div class="settings-menu">
                    <a href="#change-password" class="menu-item active" data-target="change-password-content">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <a href="#maintenance-mode" class="menu-item" data-target="maintenance-mode-content">
                        <i class="fas fa-wrench"></i> Maintenance Mode
                    </a>
                    <a href="#site-identity" class="menu-item" data-target="site-identity-content">
                        <i class="fas fa-image"></i> Site Identity
                    </a>
                    <a href="#website-content" class="menu-item" data-target="website-content-tab">
                        <i class="fas fa-file-alt"></i> Website Content
                    </a>
                    <a href="#feedback" class="menu-item" data-target="feedback-content">
                        <i class="fas fa-comments"></i> Feedback
                    </a>
                </div>

                <div class="settings-content">
                    <div id="change-password-content" class="content-section active">
                        <div class="card-header">
                            <i class="fas fa-lock"></i> Change Password
                        </div>
                        <p class="card-description">Update your account password for better security</p>
                        <form id="changePasswordForm" method="POST" action="settings.php">
                            <div class="form-group">
                           
                             <?php if (!empty($password_message_type) && $password_message_type === 'error'): ?>
                                <div id="password-feedback" style="margin: 1rem 0 0 11rem; font-weight: bold; color: #dc3545; ">
                                    <?php echo htmlspecialchars($password_message); ?>
                                </div>
                            <?php endif; ?>
                                <label for="current-password">Current Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="current-password" name="current_password" class="form-control" required />
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new-password">New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="new-password" name="new_password" class="form-control" required />
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>
                            <div class="strength-bar">
                                <div class="strength-bar-inner" id="strength-bar"></div>
                            </div>

                            <div class="password-criteria">
                                <div class="criteria-grid">
                                    <div class="criteria-item" id="length"><i class="fas fa-circle"></i> - 8+ characters</div>
                                    <div class="criteria-item" id="upper"><i class="fas fa-circle"></i> - Uppercase letter</div>
                                    <div class="criteria-item" id="lower"><i class="fas fa-circle"></i> - Lowercase letter</div>
                                    <div class="criteria-item" id="number"><i class="fas fa-circle"></i> - Number</div>
                                    <div class="criteria-item" id="special"><i class="fas fa-circle"></i> - Special character</div>
                                </div><br>
                            </div>  
                            <div class="form-group">
                                <label for="confirm-new-password">Confirm New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirm-new-password" name="confirm_new_password" class="form-control" required />
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>
                            <button type="submit" name="change_password_submit" class="save-button primary-button">Save Password</button>
                        </form>
                    </div>

                    <div id="maintenance-mode-content" class="content-section">
                        <div class="card-header">
                            <i class="fas fa-wrench"></i> Maintenance Mode
                        </div>
                        <p class="card-description">Control site accessibility during updates</p>
                        <div class="form-group-toggle">
                            <label for="maintenance-toggle">Temporarily Close Site</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="maintenance-toggle" class="toggle-input" <?php echo $isMaintenanceMode ? 'checked' : ''; ?> />
                                <label for="maintenance-toggle" class="toggle-label"></label>
                            </div>
                        </div>
                        <p class="toggle-description">Enable to show a maintenance message on the frontend. Only administrators can access the backend.</p>
                        
                        <div id="maintenance-status-message" class="status-message">
                            </div>
                    </div>

                    <div id="site-identity-content" class="content-section">
                        <div class="card-header">
                            <i class="fas fa-image"></i> Site Identity
                        </div>
                        <p class="card-description">Customize your website's branding and appearance</p>
                        <form id="siteIdentityForm" method="POST" action="settings.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="site-logo">Site Logo</label>
                                <div class="upload-area">
                                    <div class="logo-placeholder">
                                      <img id="current-logo-preview" src="<?php echo $systemLogoBase64; ?>" alt="Site Logo" style="<?php echo ($systemLogoBase64 === '../image/jftlogo.png') ? 'display: none;' : ''; ?> max-width: 100px; max-height: 100px; object-fit: contain;">                                        <i class="fas fa-image" id="default-logo-icon" style="<?php echo ($systemLogoBase64 !== '../image/jftlogo.png') ? 'display: none;' : ''; ?>"></i>
                                    </div>
                                    <label for="upload-logo" class="upload-button">
                                        <i class="fas fa-upload"></i> Upload Logo
                                    </label>
                                    <input type="file" id="upload-logo" name="upload_logo" style="display: none;" accept="image/png, image/jpeg, image/jpg">
                                </div>
                                <p class="upload-recommendation">Recommended: 200x200px, PNG or JPG format</p>
                            </div>
                            <div class="form-group">
                                <label for="site-name">Site Name</label>
                                <input type="text" id="site-name" name="site_name" class="form-control" value="<?php echo $systemName; ?>" required />
                                <p class="form-description">This will appear in your website header</p>
                            </div>
                            <button type="submit" name="site_identity_submit" class="save-button primary-button">Save Changes</button>
                            <?php if (!empty($site_identity_message_type) && $site_identity_message_type === 'error'): ?>
                                <div style="margin-top: 1rem; font-weight: bold; color: #dc3545;">
                                    <?php echo htmlspecialchars($site_identity_message); ?>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div id="website-content-tab" class="content-section">
                        <div class="card-header">
                            <i class="fas fa-file-alt"></i> Website Content
                        </div>
                        <p class="card-description">Edit the content for your public-facing website pages.</p>
                        
              <form id="websiteContentForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="aboutus_home">About Us - Home</label>
                                <textarea id="aboutus_home" name="aboutus_home" class="form-control textarea-large" placeholder="Enter your home page about us content..."><?php echo htmlspecialchars($aboutus_home); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="upload-banner">Banner</label>
                                <div class="image-preview-box" id="banner-preview-box">
                                    <img 
                                        id="banner-preview-img" 
                                        class="image-preview-img <?php echo !empty($banner_base64) ? 'has-image' : ''; ?>" 
                                        src="<?php echo $banner_base64; ?>" 
                                        alt="Banner Preview"
                                    >
                                    <div 
                                        class="placeholder-content <?php echo !empty($banner_base64) ? 'has-image' : ''; ?>" 
                                        id="banner-placeholder"
                                    >
                                        <i class="fas fa-image placeholder-icon"></i>
                                        <span class="placeholder-text">Banner Image Preview<br>Full width banner display</span>
                                    </div>
                                </div>
                                <label for="upload-banner" class="upload-button">
                                    <i class="fas fa-upload"></i> Upload Banner Image
                                </label>
                                <input type="file" id="upload-banner" name="banner" style="display: none;" accept="image/png, image/jpeg, image/jpg">
                            </div>

                            <hr style="margin: 2rem 0;">

                            <h3>About Us - Company</h3>
                            
                            <div class="content-grid-2col">
                                <div class="form-group">
                                    <label for="who_we_are">Who We Are</label>
                                    <textarea id="who_we_are" name="who_we_are" class="form-control textarea-large" placeholder="Describe who you are as a company..."><?php echo htmlspecialchars($who_we_are); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="upload-group-photo">Group Photo</label>
                                    <div class="image-preview-box" id="group-photo-preview-box">
                                        <img 
                                            id="group-photo-preview-img" 
                                            class="image-preview-img <?php echo !empty($group_photo_base64) ? 'has-image' : ''; ?>" 
                                            src="<?php echo $group_photo_base64; ?>" 
                                            alt="Group Photo Preview"
                                        >
                                        <div 
                                            class="placeholder-content <?php echo !empty($group_photo_base64) ? 'has-image' : ''; ?>" 
                                            id="group-photo-placeholder"
                                        >
                                            <i class="fas fa-users placeholder-icon"></i>
                                            <span class="placeholder-text">Group Photo</span>
                                        </div>
                                    </div>
                                    <label for="upload-group-photo" class="upload-button">
                                        <i class="fas fa-upload"></i> Upload
                                    </label>
                                    <input type="file" id="upload-group-photo" name="group_photo" style="display: none;" accept="image/png, image/jpeg, image/jpg">
                                </div>

                                <div class="form-group">
                                    <label for="mission">Mission</label>
                                    <textarea id="mission" name="mission" class="form-control textarea-large" placeholder="Enter your company mission..."><?php echo htmlspecialchars($mission); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="vision">Vision</label>
                                    <textarea id="vision" name="vision" class="form-control textarea-large" placeholder="Enter your company vision..."><?php echo htmlspecialchars($vision); ?></textarea>
                                </div>
                            </div> <div class="form-group">
                                <label for="quality_policy">Quality Policy</label>
                                <textarea id="quality_policy" name="quality_policy" class="form-control textarea-large" placeholder="Enter your quality policy statement..."><?php echo htmlspecialchars($quality_policy); ?></textarea>
                            </div>

                            <button type="submit" name="website_content_submit" class="save-button primary-button">Save All Content</button>
                        </form>
                    </div>


                    <div id="feedback-content" class="content-section">
                        <div class="users-section">
                                <div class="card-header-container">
                                <div class="card-header">
                                    <i class="fas fa-comments"></i> User Feedback
                                </div>

                                <div class="card-header-right">
                                    <button class="report-button" id="generateReportBtn">
                                    <i class="fas fa-file-alt"></i> Generate Report
                                    </button>
                                </div>
                                </div>

                                <p class="card-description">A list of all feedback received from users.</p>

                                <div class="search-filter-controls">
                                    <div class="search-box">
                                        <input type="text" class="search-input" id="feedbackSearch" placeholder="Search feedback...">
                                    </div>
                                    <div class="filters">
                                        <select class="filter-select" id="adminStatusFilter">
                                            <option value="">Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Replied">Replied</option>
                                            <option value="Resolved">Resolved</option>
                                        </select>
                                        <select class="filter-select" id="feedbackSortOrder">
                                            <option value="default">Default Order</option>
                                            <option value="newest">Newest First</option>
                                            <option value="oldest">Oldest First</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-container">
                                    <table id="feedbackTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>DATE</th>
                                                <th>NAME</th>
                                                <th>SUBJECT</th>
                                                <th>STATUS</th>
                                                <th>ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Modified query to fetch all details needed for the modal as well
                                            $feedbackResult = mysqli_query($con, "SELECT conid, condate, conname, conemail, conphone, consubject, conmessage, constatus FROM contactus ORDER BY condate DESC");
                                            if ($feedbackResult && mysqli_num_rows($feedbackResult) > 0) {
                                                while ($feedback = mysqli_fetch_assoc($feedbackResult)) {
                                                    $isResolved = (strtolower($feedback['constatus']) === 'resolved');
                                                    echo "<tr
                                                        data-conid='" . htmlspecialchars($feedback['conid']) . "'
                                                        data-condate='" . htmlspecialchars(date('F j, Y', strtotime($feedback['condate']))) . "'
                                                        data-conname='" . htmlspecialchars($feedback['conname']) . "'
                                                        data-conemail='" . htmlspecialchars($feedback['conemail']) . "'
                                                        data-conphone='" . htmlspecialchars($feedback['conphone']) . "'
                                                        data-consubject='" . htmlspecialchars($feedback['consubject']) . "'
                                                        data-conmessage='" . htmlspecialchars($feedback['conmessage']) . "'
                                                        data-constatus='" . htmlspecialchars($feedback['constatus']) . "'
                                                    >";
                                                    echo "<td>" . htmlspecialchars($feedback['conid']) . "</td>";
                                                    echo "<td>" . htmlspecialchars(date('F j, Y', strtotime($feedback['condate']))) . "</td>";
                                                    echo "<td>" . htmlspecialchars($feedback['conname']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($feedback['consubject']) . "</td>";
                                                    echo "<td><span class='status-tag " . 
                                                        (strtolower($feedback['constatus']) === 'resolved' ? 'resolved' : 
                                                            (strtolower($feedback['constatus']) === 'replied' ? 'replied' : 'pending')) . 
                                                        "'>" . htmlspecialchars($feedback['constatus']) . "</span></td>";
                                                    echo "<td class='actions-cell'>";
                                                    echo "<i class='fas fa-eye action-icon view-feedback' title='View Details'></i>";
                                                    echo "<div class='action-dropdown-wrapper'>";
                                                    echo "<i class='fas fa-pen-to-square action-icon edit-feedback' title='Edit Actions'></i>";
                                                    echo "<div class='action-dropdown'>";
                                                    echo "<a href='mailto:" . htmlspecialchars($feedback['conemail']) . "' class='dropdown-item' style='color:#17a2b8;'>Reply</a>";
                                                    echo "<div class='dropdown-divider'></div>";
                                                    
                                                   
                                                    echo "<form method='POST' action='settings.php' style='display:inline;' class='resolve-feedback-form'>";
                                                    echo "<input type='hidden' name='conid' value='" . $feedback['conid'] . "'>";
                                                    echo "<input type='hidden' name='feedback_action' value='resolve'>";
                                                    echo "<button type='button' class='dropdown-item resolve-feedback-btn " . ($isResolved ? 'disabled' : '') . "' style='background:none; border:none; padding:10px 15px; width:100%; text-align:left; cursor:" . ($isResolved ? 'not-allowed' : 'pointer') . "; color:#28a745; display:block;' " . ($isResolved ? 'disabled' : '') . " data-id='" . htmlspecialchars($feedback['conid']) . "' data-name='" . htmlspecialchars($feedback['conname']) . "'>Mark as Resolved</button>";
                                                    echo "</form>";
                                                  
                                                    
                                                    echo "</div>"; 
                                                    echo "</div>"; 
                                                   echo "<i class='fas fa-trash-alt action-icon delete-feedback' title='Delete Feedback' data-id='" . $feedback['conid'] . "'></i>";
                                                    echo "</td>";


                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' style='text-align: center;'>No Feedback Found</td></tr>"; // Adjusted colspan
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pagination-controls">
                                    <div class="pagination-info" id="feedbackPaginationInfo">Showing 0 to 0 of 0 feedback</div>
                                    <div class="pagination-buttons">
                                        <button class="pagination-button" id="feedbackPrevPage"><i class="fas fa-chevron-left"></i></button>
                                        <div class="page-numbers" id="feedbackPageNumbers"></div>
                                        <button class="pagination-button" id="feedbackNextPage"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                    <div class="pagination-rows-per-page">
                                        <select id="feedbackRowsPerPage" class="rows-per-page-select">
                                            <option value="5">5 per page</option>
                                            <option value="10">10 per page</option>
                                            <option value="20">20 per page</option>
                                        </select>
                                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<div class="modal user-profile-modal" id="viewFeedbackModal">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>Feedback Details</h2>
            <span class="close-button" id="closeFeedbackModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="avatar-circle-large" style="background-color: #007bff;"><i class="fas fa-comment-dots"></i></div>
                <div class="profile-name" id="feedbackViewName"></div>
                <div class="profile-email" id="feedbackViewEmailSummary"></div>
                <p class="profile-status" id="feedbackViewStatus"></p>
            </div>
            
            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">Feedback Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Feedback ID</label>
                        <input type="text" id="feedbackViewID" readonly>
                    </div>
                    <div class="form-field">
                        <label>Date Received</label>
                        <input type="text" id="feedbackViewDate" readonly>
                    </div>
                    <div class="form-field">
                        <label>Subject</label>
                        <input type="text" id="feedbackViewSubject" readonly>
                    </div>
                     <div class="form-field">
                        <label>Phone Number</label>
                        <input type="text" id="feedbackViewPhone" readonly>
                    </div>
                    <div class="form-field" style="flex: 1 1 100%;">
                        <label>Message</label>
                        <textarea id="feedbackViewMessage" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="feedbackModalCloseBtn">Close</button>
        </div>
    </div>
</div>

<div class="delete-user-modal" id="deleteFeedbackModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-exclamation-triangle delete-warning-icon"></i>
        </div>
        <h2>Delete Feedback</h2>
        <p class="delete-warning-text">This action cannot be undone.</p>
        <p class="delete-confirmation-text">Are you sure you want to delete the feedback from <span id="deleteFeedbackName" style="font-weight: bold;"></span>?</p>
        
        <input type="hidden" id="feedbackToDeleteId" value="">

        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelDeleteFeedback">Cancel</button>
            <button class="btn-delete" id="confirmDeleteFeedback">Delete Feedback</button>
        </div>
    </div>
</div>

<div class="modal confirm-modal" id="resolveFeedbackConfirmModal" style="display: none;">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-question-circle confirm-icon"></i>
        </div>
        <h2>Mark as Resolved</h2>
        <p>Are you sure you want to mark the feedback from <span id="resolveFeedbackName" style="font-weight: bold;"></span> as Resolved?</p>
        
        <input type="hidden" id="feedbackToResolveId" value="">

        <div class="modal-buttons">
            <button class="btn-no" id="cancelResolveFeedbackConfirm">No</button>
            <button class="btn-yes" id="confirmResolveFeedback">Yes, Resolve</button>
        </div>
    </div>
</div>


<div class="modal user-profile-modal" id="replyFeedbackModal" style="display: none;">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>Reply to Feedback</h2>
            <span class="close-button" id="closeReplyModal">&times;</span>
        </div>
        <form id="replyFeedbackForm" method="POST" action="settings.php">
            <div class="modal-body">
                <input type="hidden" id="conid-for-reply" name="conid_for_reply"> <div class="form-field">

                <div class="form-field">
                    <label for="reply-recipient-email">Send To</label>
                    <input type="email" id="reply-recipient-email" name="reply_recipient_email" class="form-control" readonly required>
                </div>
                <div class="form-field">
                    <label for="reply-subject">Subject</label>
                    <input type="text" id="reply-subject" name="reply_subject" class="form-control" required>
                </div>
                <div class="form-field">
                    <label for="reply-message">Message</label>
                    <textarea id="reply-message" name="reply_message" class="form-control" rows="8" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" id="cancelReplyModal">Cancel</button>
                <button type="submit" name="send_reply_email" class="save-button primary-button">Send Email</button>
            </div>
        </form>
    </div>
</div>



<?php
if (isset($_SESSION['password_message_type']) && $_SESSION['password_message_type'] === 'success') {
?>
<script>
    const alertBox = document.getElementById('customAlert');
    alertBox.textContent = 'Password Successfully Changed';
    alertBox.classList.add('show');
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
</script>
<?php
    unset($_SESSION['password_message']);
    unset($_SESSION['password_message_type']);
}
if (isset($_SESSION['site_identity_message_type']) && $_SESSION['site_identity_message_type'] === 'success') {
?>
<script>
    const alertBox = document.getElementById('customAlert');
    alertBox.textContent = 'Site Identity Successfully Updated!';
    alertBox.classList.add('show');
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
</script>
<?php
    unset($_SESSION['site_identity_message']);
    unset($_SESSION['site_identity_message_type']);
}

if (isset($_SESSION['feedback_message_type']) && $_SESSION['feedback_message_type'] === 'success') {
?>
<script>
    const alertBox = document.getElementById('customAlert');
    alertBox.textContent = '<?php echo htmlspecialchars($_SESSION['feedback_message']); ?>';
    alertBox.classList.add('show');
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
</script>
<?php
    unset($_SESSION['feedback_message']);
    unset($_SESSION['feedback_message_type']);
}
if (isset($_SESSION['profile_message_type']) && $_SESSION['profile_message_type'] === 'success') {
?>
<script>
    const alertBox = document.getElementById('customAlert');
    alertBox.textContent = '<?php echo htmlspecialchars($_SESSION['profile_message']); ?>';
    alertBox.classList.add('show');
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
</script>
<?php
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

if (isset($_SESSION['website_content_message_type']) && !empty($_SESSION['website_content_message'])) {
?>
<script>
    const alertBox = document.getElementById('customAlert');
    alertBox.textContent = '<?php echo addslashes(htmlspecialchars($_SESSION['website_content_message'])); ?>';
    alertBox.classList.add('show');
    <?php if ($_SESSION['website_content_message_type'] === 'error'): ?>
        alertBox.classList.add('error');
    <?php endif; ?>
    setTimeout(() => {
        alertBox.classList.remove('show');
        <?php if ($_SESSION['website_content_message_type'] === 'error'): ?>
            alertBox.classList.remove('error');
        <?php endif; ?>
    }, 5000);
</script>
<?php

    unset($_SESSION['website_content_message']);
    unset($_SESSION['website_content_message_type']);
}

?>

<script src="../js/settings.js"></script>
<script>
    document.getElementById('generateReportBtn').addEventListener('click', function() {
  window.open('feedbackreport.php', '_blank');
});

document.addEventListener('DOMContentLoaded', function() {
    const websiteContentForm = document.getElementById('websiteContentForm');
    
    if (websiteContentForm) {
        websiteContentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
           
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = new FormData(this);
            
            fetch('../api/api_update_website_content.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertBox = document.getElementById('customAlert');
                
                if (data.success) {
                    
                    alertBox.textContent = data.message;
                    alertBox.classList.add('show');
                    alertBox.style.backgroundColor = '#28a745';
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.add('show');
                    alertBox.style.backgroundColor = '#dc3545';
                }
                
                setTimeout(() => {
                    alertBox.classList.remove('show');
                    alertBox.style.backgroundColor = '';
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
                const alertBox = document.getElementById('customAlert');
                alertBox.textContent = 'An error occurred while saving. Please try again.';
                alertBox.classList.add('show');
                alertBox.style.backgroundColor = '#dc3545';
                
                setTimeout(() => {
                    alertBox.classList.remove('show');
                    alertBox.style.backgroundColor = '';
                }, 5000);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>
</body>
</html>