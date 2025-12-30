<?php
session_start();
header('Content-Type: application/json');

include 'database.php'; 

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$currentAdminId = null;
$currentAdminName = 'System Admin'; 
if (isset($_SESSION['admin'])) {
    $currentAdminEmail = $_SESSION['admin'];
    $getAdminInfoQuery = "SELECT adminid, adminname FROM admin WHERE adminemail = ?";
    $stmtAdminInfo = $con->prepare($getAdminInfoQuery);
    if ($stmtAdminInfo) { 
        $stmtAdminInfo->bind_param("s", $currentAdminEmail);
        $stmtAdminInfo->execute();
        $resultAdminInfo = $stmtAdminInfo->get_result();
        if ($resultAdminInfo && $rowAdminInfo = $resultAdminInfo->fetch_assoc()) {
            $currentAdminId = $rowAdminInfo['adminid'];
            $currentAdminName = htmlspecialchars($rowAdminInfo['adminname'] !== null ? $rowAdminInfo['adminname'] : 'System Admin');
        }
        $stmtAdminInfo->close();
    }
}



if (!isset($_POST['action']) || !isset($_POST['postid'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters (action or postid).']);
    exit();
}

$postId = (int)$_POST['postid'];
$action = $_POST['action'];
$con->begin_transaction(); 

try {

    $jobRole = 'N/A';
    $getJobRoleQuery = "SELECT postjobrole FROM Jobposting WHERE postid = ?";
    $stmtJobRole = $con->prepare($getJobRoleQuery);
    if ($stmtJobRole) {
        $stmtJobRole->bind_param("i", $postId);
        $stmtJobRole->execute();
        $resultJobRole = $stmtJobRole->get_result();
        if ($rowJobRole = $resultJobRole->fetch_assoc()) {
            $jobRole = $rowJobRole['postjobrole'];
        }
        $stmtJobRole->close();
    }



    if ($action === 'close_post') {
        $newStatus = 'Closed';

        $stmt = $con->prepare("UPDATE Jobposting SET poststatus = ? WHERE postid = ?");
        $stmt->bind_param("si", $newStatus, $postId);
        
        if ($stmt->execute()) {
            $con->commit();

  
            $auditAction = 'Close Job Post';
            $auditDetails = "Closed job posting (ID: " . $postId . ", Role: " . htmlspecialchars($jobRole) . ").";
            
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            if ($stmtAudit) {
                $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $auditAction, $auditDetails);
                $stmtAudit->execute();
                $stmtAudit->close();
            } else {
                error_log("Failed to prepare audit trail statement (Close Post): " . $con->error);
            }

            $_SESSION['message'] = 'Job post successfully closed.';
            $_SESSION['message_type'] = 'success';

            echo json_encode([
                'success' => true, 
                'message' => 'Job post successfully closed.',
                'new_status' => $newStatus,
                'postid' => $postId
            ]);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
        $stmt->close();

    } elseif ($action === 'open_post_with_deadline') {
        if (!isset($_POST['postdeadline']) || empty($_POST['postdeadline'])) {
            throw new Exception('Missing or empty application deadline.');
        }
        
        $newStatus = 'Open';
        $newDeadline = $_POST['postdeadline'];
        
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $newDeadline)) {
            throw new Exception('Invalid date format.');
        }


        $stmt = $con->prepare("UPDATE Jobposting SET poststatus = ?, postdeadline = ? WHERE postid = ?");
        $stmt->bind_param("ssi", $newStatus, $newDeadline, $postId);

        if ($stmt->execute()) {
            $con->commit();
            

            $auditAction = 'Open Job Post';
            $auditDetails = "Opened job posting (ID: " . $postId . ", Role: " . htmlspecialchars($jobRole) . ") with new deadline: " . htmlspecialchars($newDeadline) . ".";
            
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            if ($stmtAudit) {
                $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $auditAction, $auditDetails);
                $stmtAudit->execute();
                $stmtAudit->close();
            } else {
                error_log("Failed to prepare audit trail statement (Open Post): " . $con->error);
            }

            $_SESSION['message'] = 'Job post successfully opened and deadline updated.';
            $_SESSION['message_type'] = 'success';
            
            $formattedDeadline = date('F j, Y', strtotime($newDeadline));

            echo json_encode([
                'success' => true, 
                'message' => 'Job post successfully opened and deadline updated.',
                'new_status' => $newStatus,
                'new_deadline_display' => $formattedDeadline,
                'postid' => $postId
            ]);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
        $stmt->close();

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }

} catch (Exception $e) {
    $con->rollback(); 
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if ($con) {
    $con->close();
}
?>