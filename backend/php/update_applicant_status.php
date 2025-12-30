<?php
session_start();
include 'database.php'; 

require 'Mail/phpmailer/PHPMailerAutoload.php'; 

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid Request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appID = filter_input(INPUT_POST, 'appid', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'newStatus', FILTER_SANITIZE_STRING);
    $rejectionReason = filter_input(INPUT_POST, 'rejectionReason', FILTER_SANITIZE_STRING);

    $customSubject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $customMessage = isset($_POST['customMessage']) ? $_POST['customMessage'] : '';

    if (!isset($_SESSION['admin'])) {
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit;
    }

    $currentAdminId = null; 
    $currentAdminName = 'Jobfit Koei - Philkoei Internaitional, Inc.';
    if (isset($_SESSION['admin'])) {
        $currentAdminEmail = $_SESSION['admin'];
        $getAdminInfoQuery = "SELECT adminid, adminname FROM admin WHERE adminemail = ?";
        $stmtAdminInfo = $con->prepare($getAdminInfoQuery);
        if ($stmtAdminInfo) {
            $stmtAdminInfo->bind_param("s", $currentAdminEmail);
            $stmtAdminInfo->execute();
            $resultAdminInfo = $stmtAdminInfo->get_result();
            if ($rowAdminInfo = $resultAdminInfo->fetch_assoc()) {
                $currentAdminId = $rowAdminInfo['adminid'];
                $currentAdminName = htmlspecialchars($rowAdminInfo['adminname']);
            }
            $stmtAdminInfo->close();
        }
    }

    $applicantInfo = null;
    $infoQuery = "
        SELECT 
            u.userid, u.email, 
            u.firstname, 
            u.lastname, 
            j.postjobrole
        FROM application a
        INNER JOIN users u ON a.userid = u.userid
        INNER JOIN jobposting j ON a.postid = j.postid
        WHERE a.applicationid = ?";
        
    $stmtInfo = $con->prepare($infoQuery);
    $stmtInfo->bind_param("i", $appID);
    $stmtInfo->execute();
    $resultInfo = $stmtInfo->get_result();
    if ($resultInfo->num_rows === 1) {
        $applicantInfo = $resultInfo->fetch_assoc();
    }
    $stmtInfo->close();

    if (!$applicantInfo) {
         $response['message'] = 'Applicant or Job information not found.';
         echo json_encode($response);
         $con->close();
         exit;
    }
    
    $applicantUserID = $applicantInfo['userid'];
    $applicantEmail = $applicantInfo['email'];
    $applicantFullName = $applicantInfo['firstname'] . ' ' . $applicantInfo['lastname'];
    $jobRole = $applicantInfo['postjobrole'];

    $valid_statuses = ['job offer', 'failed', 'initial interview', 'technical interview'];

    if ($appID && $newStatus && in_array(strtolower($newStatus), $valid_statuses)) {
        

        $stmt = $con->prepare("UPDATE application SET status = ? WHERE applicationid = ?");
        $stmt->bind_param("si", $newStatus, $appID);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Application status updated successfully.', 'newStatus' => $newStatus];
            
            $_SESSION['message'] = 'Application status updated successfully.';
            $_SESSION['message_type'] = 'success';


            $action = 'Update Applicant Status';
            $details = "Updated status for application ID '{$appID}' (User: '{$applicantFullName}', User ID: '{$applicantUserID}') for the job '{$jobRole}' to '{$newStatus}'.";
            

            if (strtolower($newStatus) === 'failed' && !empty($rejectionReason)) {
                $details .= " Rejection Reason: " . htmlspecialchars($rejectionReason);
            }

            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);

            if ($stmtAudit) {
                $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $action, $details);
                $stmtAudit->execute();
                $stmtAudit->close();
            } else {
                 error_log("Failed to prepare audit trail statement in update_applicant_status.php: " . $con->error);
            }



   
            if (!empty($applicantEmail)) {
                $mail = new PHPMailer(true);
                try {
         
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'jftsystem@gmail.com';
                    $mail->Password = 'vwhs rehv nang bxuu'; 

                    
                    $mail->setFrom('jftsystem@gmail.com', 'Jobfit Koei - Philkoei Internaitional, Inc.');
                    $mail->addAddress($applicantEmail);
                    $mail->isHTML(true);

                    $status_lower = strtolower($newStatus);

                    if ($status_lower === 'job offer' || $status_lower === 'initial interview' || $status_lower === 'technical interview') {
                
                        $mail->Subject = !empty($customSubject) ? $customSubject : 'Your Application Status Update';
                        

                        $mail->Body = !empty($customMessage) ? $customMessage : 'Your application for ' . htmlspecialchars($jobRole) . ' has been updated. Please contact us for the next steps.'; 
                        
                    } elseif ($status_lower === 'failed') {
                
                        $mail->Subject = 'Update on your Application for ' . htmlspecialchars($jobRole);
                        $emailBody = "
                                        <p>Dear {$applicantFullName},</p>
                                        <p>Good day!</p>
                                        <p>Thank you so much for taking the time to meet with us and for your interest in joining Philkoei International. It was a pleasure getting to know you and learning more about your background, experiences, and achievements.</p>
                                        <p>After careful consideration, we’ve decided to move forward with other candidates for the position at this time. Please know that this decision was not a reflection of your skills or accomplishments, but rather about finding the best fit for the specific requirements of the role.</p>
                                        <p>The reason for the decision is: <strong>" . htmlspecialchars($rejectionReason) . "</strong></p>
                                        <p>We truly appreciate the effort you put into your application and interview, and we’re confident that your talents will open doors to great opportunities ahead.</p>
                                        <p>Thank you once again for your time and interest in our company. We wish you all the best in your future endeavors!</p>
                                        <p>Warm regards,</p>
                                        <p><strong>The JOBFIT System Administration Team</strong><br>in partnership with Philkoei International, Inc.</p>
                                    ";
                        $mail->Body = $emailBody;
                    }

                    $mail->send();

                } catch (Exception $e) {
                    error_log("Email could not be sent to applicant {$applicantEmail}. Mailer Error: {$mail->ErrorInfo}");
                    $response['message'] .= ' (Note: Email failed to send, but status updated.)';
                    $_SESSION['message'] = 'Application status updated successfully (Note: Email failed to send).';
                    $_SESSION['message_type'] = 'success';
                }
            }

        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $con->error];
        }
        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Invalid Application ID or Status provided.'];
    }
}

echo json_encode($response);
$con->close();
?>