<?php
session_start();
header('Content-Type: application/json');

include 'database.php'; 

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_POST['action'] ?? 'archive';
$applicationId = (int)$_POST['appid'] ?? 0;

if ($applicationId === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing application ID.']);
    exit();
}

$con->begin_transaction();
$success = false;
$message = '';

try {
    if ($action === 'archive') {
        $stmt_fetch = $con->prepare("SELECT postid, userid, resume, date_applied, status FROM application WHERE applicationid = ?");
        $stmt_fetch->bind_param("i", $applicationId);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 0) {
            $stmt_fetch->close();
            throw new Exception('Application not found in active table.');
        }

        $applicationData = $result_fetch->fetch_assoc();
        $stmt_fetch->close();

        $postId = $applicationData['postid'];
        $userId = $applicationData['userid'];
        $resume = $applicationData['resume']; 
        $dateApplied = $applicationData['date_applied'];
        $status = $applicationData['status'];

        $stmt_archive = $con->prepare("INSERT INTO archived_applicants (applicationid, postid, userid, resume, date_applied, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_archive->bind_param("iiisss", $applicationId, $postId, $userId, $resume, $dateApplied, $status);
        
        if (!$stmt_archive->execute()) {
            $stmt_archive->close();
            throw new Exception('Failed to insert into archive table: ' . $con->error);
        }
        $stmt_archive->close();

        $stmt_delete = $con->prepare("DELETE FROM application WHERE applicationid = ?");
        $stmt_delete->bind_param("i", $applicationId);
        
        if (!$stmt_delete->execute()) {
            $stmt_delete->close();
            throw new Exception('Failed to delete from application table: ' . $con->error);
        }
        $stmt_delete->close();
        
        $success = true;
        $message = 'Application archived and removed successfully.';

    } elseif ($action === 'retrieve') {

        $stmt_fetch = $con->prepare("SELECT postid, userid, resume, date_applied, status FROM archived_applicants WHERE applicationid = ?");
        $stmt_fetch->bind_param("i", $applicationId);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows === 0) {
            $stmt_fetch->close();
            throw new Exception('Application not found in archive table.');
        }

        $applicationData = $result_fetch->fetch_assoc();
        $stmt_fetch->close();

        $postId = $applicationData['postid'];
        $userId = $applicationData['userid'];
        $resume = $applicationData['resume']; 
        $dateApplied = $applicationData['date_applied'];
        $newStatus = 'Pending'; 

    
        $stmt_insert = $con->prepare("INSERT INTO application (applicationid, postid, userid, resume, date_applied, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iiisss", $applicationId, $postId, $userId, $resume, $dateApplied, $newStatus);
        
        if (!$stmt_insert->execute()) {
            $stmt_insert->close();
            throw new Exception('Failed to insert into active application table: ' . $con->error);
        }
        $stmt_insert->close();

        $stmt_delete = $con->prepare("DELETE FROM archived_applicants WHERE applicationid = ?");
        $stmt_delete->bind_param("i", $applicationId);
        
        if (!$stmt_delete->execute()) {
            $stmt_delete->close();
            throw new Exception('Failed to delete from archive table: ' . $con->error);
        }
        $stmt_delete->close();

        $success = true;
        $message = 'Application successfully retrieved and moved back to active applicants.';

    } else if ($action === 'delete_permanent') {
        
        $stmt_delete = $con->prepare("DELETE FROM archived_applicants WHERE applicationid = ?");
        $stmt_delete->bind_param("i", $applicationId);
        
        if (!$stmt_delete->execute()) {
            $stmt_delete->close();
            throw new Exception('Failed to permanently delete from archive table: ' . $con->error);
        }
        $stmt_delete->close();

        $success = true;
        $message = 'Application permanently deleted from archive.';
    }


    if ($success) {
        $con->commit();
        
        $_SESSION['message'] = $message; 
        $_SESSION['message_type'] = 'success';
        
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        $con->rollback();
        echo json_encode(['success' => false, 'message' => 'Operation failed. Database transaction rolled back.']);
    }

} catch (Exception $e) {
    $con->rollback();
    error_log("Archive/Retrieve/Delete Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}

$con->close();
?>