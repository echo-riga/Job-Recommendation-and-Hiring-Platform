<?php
session_start();
include 'database.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    http_response_code(401); 
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if (!isset($_POST['postid']) || empty($_POST['postid'])) {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Invalid or missing Post ID.']);
    exit();
}

$postid = $_POST['postid'];

try {
    // --- Retrieve Current Admin Info for Audit ---
    $currentAdminId = null;
    $currentAdminName = 'System Admin'; 
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
    // --- END Admin Info ---
    
    $jobRole = 'N/A';
    $getJobRoleQuery = "SELECT postjobrole FROM Jobposting WHERE postid = ?";
    $stmtJobRole = $con->prepare($getJobRoleQuery);
    if ($stmtJobRole) {
        $stmtJobRole->bind_param("i", $postid);
        $stmtJobRole->execute();
        $resultJobRole = $stmtJobRole->get_result();
        if ($rowJobRole = $resultJobRole->fetch_assoc()) {
            $jobRole = $rowJobRole['postjobrole'];
        }
        $stmtJobRole->close();
    }
    $stmt = $con->prepare("DELETE FROM Jobposting WHERE postid = ?");
    $stmt->bind_param("i", $postid);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            
            $action = 'Delete Job Post';
            $details = "Deleted job posting (ID: " . $postid . ", Role: " . htmlspecialchars($jobRole) . ").";
            
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = $con->prepare($insertAuditQuery);
            
            if ($stmtAudit) {
                $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $action, $details);
                $stmtAudit->execute();
                $stmtAudit->close();
            } else {
                error_log("Failed to prepare audit trail statement in delete_posting.php: " . $con->error);
            }

            $_SESSION['message'] = 'Posting successfully deleted.';
            $_SESSION['message_type'] = 'success';

            echo json_encode([
                'success' => true, 
                'message' => 'Posting successfully deleted.', 
                'postid' => $postid
            ]);
        } else {
            http_response_code(404); 
            echo json_encode(['success' => false, 'message' => 'No posting found with that ID.']);
        }
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Posting Deletion Error: " . $e->getMessage()); 
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred during deletion.']);
}

if (isset($con)) {
    $con->close();
}
?>