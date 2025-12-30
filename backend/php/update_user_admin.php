<?php
include 'database.php';
session_start();

require 'Mail/phpmailer/PHPMailerAutoload.php'; 

if (!isset($_SESSION['admin'])) {
    $_SESSION['message'] = 'Unauthorized access.';
    $_SESSION['message_type'] = 'error';
    header("Location: admin_login.php");
    exit();
}

$currentAdminId = null;
$currentAdminName = 'System Admin'; 
if (isset($_SESSION['admin'])) {
    $currentAdminEmail = $_SESSION['admin'];
    $getAdminInfoQuery = "SELECT adminid, adminname FROM admin WHERE adminemail = ?";
    $stmtAdminInfo = mysqli_prepare($con, $getAdminInfoQuery);
    if ($stmtAdminInfo) { 
        mysqli_stmt_bind_param($stmtAdminInfo, "s", $currentAdminEmail);
        mysqli_stmt_execute($stmtAdminInfo);
        $resultAdminInfo = mysqli_stmt_get_result($stmtAdminInfo);
        if ($resultAdminInfo && $rowAdminInfo = mysqli_fetch_assoc($resultAdminInfo)) {
            $adminNameFromDB = $rowAdminInfo['adminname'] !== null ? $rowAdminInfo['adminname'] : 'System Admin';
            $currentAdminId = $rowAdminInfo['adminid'];
            $currentAdminName = 'Admin ' . $adminNameFromDB;
        }
        mysqli_stmt_close($stmtAdminInfo);
    } else {
        error_log("Failed to prepare statement to get admin info in update_user_admin.php: " . mysqli_error($con));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['updateAdminStatusConfirmed'])) { 
        $adminId = (int)$_POST['adminId'];
        $newStatus = strtolower(mysqli_real_escape_string($con, $_POST['newStatus']));

        $adminToUpdateName = '';
        $adminToUpdateEmail = '';
        $getAdminInfoQuery = "SELECT adminname, adminemail FROM admin WHERE adminid = ?";
        $stmtGetInfo = mysqli_prepare($con, $getAdminInfoQuery);
        mysqli_stmt_bind_param($stmtGetInfo, "i", $adminId);
        mysqli_stmt_execute($stmtGetInfo);
        $resultGetInfo = mysqli_stmt_get_result($stmtGetInfo);
        if ($rowGetInfo = mysqli_fetch_assoc($resultGetInfo)) {
            $adminToUpdateName = $rowGetInfo['adminname'];
            $adminToUpdateEmail = $rowGetInfo['adminemail'];
        }
        mysqli_stmt_close($stmtGetInfo);

        $updateQuery = "UPDATE admin SET adminstatus = ? WHERE adminid = ?";
        $stmt = mysqli_prepare($con, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $adminId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Admin status updated to ' . $newStatus . ' successfully!';
            $_SESSION['message_type'] = 'success';

     
            $action = 'Update Admin Status';
            $details = "Updated status of admin '" . $adminToUpdateName . "' (ID: " . $adminId . ") to '" . $newStatus . "'";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
            mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
            mysqli_stmt_execute($stmtAudit);
            mysqli_stmt_close($stmtAudit);
           

            if (!empty($adminToUpdateEmail)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'jftsystem@gmail.com'; 
                    $mail->Password = 'vwhs rehv nang bxuu'; 

                    $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
                    $mail->addAddress($adminToUpdateEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your JOBFIT Admin Account Status Updated';
                    $emailBody = "<p>Dear " . htmlspecialchars($adminToUpdateName) . ",</p>";
                    if ($newStatus === 'active') {
                        $emailBody .= "<p>Great news! Your administrator account on the JOBFIT Admin Panel has been activated and is now <strong>" . htmlspecialchars($newStatus) . "</strong> "  . htmlspecialchars($currentAdminName) . ". You can now access all administrator functionalities.</p>";
                    } else {
                        $emailBody .= "<p>This is to inform you that your administrator account on the JOBFIT Admin Panel has been updated to <strong>" . htmlspecialchars($newStatus) . "</strong> "  . htmlspecialchars($currentAdminName) . ". Due to this change, your account is temporarily unable to access the admin panel. If you believe this is an error or have questions, please contact the system administrator immediately.</p>";
                    }
                    $emailBody .= "<p>Thank you,<br>The JOBFIT Team</p>";
                    $mail->Body = $emailBody;
                    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to admin {$adminToUpdateEmail}. Mailer Error: {$mail->ErrorInfo}");
                }
            }

        } else {
            $_SESSION['message'] = 'Error updating admin status: ' . mysqli_error($con);
            $_SESSION['message_type'] = 'error';
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['updateUserStatusConfirmed'])) { 
        $userId = (int)$_POST['userId'];
        $newStatus = strtolower(mysqli_real_escape_string($con, $_POST['newStatus']));

        $userToUpdateName = '';
        $userToUpdateEmail = '';
        $getUserInfoQuery = "SELECT firstname, lastname, email FROM users WHERE userid = ?"; 
        $stmtGetInfo = mysqli_prepare($con, $getUserInfoQuery);
        mysqli_stmt_bind_param($stmtGetInfo, "i", $userId);
        mysqli_stmt_execute($stmtGetInfo);
        $resultGetInfo = mysqli_stmt_get_result($stmtGetInfo);
        if ($rowGetInfo = mysqli_fetch_assoc($resultGetInfo)) {
            $userToUpdateName = $rowGetInfo['firstname'] . ' ' . $rowGetInfo['lastname'];
            $userToUpdateEmail = $rowGetInfo['email'];
        }
        mysqli_stmt_close($stmtGetInfo);


        $updateQuery = "UPDATE users SET status = ? WHERE userid = ?";
        $stmt = mysqli_prepare($con, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $userId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'User status updated to ' . $newStatus . ' successfully!';
            $_SESSION['message_type'] = 'success';

            $action = 'Update User Status';
            $details = "Updated status of user '" . $userToUpdateName . "' (ID: " . $userId . ") to '" . $newStatus . "'";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
            mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
            mysqli_stmt_execute($stmtAudit);
            mysqli_stmt_close($stmtAudit);

            if (!empty($userToUpdateEmail)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'jftsystem@gmail.com'; 
                    $mail->Password = 'vwhs rehv nang bxuu'; 

                    $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
                    $mail->addAddress($userToUpdateEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your JOBFIT Account Status Updated';
                    $emailBody = "<p>Dear " . htmlspecialchars($userToUpdateName) . ",</p>";
                    if ($newStatus === 'active') {
                        $emailBody .= "<p>Great news! Your account on JOBFIT has been activated and is now <strong>" . htmlspecialchars($newStatus) . "</strong>.</p> <p> You can now fully access the JOBFIT website.</p> <p>Best Regards,</p>";
                    } else {
                        $emailBody .= "<p>This is to inform you that your account on JOBFIT has been updated <strong>" . htmlspecialchars($newStatus) . "</strong>.</p> <p> Due to this change, your account is temporarily unable to access the JOBFIT website. This action might be due to a violation of our terms of service.</p> <p> If you believe this is an error or have questions, please contact our support team immediately.</p> <p>Best Regards,</p>";
                    }
                    $emailBody .= "<p>Thank you,<br>The JOBFIT Team</p>";
                    $mail->Body = $emailBody;

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to user {$userToUpdateEmail}. Mailer Error: {$mail->ErrorInfo}");
                }
            }

        } else {
            $_SESSION['message'] = 'Error updating user status: ' . mysqli_error($con);
            $_SESSION['message_type'] = 'error';
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['updateAdminInfoConfirmed'])) { 
        $adminId = (int)$_POST['editAdminId'];
        $adminNameUpdate = mysqli_real_escape_string($con, $_POST['editAdminName']);
        $adminEmailUpdate = mysqli_real_escape_string($con, $_POST['editAdminEmail']);
        $adminPasswordUpdate = $_POST['editAdminPassword'];

        $originalAdminName = '';
        $originalAdminEmail = '';
        $getOriginalAdminInfoQuery = "SELECT adminname, adminemail FROM admin WHERE adminid = ?";
        $stmtGetOriginal = mysqli_prepare($con, $getOriginalAdminInfoQuery);
        mysqli_stmt_bind_param($stmtGetOriginal, "i", $adminId);
        mysqli_stmt_execute($stmtGetOriginal);
        $resultGetOriginal = mysqli_stmt_get_result($stmtGetOriginal);
        if ($rowGetOriginal = mysqli_fetch_assoc($resultGetOriginal)) {
            $originalAdminName = $rowGetOriginal['adminname'];
            $originalAdminEmail = $rowGetOriginal['adminemail'];
        }
        mysqli_stmt_close($stmtGetOriginal);


        $updateQuery = "UPDATE admin SET adminname = ?, adminemail = ?";
        $params = "ss";
        $bindValues = [$adminNameUpdate, $adminEmailUpdate];
        $detailsChanges = [];
        $passwordChanged = false;

        if ($adminNameUpdate !== $originalAdminName) {
            $detailsChanges[] = "Name changed from '" . $originalAdminName . "' to '" . $adminNameUpdate . "'";
        }
        if ($adminEmailUpdate !== $originalAdminEmail) {
            $detailsChanges[] = "Email changed from '" . $originalAdminEmail . "' to '" . $adminEmailUpdate . "'";
        }

        if (!empty($adminPasswordUpdate)) {
            $hashedPassword = password_hash($adminPasswordUpdate, PASSWORD_DEFAULT);
            $updateQuery .= ", adminpassword = ?";
            $params .= "s";
            $bindValues[] = $hashedPassword;
            $detailsChanges[] = "Password updated";
            $passwordChanged = true;
        }
        $updateQuery .= " WHERE adminid = ?";
        $params .= "i";
        $bindValues[] = $adminId;

        $stmt = mysqli_prepare($con, $updateQuery);
        mysqli_stmt_bind_param($stmt, $params, ...$bindValues);

        if (mysqli_stmt_execute($stmt)) {
            if (isset($_SESSION['admin_id_for_update_check']) && $adminId == $_SESSION['admin_id_for_update_check']) {
                $_SESSION['admin'] = $adminEmailUpdate; 
            }
            $_SESSION['message'] = 'Admin information updated successfully!';
            $_SESSION['message_type'] = 'success';

            $action = 'Update Admin Info';
            $details = "Updated admin '" . $originalAdminName . "' (ID: " . $adminId . "). ";
            if (empty($detailsChanges)) {
                $details .= "No significant changes detected.";
            } else {
                $details .= implode(", ", $detailsChanges) . ".";
            }
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
            mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
            mysqli_stmt_execute($stmtAudit);
            mysqli_stmt_close($stmtAudit);
            
            $recipientEmail = $adminEmailUpdate; 
            if (!empty($recipientEmail)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'jftsystem@gmail.com'; 
                    $mail->Password = 'vwhs rehv nang bxuu'; 

                    $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
                    $mail->addAddress($recipientEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your JOBFIT Admin Account Information Updated';
                    $emailBody = "<p>Dear " . htmlspecialchars($adminNameUpdate) . ",</p>";
                    $emailBody .= "<p>This is to inform you that your administrator account details on the JOBFIT Admin Panel have been updated by " . htmlspecialchars($currentAdminName) . ".</p>";
                    $emailBody .= "<ul>";
                    if ($adminNameUpdate !== $originalAdminName) {
                        $emailBody .= "<li><strong>Name:</strong> Changed from '" . htmlspecialchars($originalAdminName) . "' to '" . htmlspecialchars($adminNameUpdate) . "'</li>";
                    }
                    if ($adminEmailUpdate !== $originalAdminEmail) {
                        $emailBody .= "<li><strong>Email:</strong> Changed from '" . htmlspecialchars($originalAdminEmail) . "' to '" . htmlspecialchars($adminEmailUpdate) . "'</li>";
                    }
                    if ($passwordChanged) {
                        $emailBody .= "<li><strong>Password:</strong> Has been updated.</li>";
                        $emailBody .= "<p>For security reasons, the new password is not included in this email. If you did not make this change, please reset your password immediately or contact the system administrator.</p>";
                    }
                    if (empty($detailsChanges)) {
                         $emailBody .= "<li>No significant changes to Name, Email, or Password were detected, but this notification confirms an update attempt.</li>";
                    }
                    $emailBody .= "</ul>";
                    $emailBody .= "<p>If you did not request these changes, please contact the system administrator immediately.</p>";
                    $emailBody .= "<p>Thank you,<br>The JOBFIT Team</p>";
                    $mail->Body = $emailBody;
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to admin {$recipientEmail}. Mailer Error: {$mail->ErrorInfo}");
                }
            }

        } else {
            $_SESSION['message'] = 'Error updating admin information: ' . mysqli_error($con);
            $_SESSION['message_type'] = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

header("Location: users.php");
exit;
?>