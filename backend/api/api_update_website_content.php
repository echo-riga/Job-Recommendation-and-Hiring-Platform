<?php
session_start();
include '../php/database.php';

// DEBUG: Start logging
error_log("=== WEBSITE CONTENT API CALLED ===");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is admin
if (!isset($_SESSION['admin'])) {
    error_log("UNAUTHORIZED: No admin in session");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

error_log("User authorized: " . $_SESSION['admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("INVALID METHOD: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// DEBUG: Log all incoming data
error_log("=== INCOMING POST DATA ===");
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));
error_log("=== END INCOMING DATA ===");

try {
    // Get admin info for audit trail
    $adminEmail = $_SESSION['admin'];
    error_log("Fetching admin data for: " . $adminEmail);
    
    $adminStmt = $con->prepare("SELECT adminid, adminname FROM admin WHERE adminemail = ?");
    if (!$adminStmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $adminStmt->bind_param("s", $adminEmail);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminData = $adminResult->fetch_assoc();
    $adminStmt->close();

    if (!$adminData) {
        throw new Exception("Admin data not found for email: " . $adminEmail);
    }

    $currentAdminId = $adminData['adminid'];
    $adminName = $adminData['adminname'];
    error_log("Admin found - ID: $currentAdminId, Name: $adminName");

    // Start transaction
    error_log("Starting database transaction");
    mysqli_begin_transaction($con);
    
    // Get text data
    $aboutus_home = mysqli_real_escape_string($con, $_POST['aboutus_home'] ?? '');
    $who_we_are = mysqli_real_escape_string($con, $_POST['who_we_are'] ?? '');
    $mission = mysqli_real_escape_string($con, $_POST['mission'] ?? '');
    $vision = mysqli_real_escape_string($con, $_POST['vision'] ?? '');
    $quality_policy = mysqli_real_escape_string($con, $_POST['quality_policy'] ?? '');

    // DEBUG: Log the data we're about to save
    error_log("=== DATA TO SAVE ===");
    error_log("aboutus_home: " . substr($aboutus_home, 0, 100));
    error_log("who_we_are: " . substr($who_we_are, 0, 100));
    error_log("mission: " . substr($mission, 0, 100));
    error_log("vision: " . substr($vision, 0, 100));
    error_log("quality_policy: " . substr($quality_policy, 0, 100));
    error_log("=== END DATA TO SAVE ===");

    // Ensure the row exists
    error_log("Checking if website_content row exists...");
    $checkStmt = $con->prepare("SELECT content_id FROM website_content WHERE content_id = 1");
    if (!$checkStmt) {
        throw new Exception("Prepare check failed: " . $con->error);
    }
    
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        error_log("Creating new website_content row");
        $insertStmt = $con->prepare("INSERT INTO website_content (content_id) VALUES (1)");
        if (!$insertStmt) {
            throw new Exception("Prepare insert failed: " . $con->error);
        }
        $insertStmt->execute();
        $insertStmt->close();
        error_log("New website_content row created");
    } else {
        error_log("website_content row already exists");
    }
    $checkStmt->close();

    // Update text content
    $textQuery = "UPDATE website_content SET 
        aboutus_home = '$aboutus_home', 
        who_we_are = '$who_we_are', 
        mission = '$mission', 
        vision = '$vision', 
        quality_policy = '$quality_policy' 
        WHERE content_id = 1";
    
    error_log("Executing UPDATE query: " . $textQuery);
    
    $result = mysqli_query($con, $textQuery);
    if (!$result) {
        $error = mysqli_error($con);
        error_log("DATABASE UPDATE FAILED: " . $error);
        throw new Exception("Text update failed: " . $error);
    }

    // DEBUG: Check if update was successful
    $affectedRows = mysqli_affected_rows($con);
    error_log("UPDATE SUCCESS - Affected rows: " . $affectedRows);

    if ($affectedRows === 0) {
        error_log("WARNING: No rows affected by update - data may not have changed");
    }

    // Handle banner upload (optional - skip if no file)
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        error_log("Processing banner upload...");
        $bannerFile = $_FILES['banner'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($bannerFile['type'], $allowedTypes) && $bannerFile['size'] <= $maxSize) {
            $bannerData = mysqli_real_escape_string($con, file_get_contents($bannerFile['tmp_name']));
            $bannerQuery = "UPDATE website_content SET banner = '$bannerData' WHERE content_id = 1";
            
            if (!mysqli_query($con, $bannerQuery)) {
                throw new Exception("Banner update failed: " . mysqli_error($con));
            }
            error_log("Banner image updated successfully");
        } else {
            throw new Exception("Invalid banner image. Please upload JPEG/PNG under 5MB.");
        }
    } else {
        error_log("No banner file uploaded or upload error: " . ($_FILES['banner']['error'] ?? 'N/A'));
    }

    // Handle group photo upload (optional - skip if no file)
    if (isset($_FILES['group_photo']) && $_FILES['group_photo']['error'] === UPLOAD_ERR_OK) {
        error_log("Processing group photo upload...");
        $groupPhotoFile = $_FILES['group_photo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($groupPhotoFile['type'], $allowedTypes) && $groupPhotoFile['size'] <= $maxSize) {
            $groupPhotoData = mysqli_real_escape_string($con, file_get_contents($groupPhotoFile['tmp_name']));
            $groupPhotoQuery = "UPDATE website_content SET group_photo = '$groupPhotoData' WHERE content_id = 1";
            
            if (!mysqli_query($con, $groupPhotoQuery)) {
                throw new Exception("Group photo update failed: " . mysqli_error($con));
            }
            error_log("Group photo updated successfully");
        } else {
            throw new Exception("Invalid group photo. Please upload JPEG/PNG under 5MB.");
        }
    } else {
        error_log("No group photo file uploaded or upload error: " . ($_FILES['group_photo']['error'] ?? 'N/A'));
    }

    // Commit transaction
    error_log("Committing transaction...");
    mysqli_commit($con);
    error_log("Transaction committed successfully");
    
    // Audit trail
    error_log("Creating audit trail entry...");
    $auditAction = 'Update Website Content';
    $auditDetails = "Administrator " . htmlspecialchars($adminName) . " (ID: " . htmlspecialchars($currentAdminId) . ") updated the website content via API.";
    $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
    $stmtAudit = $con->prepare($insertAuditQuery);
    if (!$stmtAudit) {
        throw new Exception("Prepare audit failed: " . $con->error);
    }
    $stmtAudit->bind_param("isss", $currentAdminId, $adminName, $auditAction, $auditDetails);
    $stmtAudit->execute();
    $stmtAudit->close();
    error_log("Audit trail entry created");

    error_log("=== WEBSITE CONTENT UPDATE COMPLETED SUCCESSFULLY ===");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Website content updated successfully!',
        'affected_rows' => $affectedRows
    ]);

} catch (Exception $e) {
    error_log("=== ERROR IN WEBSITE CONTENT API ===");
    error_log("Error: " . $e->getMessage());
    error_log("Rolling back transaction...");
    
    mysqli_rollback($con);
    
    error_log("=== END ERROR ===");
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Final debug
error_log("=== API EXECUTION COMPLETED ===");
?>