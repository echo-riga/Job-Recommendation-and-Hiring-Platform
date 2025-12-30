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

    $adminName = "Administrator";
    $avatarInitials = "AD";
    $adminImageBase64 = null; 
    $currentAdminId = null;
    $adminEmail = $_SESSION['admin'];
    $stmt = $con->prepare("SELECT adminid, adminname, adminpassword, adminimage FROM admin WHERE adminemail = ?"); // Fetch adminid
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $res = $stmt->get_result();
    $adminData = null;
    if ($res->num_rows === 1) {
        $adminData = $res->fetch_assoc();
        $currentAdminId = $adminData['adminid'];
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




    $contentData = [
        'aboutus_home' => '',
        'who_we_are' => '',
        'mission' => '',
        'vision' => '',
        'quality_policy' => '',
        'banner_base64' => '',
        'group_photo_base64' => ''
    ];

    $stmtContent = $con->prepare("SELECT aboutus_home, who_we_are, mission, vision, quality_policy, banner, group_photo FROM website_content WHERE content_id = 1");
    if ($stmtContent) {
        $stmtContent->execute();
        $resContent = $stmtContent->get_result();
        if ($resContent->num_rows === 1) {
            $data = $resContent->fetch_assoc();
            $contentData['aboutus_home'] = $data['aboutus_home'];
            $contentData['who_we_are'] = $data['who_we_are'];
            $contentData['mission'] = $data['mission'];
            $contentData['vision'] = $data['vision'];
            $contentData['quality_policy'] = $data['quality_policy'];

            if (!empty($data['banner'])) {
                $contentData['banner_base64'] = 'data:image/jpeg;base64,' . base64_encode($data['banner']);
            }
            if (!empty($data['group_photo'])) {
                $contentData['group_photo_base64'] = 'data:image/jpeg;base64,' . base64_encode($data['group_photo']);
            }
        }
        $stmtContent->close();
    }
    extract($contentData); 



    $password_message = '';
    $password_message_type = ''; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password_submit'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $password_message = 'All password fields are required.';
            $password_message_type = 'error';
        } elseif ($newPassword !== $confirmNewPassword) {
            $password_message = 'New password and confirm new password do not match.';
            $password_message_type = 'error';
        } elseif (strlen($newPassword) < 8 ||
                !preg_match("/[A-Z]/", $newPassword) ||
                !preg_match("/[a-z]/", $newPassword) ||
                !preg_match("/[0-9]/", $newPassword) ||
                !preg_match("/[^A-Za-z0-9]/", $newPassword)) { 
            $password_message = 'New password must meet the required password format';
            $password_message_type = 'error';
        } else {
    
            if ($adminData && password_verify($currentPassword, $adminData['adminpassword'])) {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

         
                $updateStmt = $con->prepare("UPDATE admin SET adminpassword = ? WHERE adminemail = ?");
                $updateStmt->bind_param("ss", $hashedNewPassword, $adminEmail); 

                if ($updateStmt->execute()) {
                    $_SESSION['password_message'] = 'Password updated successfully!';
                    $_SESSION['password_message_type'] = 'success';

                    // --- Audit Trail Entry for Password Change ---
                    $action = 'Change Password';
                    $details = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") changed their password.";
                    $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
                    $stmtAudit = $con->prepare($insertAuditQuery);
                    $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $action, $details);
                    $stmtAudit->execute();
                    $stmtAudit->close();
                    // --- End Audit Trail Entry ---

                    header('Location: settings.php');
                    exit();
                } else {
                    $password_message = 'Error updating password: ' . $updateStmt->error;
                    $password_message_type = 'error';
                }
                $updateStmt->close();
            } else {
                $password_message = 'Current password is incorrect.';
                $password_message_type = 'error';
            }
        }
    }

    $site_identity_message = '';
    $site_identity_message_type = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_identity_submit'])) {
        $newSiteName = $_POST['site_name'] ?? '';
        $updateSuccessful = false;
        $auditAction = '';
        $auditDetails = '';

        // Check if a new file was uploaded
        if (isset($_FILES['upload_logo']) && $_FILES['upload_logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['upload_logo']['tmp_name'];
            $fileContent = file_get_contents($fileTmpPath);

            // Update both name and image
            $updateStmt = $con->prepare("UPDATE systemname SET sysname = ?, sysimage = ? WHERE sysid = 1");
            $updateStmt->bind_param("sb", $newSiteName, $fileContent);
            // Special handling for BLOB: send_long_data
            $updateStmt->send_long_data(1, $fileContent);

            if ($updateStmt->execute()) {
                $updateSuccessful = true;
                $auditAction = 'Update Site Identity';
                $auditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") updated site name to '" . htmlspecialchars($newSiteName) . "' and uploaded a new logo.";
            } else {
                $site_identity_message = 'Error updating site identity: ' . $updateStmt->error;
                $site_identity_message_type = 'error';
            }
            $updateStmt->close();

        } elseif (!empty($newSiteName)) { 
            $updateStmt = $con->prepare("UPDATE systemname SET sysname = ? WHERE sysid = 1");
            $updateStmt->bind_param("s", $newSiteName);
            if ($updateStmt->execute()) {
                $updateSuccessful = true;
                $auditAction = 'Update Site Name';
                $auditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") updated site name to '" . htmlspecialchars($newSiteName) . "'.";
            } else {
                $site_identity_message = 'Error updating site name: ' . $updateStmt->error;
                $site_identity_message_type = 'error';
            }
            $updateStmt->close();
        } else {
            $site_identity_message = 'No changes to save.';
            $site_identity_message_type = 'error';
        }

        if ($updateSuccessful) {
            $_SESSION['site_identity_message'] = 'Site identity updated successfully!';
            $_SESSION['site_identity_message_type'] = 'success';

            // --- Audit Trail Entry for Site Identity Update ---
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $auditAction, $auditDetails);
            $stmtAudit->execute();
            $stmtAudit->close();
            // --- End Audit Trail Entry ---

            header('Location: settings.php'); 
            exit();
        }
    }

        // --- Handle Website Content Form Submission ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['website_content_submit'])) {
        
        $aboutus_home = $_POST['aboutus_home'] ?? '';
        $who_we_are = $_POST['who_we_are'] ?? '';
        $mission = $_POST['mission'] ?? '';
        $vision = $_POST['vision'] ?? '';
        $quality_policy = $_POST['quality_policy'] ?? '';


        $bannerContent = null;
        $groupPhotoContent = null;

        $query = "UPDATE website_content SET 
                    aboutus_home = ?, 
                    who_we_are = ?, 
                    mission = ?, 
                    vision = ?, 
                    quality_policy = ?";
        
        $types = "sssss";
        $params = [
            $aboutus_home, 
            $who_we_are, 
            $mission, 
            $vision, 
            $quality_policy
        ];


        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $bannerContent = file_get_contents($_FILES['banner']['tmp_name']);
            $query .= ", banner = ?";
            $types .= "b";
            $params[] = &$bannerContent;
        }

      
        if (isset($_FILES['group_photo']) && $_FILES['group_photo']['error'] === UPLOAD_ERR_OK) {
            $groupPhotoContent = file_get_contents($_FILES['group_photo']['tmp_name']);
            $query .= ", group_photo = ?";
            $types .= "b";
            $params[] = &$groupPhotoContent; 
        }

        $query .= " WHERE content_id = 1";

        $updateStmt = $con->prepare($query);

        $updateStmt->bind_param($types, ...$params);

        $blobIndex = 0;
        if ($bannerContent !== null) {
            $updateStmt->send_long_data(substr_count($types, 's') + $blobIndex, $bannerContent);
            $blobIndex++;
        }
        if ($groupPhotoContent !== null) {
            $updateStmt->send_long_data(substr_count($types, 's') + $blobIndex, $groupPhotoContent);
        }

        if ($updateStmt->execute()) {
            $_SESSION['website_content_message'] = 'Website content updated successfully!';
            $_SESSION['website_content_message_type'] = 'success';

            // --- Audit Trail Entry ---
            $auditAction = 'Update Website Content';
            $auditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") updated the website content.";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $auditAction, $auditDetails);
            $stmtAudit->execute();
            $stmtAudit->close();
            // --- End Audit Trail Entry ---
        } else {
            $_SESSION['website_content_message'] = 'Error updating website content: ' . $updateStmt->error;
            $_SESSION['website_content_message_type'] = 'error';
        }
        $updateStmt->close();

        header('Location: settings.php#website-content');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_action'])) {
        $conid = $_POST['conid'] ?? null;
        $action = $_POST['feedback_action'];
        $feedbackAuditDetails = ''; 
        $feedbackAuditAction = ''; 

        if ($conid) {
            if ($action === 'resolve' || $action === 'pending' || $action === 'closed') {
                $newStatus = '';
                if ($action === 'resolve') {
                    $newStatus = 'Resolved';
                } elseif ($action === 'pending') {
                    $newStatus = 'Pending';
                } elseif ($action === 'closed') {
                    $newStatus = 'Closed';
                }

                $updateStmt = $con->prepare("UPDATE contactus SET constatus = ? WHERE conid = ?");
                $updateStmt->bind_param("si", $newStatus, $conid);
                if ($updateStmt->execute()) {
                    $_SESSION['feedback_message'] = 'Feedback status updated to ' . htmlspecialchars($newStatus) . ' successfully!';
                    $_SESSION['feedback_message_type'] = 'success';

                    // --- Audit Trail Entry for Feedback Status Update ---
                    $feedbackAuditAction = 'Update Feedback Status';
                    $feedbackAuditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") updated feedback (ID: " . htmlspecialchars($conid) . ") status to '" . htmlspecialchars($newStatus) . "'.";
                    $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
                    $stmtAudit = $con->prepare($insertAuditQuery);
                    $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $feedbackAuditAction, $feedbackAuditDetails);
                    $stmtAudit->execute();
                    $stmtAudit->close();
                    // --- End Audit Trail Entry ---

                } else {
                    $_SESSION['feedback_message'] = 'Error updating feedback status: ' . $updateStmt->error;
                    $_SESSION['feedback_message_type'] = 'error';
                }
                $updateStmt->close();
            } elseif ($action === 'delete') {
                $deleteStmt = $con->prepare("DELETE FROM contactus WHERE conid = ?");
                $deleteStmt->bind_param("i", $conid);
                if ($deleteStmt->execute()) {
                    $_SESSION['feedback_message'] = 'Feedback deleted successfully!';
                    $_SESSION['feedback_message_type'] = 'success';

                    // --- Audit Trail Entry for Feedback Deletion ---
                    $feedbackAuditAction = 'Delete Feedback';
                    $feedbackAuditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") deleted feedback (ID: " . htmlspecialchars($conid) . ").";
                    $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
                    $stmtAudit = $con->prepare($insertAuditQuery);
                    $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $feedbackAuditAction, $feedbackAuditDetails);
                    $stmtAudit->execute();
                    $stmtAudit->close();
                    // --- End Audit Trail Entry ---

                } else {
                    $_SESSION['feedback_message'] = 'Error deleting feedback: ' . $deleteStmt->error;
                    $_SESSION['feedback_message_type'] = 'error';
                }
                $deleteStmt->close();
            }
        }
        header('Location: settings.php?section=feedback-content'); 
        exit();
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply_email'])) {
    header('Content-Type: application/json');
    
    try {
        $phpmailerPath = 'Mail/phpmailer/PHPMailerAutoload.php';
        if (!file_exists($phpmailerPath)) {
            throw new Exception('PHPMailer library not found');
        }
        
        require $phpmailerPath;

        $recipientEmail = $_POST['reply_recipient_email'] ?? '';
        $subject = $_POST['reply_subject'] ?? '';
        $message = $_POST['reply_message'] ?? '';
        $conidForReply = $_POST['conid_for_reply'] ?? null;


        if (empty($recipientEmail) || empty($subject) || empty($message)) {
            throw new Exception('All reply email fields are required.');
        }

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address format.');
        }

        $mail = new PHPMailer(true); 
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'jftsystem@gmail.com';
        $mail->Password = 'vwhs rehv nang bxuu';
        
       
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom('jftsystem@gmail.com', 'JOBFIT ADMINISTRATOR');
        $mail->addAddress($recipientEmail);
        $mail->addReplyTo('jftsystem@gmail.com', 'JOBFIT ADMINISTRATOR');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message); 

        if ($mail->send()) {
            if ($conidForReply) {
                $updateStatusStmt = $con->prepare("UPDATE contactus SET constatus = 'Replied' WHERE conid = ? AND constatus != 'Resolved'");
                $updateStatusStmt->bind_param("i", $conidForReply);
                $updateStatusStmt->execute();
                $updateStatusStmt->close();
            }

            $_SESSION['feedback_message'] = 'Reply email sent successfully!';
            $_SESSION['feedback_message_type'] = 'success';

            $auditAction = 'Send Reply Email';
            $auditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") sent a reply email to " . htmlspecialchars($recipientEmail) . " for feedback ID: " . htmlspecialchars($conidForReply) . ".";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $auditAction, $auditDetails);
            $stmtAudit->execute();
            $stmtAudit->close();

            echo json_encode([
                'status' => 'success', 
                'message' => 'Reply email sent successfully!'
            ]);
            
        } else {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to send email: ' . $e->getMessage()
        ]);
    }
    exit();
}

?>