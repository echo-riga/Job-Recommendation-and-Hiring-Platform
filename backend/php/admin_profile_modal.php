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

// Prepare data for display
$adminId = htmlspecialchars($adminData['adminid'] ?? 'N/A');
$adminName = htmlspecialchars($adminData['adminname'] ?? 'Administrator');
$adminEmailDisplay = htmlspecialchars($adminData['adminemail'] ?? 'N/A');
$adminStatus = htmlspecialchars($adminData['adminstatus'] ?? 'N/A');
$joinedAt = 'N/A';
if (isset($adminData['admincreated_at'])) {
    $dateTime = new DateTime($adminData['admincreated_at']);
    $joinedAt = $dateTime->format('F j, Y - h:i A');
}

// Generate initials for avatar if no image
$avatarInitials = "AD";
if (isset($adminData['adminname'])) {
    $nameParts = explode(" ", trim($adminData['adminname']));
    $avatarInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $avatarInitials .= strtoupper(substr($nameParts[1], 0, 1));
    }
}
?>


<div class="modal user-profile-modal" id="adminUserProfileModal">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>Admin Profile</h2>
            <span class="close-button" id="closeAdminUserProfileModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <?php if ($adminImageBase64): ?>
                    <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle-large" id="profileAvatarModal">
                <?php else: ?>
                    <div class="avatar-circle-large" id="profileAvatarModal"><?php echo $avatarInitials; ?></div>
                <?php endif; ?>
                <div class="profile-name" id="profileFullNameModal"><?php echo $adminName; ?></div>
                <div class="profile-email" id="profileEmailSummaryModal"><?php echo $adminEmailDisplay; ?></div>
                <p class="profile-statusni" id="profileStatusModal">Status: <?php echo $adminStatus; ?></p>
                <div class="profile-actions">
                    <button class="btn-edit-profile" id="editProfileBtn">Edit Profile</button>
                    <button class="btn-change-photo" id="changePhotoBtn" style="display: none;">Change Photo</button>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user-shield"></i></div>
                <div class="section-title">Admin Information</div>
                <div class="section-fields">
                    <div class="form-field-group">
                        <div class="form-field">
                            <label>Admin ID</label>
                            <input type="text" id="viewAdminId" value="<?php echo $adminId; ?>" readonly>
                        </div>
                        <div class="form-field">
                            <label>Full Name</label>
                            <input type="text" id="viewAdminFullName" value="<?php echo $adminName; ?>" readonly>
                        </div>
                     </div>
                    <div class="form-field">
                        <label>Email Address</label>
                        <input type="email" id="viewAdminEmail" value="<?php echo $adminEmailDisplay; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Status</label>
                        <input type="text" id="viewAdminStatus" value="<?php echo $adminStatus; ?>" readonly>
                    </div>
                    <div class="form-field">
                        <label>Joined Date</label>
                        <input type="text" id="viewAdminJoinedAt" value="<?php echo $joinedAt; ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="change-password-link">
                <a href="settings.php">Want to change password? Click here.</a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="adminProfileModalCloseBtn">Close</button>
            <button class="btn-save-profile" id="saveProfileBtn" style="display: none;">Save Changes</button>
        </div>
    </div>
</div>

<form id="imageUploadForm" method="POST" enctype="multipart/form-data" action="upload_admin_image.php" style="display: none;">
    <input type="file" name="adminImage" id="adminImageInput" accept="image/*">
    <input type="hidden" id="originalAdminImageBase64" value="<?php echo htmlspecialchars($originalAdminImageBase64); ?>">
</form>