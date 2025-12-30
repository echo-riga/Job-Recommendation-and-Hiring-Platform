<?php
include 'database.php'; 
session_start(); 
require 'Mail/phpmailer/PHPMailerAutoload.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = $_POST['application_id'] ?? null;
    $status = $_POST['status'] ?? null;

    $allowed_statuses = [
        'Pending',
        'Initial Interview',
        'Technical Interview',
        'Job Offer',
        'Failed',
        'Job Offer Accepted',
        'Job Offer Rejected'
    ];
    if (!$applicationId || !$status) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit;
    }
    if (!in_array($status, $allowed_statuses, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status value']);
        exit;
    }

    $applicantName = '';
    $applicantEmail = '';
    $getApplicantInfoQuery = "
        SELECT u.firstname, u.lastname, u.email 
        FROM application a 
        JOIN users u ON a.userid = u.userid 
        WHERE a.applicationid = ?
    ";
    
    $stmtGetInfo = $con->prepare($getApplicantInfoQuery);
    if ($stmtGetInfo) {
        $stmtGetInfo->bind_param("i", $applicationId);
        $stmtGetInfo->execute();
        $resultGetInfo = $stmtGetInfo->get_result();
        if ($rowGetInfo = $resultGetInfo->fetch_assoc()) {
            $applicantName = htmlspecialchars($rowGetInfo['firstname'] . ' ' . $rowGetInfo['lastname']);
            $applicantEmail = $rowGetInfo['email'];
        }
        $stmtGetInfo->close();
    } else {
        error_log("Failed to prepare statement to get applicant info: " . $con->error);
    }
    $stmt = $con->prepare("UPDATE application SET status = ? WHERE applicationid = ?");
    $stmt->bind_param("si", $status, $applicationId);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        if ($status === 'Job Offer Accepted' && !empty($applicantEmail)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                $mail->Username = 'jftsystem@gmail.com'; 
                $mail->Password = 'vwhs rehv nang bxuu'; 

                $mail->setFrom('jftsystem@gmail.com', 'JOBFIT koei'); 

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to Philkoei International!';

                $emailBody = "<p>Dear " . $applicantName . ",</p>";
                $emailBody .= "<p>Good day!</p>";
                $emailBody .= "<p>Thank you for accepting our offer and joining Philkoei International! We’re excited to have you on board and look forward to working with you as part of our growing team.</p>";
                $emailBody .= "<p>Your skills and experience will surely make a great contribution, and we’re eager to see you thrive in your new role.</p>";
                $emailBody .= "<p>Please expect to receive another email soon with details about your onboarding process, including your first-day instructions and other important information.</p>";
                $emailBody .= "<p>Once again, welcome to the Philkoei family — we’re happy to have you with us!</p>";
                $emailBody .= "<p>Warm regards,</p>";
                
                $mail->Body = $emailBody;
                
                $mail->send();
            } catch (Exception $e) {
                error_log("Email could not be sent to applicant {$applicantEmail}. Mailer Error: {$mail->ErrorInfo}");
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>