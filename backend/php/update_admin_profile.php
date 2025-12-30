<?php
session_start();
include 'database.php'; 

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => '', 'imageUrl' => null];

if (!isset($_SESSION['admin'])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$adminEmailSession = $_SESSION['admin']; 


$newAdminName = $_POST['adminname'] ?? '';
$newAdminEmail = $_POST['adminemail'] ?? '';
$adminImageFile = $_FILES['adminImage'] ?? null; 


if (empty($newAdminName) || empty($newAdminEmail)) {
    $response['message'] = 'Full Name and Email Address are required.';
    echo json_encode($response);
    exit();
}

if (!filter_var($newAdminEmail, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email address format.';
    echo json_encode($response);
    exit();
}


$currentAdminId = null;
$originalAdminName = '';
$originalAdminEmail = $adminEmailSession;

$getOriginalInfoQuery = "SELECT adminid, adminname, adminemail FROM admin WHERE adminemail = ?";
$stmtGetOriginal = $con->prepare($getOriginalInfoQuery);
if ($stmtGetOriginal) {
    $stmtGetOriginal->bind_param("s", $adminEmailSession);
    $stmtGetOriginal->execute();
    $resultOriginal = $stmtGetOriginal->get_result();
    if ($rowOriginal = $resultOriginal->fetch_assoc()) {
        $currentAdminId = $rowOriginal['adminid'];
        $originalAdminName = $rowOriginal['adminname'];
        $originalAdminEmail = $rowOriginal['adminemail'];
    }
    $stmtGetOriginal->close();
} else {
    error_log("Failed to prepare statement to get original admin info: " . $con->error);
}

$currentAdminName = $originalAdminName; 

mysqli_begin_transaction($con);

try {
    $updateImage = false;
    $imageData = null;


    if ($adminImageFile && $adminImageFile['error'] === UPLOAD_ERR_OK) {
        $check = getimagesize($adminImageFile['tmp_name']);
        if ($check === false) {
            throw new Exception("File is not an image.");
        }

        $imageData = file_get_contents($adminImageFile['tmp_name']);
        $updateImage = true;
    } else if (isset($_POST['adminImage']) && $_POST['adminImage'] === '') {
        $imageData = null; 
        $updateImage = true;
    }



    $query = "UPDATE admin SET adminname = ?, adminemail = ?";
    if ($updateImage) {
        $query .= ", adminimage = ?";
    }
    $query .= " WHERE adminemail = ?";

    $stmt = $con->prepare($query);

    if ($updateImage) {
        if ($imageData === null) {

            $null = NULL;
            $stmt->bind_param("ssbs", $newAdminName, $newAdminEmail, $null, $adminEmailSession); 
        } else {
            $null = NULL; 
            $stmt->bind_param("ssbs", $newAdminName, $newAdminEmail, $null, $adminEmailSession);
            $stmt->send_long_data(2, $imageData); 
        }
    } else {
        $stmt->bind_param("sss", $newAdminName, $newAdminEmail, $adminEmailSession);
    }

    if ($stmt->execute()) {
        
        if ($newAdminEmail !== $adminEmailSession) {
            $_SESSION['admin'] = $newAdminEmail;
        }

        mysqli_commit($con);

        $action = 'Update Admin Profile';
        $detailsChanges = [];
        
        if ($newAdminName !== $originalAdminName) {
            $detailsChanges[] = "Name changed from '" . htmlspecialchars($originalAdminName) . "' to '" . htmlspecialchars($newAdminName) . "'";
        }
        if ($newAdminEmail !== $originalAdminEmail) {
            $detailsChanges[] = "Email changed from '" . htmlspecialchars($originalAdminEmail) . "' to '" . htmlspecialchars($newAdminEmail) . "'";
        }
        if ($updateImage) {
            $detailsChanges[] = $imageData === null ? "Profile picture was removed" : "Profile picture was updated";
        }

        $details = "Admin (ID: " . $currentAdminId . ") updated own profile. ";
        if (empty($detailsChanges)) {
             $details .= "No significant field changes detected (Name, Email, Image).";
        } else {
             $details .= implode(", ", $detailsChanges) . ".";
        }
        
        $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
        $stmtAudit = $con->prepare($insertAuditQuery);
        
        if ($stmtAudit) {
          
            $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $action, $details);
            $stmtAudit->execute();
            $stmtAudit->close();
        } else {
            error_log("Failed to prepare audit trail statement in update_admin_profile.php: " . $con->error);
        }
     
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
        $_SESSION['message'] = 'Profile updated successfully!';
        $_SESSION['message_type'] = 'success'; 

     
        $emailToFetch = $newAdminEmail;
        $stmtCheckImage = $con->prepare("SELECT adminimage FROM admin WHERE adminemail = ?");
        $stmtCheckImage->bind_param("s", $emailToFetch);
        $stmtCheckImage->execute();
        $resCheckImage = $stmtCheckImage->get_result();
        if ($resCheckImage->num_rows === 1) {
            $updatedAdminData = $resCheckImage->fetch_assoc();
            if (!empty($updatedAdminData['adminimage'])) {
                $response['imageUrl'] = 'data:image/jpeg;base64,' . base64_encode($updatedAdminData['adminimage']);
            } else {
                $response['imageUrl'] = ''; 
            }
        }
        $stmtCheckImage->close();

    } else {
        throw new Exception("Failed to update profile: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    mysqli_rollback($con);
    $response['message'] = $e->getMessage();
    $_SESSION['message'] = 'Error updating profile: ' . $e->getMessage(); 
    $_SESSION['message_type'] = 'error'; 
}

echo json_encode($response);
?>