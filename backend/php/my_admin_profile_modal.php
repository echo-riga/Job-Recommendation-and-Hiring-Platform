<?php

include 'database.php'; 

if (!isset($_SESSION['admin'])) {
    exit('Unauthorized access.');
}

$adminEmail = $_SESSION['admin'];
$adminData = null;
$adminImageBase64 = null;
$originalAdminImageBase64 = null;

$stmt = $con->prepare("SELECT adminid, adminname, adminemail, adminimage, adminstatus, admincreated_at FROM admin WHERE adminemail = ?");
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 1) {
    $adminData = $res->fetch_assoc();
    if (!empty($adminData['adminimage'])) {
        $adminImageBase64 = 'data:image/jpeg;base64,' . base64_encode($adminData['adminimage']);
        $originalAdminImageBase64 = $adminImageBase64;
    }
}
$stmt->close();

$adminId = htmlspecialchars($adminData['adminid'] ?? 'N/A');
$adminName = htmlspecialchars($adminData['adminname'] ?? 'Administrator');
$adminEmailDisplay = htmlspecialchars($adminData['adminemail'] ?? 'N/A');
$adminStatus = htmlspecialchars($adminData['adminstatus'] ?? 'Active');
$joinedAt = date('F d, Y', strtotime($adminData['admincreated_at'] ?? 'now'));


function generate_initials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    return substr($initials, 0, 2);
}
$initials = generate_initials($adminName);

?>

<div class="modal" id="myprofile_adminUserProfileModal">
    <div class="modal-content profile-modal-content">
        <div class="modal-header">
            <h2>My Profile</h2>
            <span class="close-button" id="myprofile_closeAdminUserProfileModal">&times;</span>
        </div>
        <div class="modal-body profile-modal-body">
            
            <div class="profile-summary">
                <?php if ($adminImageBase64): ?>
                    <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle-large" id="myprofile_profileAvatarModal">
                <?php else: ?>
                    <div class="avatar-circle-large" id="myprofile_profileAvatarModal"><?php echo $initials; ?></div>
                <?php endif; ?>
                
                <h3 id="myprofile_profileFullNameModal"><?php echo $adminName; ?></h3>
                <p id="myprofile_profileEmailSummaryModal"><?php echo $adminEmailDisplay; ?></p>
                
                <button class="btn-change-photo" id="myprofile_changePhotoBtn" style="display: none;">Change Photo</button>
                <button class="btn-edit-profile" id="myprofile_editProfileBtn">Edit Profile</button>
            </div>
            
            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user"></i></div>
                <div class="section-title">Personal Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Admin ID</label>
                        <input type="text" id="myprofile_viewAdminId" value="<?php echo $adminId; ?>" readonly>
                    </div>
                    <div class="form-field">
                        <label for="myprofile_viewAdminFullName">Full Name</label>
                        <input type="text" id="myprofile_viewAdminFullName" value="<?php echo $adminName; ?>" readonly>
                    </div>
                    <div class="form-field">
                        <label for="myprofile_viewAdminEmail">Email Address</label>
                        <input type="email" id="myprofile_viewAdminEmail" value="<?php echo $adminEmailDisplay; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Status</label>
                        <input type="text" id="myprofile_viewAdminStatus" value="<?php echo $adminStatus; ?>" readonly>
                    </div>
                    <div class="form-field">
                        <label>Joined Date</label>
                        <input type="text" id="myprofile_viewAdminJoinedAt" value="<?php echo $joinedAt; ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="change-password-link">
                <a href="settings.php">Want to change password? Click here.</a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="myprofile_adminProfileModalCloseBtn">Close</button>
            <button class="btn-save-profile" id="myprofile_saveProfileBtn" style="display: none;">Save Changes</button>

        </div>
    </div>
</div>

<form id="myprofile_imageUploadForm" method="POST" enctype="multipart/form-data" action="upload_admin_image.php" style="display: none;">
    <input type="file" name="adminImage" id="myprofile_adminImageInput" accept="image/*">
    <input type="hidden" id="myprofile_originalAdminImageBase64" value="<?php echo htmlspecialchars($originalAdminImageBase64); ?>">
</form>