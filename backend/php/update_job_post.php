<?php
session_start();
header('Content-Type: application/json');
include 'database.php'; 

if (!isset($_SESSION['admin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request.']);
    exit();
}

$postId = $_POST['postid'] ?? '';
$postJobRole = $_POST['postjobrole'] ?? '';
$postType = $_POST['posttype'] ?? '';
$postExperience = $_POST['postexperience'] ?? '';
$postSalary = $_POST['postsalary'] ?? '';
$postAddress = $_POST['postaddress'] ?? '';
$postDeadline = $_POST['postdeadline'] ?? '';

$postSummary = $_POST['postsummary'] ?? '';
$postResponsibilities = $_POST['postresponsibilities'] ?? '';
$postSpecification = $_POST['postspecification'] ?? '';
$postWorkSetup = $_POST['postworksetup'] ?? '';

$postApplicantLimit = (empty($_POST['postapplicantlimit']) || !is_numeric($_POST['postapplicantlimit'])) ? 0 : (int)$_POST['postapplicantlimit'];


if (empty($postId) || empty($postJobRole) || empty($postType) || empty($postDeadline) || empty($postWorkSetup)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields for update.']);
    exit();
}

$postSalary = is_numeric($postSalary) ? (float)$postSalary : 0.0;


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

$originalJobInfo = [];
$getOriginalQuery = "SELECT postjobrole, posttype, postexperience, postsalary, postaddress, 
                            postsummary, postresponsibilities, postspecification, postworksetup, 
                            postapplicantlimit, postdeadline 
                       FROM Jobposting WHERE postid = ?";
$stmtGetOriginal = $con->prepare($getOriginalQuery);

if ($stmtGetOriginal) {
    $stmtGetOriginal->bind_param("i", $postId);
    $stmtGetOriginal->execute();
    $resultOriginal = $stmtGetOriginal->get_result();
    if ($resultOriginal && $rowOriginal = $resultOriginal->fetch_assoc()) {
        $originalJobInfo = $rowOriginal;
    }
    $stmtGetOriginal->close();
}



try {

    $stmt = $con->prepare("
        UPDATE Jobposting 
        SET 
            postjobrole = ?, 
            posttype = ?, 
            postexperience = ?, 
            postsalary = ?, 
            postaddress = ?, 
            postsummary = ?, 
            postresponsibilities = ?, 
            postspecification = ?, 
            postworksetup = ?,
            postapplicantlimit = ?,
            postdeadline = ?
        WHERE postid = ?
    ");
    
   
    $stmt->bind_param(
        "sssdsssssisi", 
        $postJobRole,  
        $postType,     
        $postExperience, 
        $postSalary,  
        $postAddress, 
        $postSummary,
        $postResponsibilities,
        $postSpecification,
        $postWorkSetup,
        $postApplicantLimit,
        $postDeadline,
        $postId       
    );
   
    
    if ($stmt->execute()) {
        
        
        $action = 'Update Job Post';
        $detailsChanges = [];
        $fieldsToCompare = [
            'postjobrole' => $postJobRole, 
            'posttype' => $postType, 
            'postexperience' => $postExperience, 
            'postsalary' => $postSalary, 
            'postaddress' => $postAddress,
            'postsummary' => $postSummary,
            'postresponsibilities' => $postResponsibilities,
            'postspecification' => $postSpecification,
            'postworksetup' => $postWorkSetup,
            'postapplicantlimit' => $postApplicantLimit,
            'postdeadline' => $postDeadline 
        ];

        foreach ($fieldsToCompare as $field => $newValue) {
            $originalValue = $originalJobInfo[$field] ?? null;

     
            if ($field === 'postdeadline') {
                $originalValue = $originalValue === null ? '' : $originalValue;
                $newValue = $newValue === null ? '' : $newValue;
            } elseif ($field === 'postsalary' || $field === 'postapplicantlimit') {
                $originalValue = (float)($originalValue ?? 0);
                $newValue = (float)($newValue ?? 0);

                if ($field === 'postsalary') {
                    $originalDisplay = number_format($originalValue, 2);
                    $newDisplay = number_format($newValue, 2);
                    if ($originalValue !== $newValue) {
                        $detailsChanges[] = "Salary changed from '₱" . $originalDisplay . "' to '₱" . $newDisplay . "'";
                    }
                } else { 
                     if ($originalValue !== $newValue) {
                        $detailsChanges[] = "Applicant Limit changed from '" . $originalValue . "' to '" . $newValue . "'";
                    }
                }
                continue; 
            }
            
  
            if ((string)$newValue !== (string)$originalValue) {
         
                $fieldName = ucwords(str_replace('post', '', $field)); 
                $detailsChanges[] = $fieldName . " changed from '" . htmlspecialchars($originalValue) . "' to '" . htmlspecialchars($newValue) . "'";
            }
        }
      

        $details = "Updated job posting (ID: " . $postId . ", Role: " . htmlspecialchars($postJobRole) . "). ";
        if (empty($detailsChanges)) {
            $details .= "No significant field changes detected.";
        } else {
            $details .= implode("; ", $detailsChanges) . ".";
        }

        $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
        $stmtAudit = $con->prepare($insertAuditQuery);
        
        if ($stmtAudit) {
            $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $action, $details);
            $stmtAudit->execute();
            $stmtAudit->close();
        } else {
            error_log("Failed to prepare audit trail statement in update_job_post.php: " . $con->error);
        }

        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = 'Job post updated successfully.';
            $_SESSION['message_type'] = 'success';
            echo json_encode(['success' => true, 'message' => 'Job post updated successfully.']);
        } else {
            $_SESSION['message'] = 'Job post data is already up to date (no changes made).';
            $_SESSION['message_type'] = 'success'; 
            echo json_encode(['success' => true, 'message' => 'Job post data is already up to date (no changes made).']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$con->close();
?>