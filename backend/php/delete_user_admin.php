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
            $currentAdminId = $rowAdminInfo['adminid'];
            $currentAdminName = $rowAdminInfo['adminname'] !== null ? 'Admin ' . $rowAdminInfo['adminname'] : 'System Admin';
        }
        mysqli_stmt_close($stmtAdminInfo);
    } else {
        error_log("Failed to prepare statement to get admin info in delete_user_admin.php: " . mysqli_error($con));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deleteAdmin'])) {
        $id = (int)$_POST['adminId'];
        $currentAdminEmail = $_SESSION['admin'];

        $adminToDeleteEmail = '';
        $adminToDeleteName = '';
        $stmt_check = mysqli_prepare($con, "SELECT adminemail, adminname FROM admin WHERE adminid = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $id);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $adminToDelete = mysqli_fetch_assoc($res_check);
        mysqli_stmt_close($stmt_check);

        if ($adminToDelete) {
            $adminToDeleteEmail = $adminToDelete['adminemail'];
            $adminToDeleteName = $adminToDelete['adminname'];
        }

        if ($adminToDelete && $adminToDeleteEmail === $currentAdminEmail) {
            $_SESSION['message'] = 'You cannot delete your own admin account!';
            $_SESSION['message_type'] = 'error';
        } else {
            $deleteQuery = "DELETE FROM admin WHERE adminid = ?";
            $stmt = mysqli_prepare($con, $deleteQuery);
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Admin Deleted Successfully!';
                $_SESSION['message_type'] = 'success';

                $action = 'Delete Admin';
                $details = "Deleted admin: " . $adminToDeleteName . " (ID: " . $id . ", Email: " . $adminToDeleteEmail . ")";
                $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
                $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
                mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
                mysqli_stmt_execute($stmtAudit);
                mysqli_stmt_close($stmtAudit);
                if (!empty($adminToDeleteEmail)) {
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
                        $mail->addAddress($adminToDeleteEmail);

                        $mail->isHTML(true);
                        $mail->Subject = 'JOBFIT Admin Account Deleted';
                        $mail->Body    = "
                            <p>Dear " . htmlspecialchars($adminToDeleteName) . ",</p>
                            <p>We regret to inform you that your administrator account (Email: " . htmlspecialchars($adminToDeleteEmail) . ") on the JOBFIT Admin Panel has been removed by " . htmlspecialchars($currentAdminName) . ".</p>
                            <p>This action means you will no longer have access to the administrator functionalities. We apologize for any inconvenience this may cause.</p>
                            <p>If you believe this was done in error or have any questions, please do not hesitate to contact the system administrator for clarification.</p>
                            <p>Thank you for your understanding,<br>
                            The JOBFIT Team</p>
                        ";
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Email could not be sent to deleted admin {$adminToDeleteEmail}. Mailer Error: {$mail->ErrorInfo}");
                    }
                }

            } else {
                $_SESSION['message'] = 'Error deleting admin: ' . mysqli_error($con);
                $_SESSION['message_type'] = 'error';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['deleteUser'])) {
        $id = (int)$_POST['userId'];

        $userToDeleteName = '';
        $userToDeleteEmail = '';
        $getUserInfoQuery = "SELECT firstname, lastname, email FROM users WHERE userid = ?"; 
        $stmtGetInfo = mysqli_prepare($con, $getUserInfoQuery);
        mysqli_stmt_bind_param($stmtGetInfo, "i", $id);
        mysqli_stmt_execute($stmtGetInfo);
        $resultGetInfo = mysqli_stmt_get_result($stmtGetInfo);
        if ($rowGetInfo = mysqli_fetch_assoc($resultGetInfo)) {
            $userToDeleteName = $rowGetInfo['firstname'] . ' ' . $rowGetInfo['lastname'];
            $userToDeleteEmail = $rowGetInfo['email']; 
        }
        mysqli_stmt_close($stmtGetInfo);

        $deleteQuery = "DELETE FROM users WHERE userid = ?";
        $stmt = mysqli_prepare($con, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'User Deleted Successfully!';
            $_SESSION['message_type'] = 'success';

            $action = 'Delete User';
            $details = "Deleted user: " . $userToDeleteName . " (ID: " . $id . ", Email: " . $userToDeleteEmail . ")";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
            mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
            mysqli_stmt_execute($stmtAudit);
            mysqli_stmt_close($stmtAudit);
            
            if (!empty($userToDeleteEmail)) {
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
                    $mail->addAddress($userToDeleteEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'JOBFIT Account Deleted';
                    $mail->Body    = "
                        <p>Dear " . htmlspecialchars($userToDeleteName) . ",</p>
                        <p>We regret to inform you that your account (Email: " . htmlspecialchars($userToDeleteEmail) . ") on JOBFIT has been removed by " . htmlspecialchars($currentAdminName) . ".</p>
                        <p>This action means you will no longer have access to the JOBFIT platform. We apologize for any inconvenience this may cause.</p>
                        <p>If you believe this was done in error or have any questions, please do not hesitate to contact our support team for clarification.</p>
                        <p>Thank you for your understanding,<br>
                        The JOBFIT Team</p>
                    ";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to deleted user {$userToDeleteEmail}. Mailer Error: {$mail->ErrorInfo}");
                }
            }

        } else {
            $_SESSION['message'] = 'Error deleting user: ' . mysqli_error($con);
            $_SESSION['message_type'] = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

header("Location: users.php");
exit;
?>