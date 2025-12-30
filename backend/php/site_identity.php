<?php
session_start();
include 'database.php'; 

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$site_identity_message = '';
$site_identity_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_identity_submit'])) {
    $newSiteName = $_POST['site_name'] ?? '';
    $updateSuccessful = false;


    if (isset($_FILES['upload_logo']) && $_FILES['upload_logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['upload_logo']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);

        $updateStmt = $con->prepare("UPDATE systemname SET sysname = ?, sysimage = ? WHERE sysid = 1");
        $updateStmt->bind_param("sb", $newSiteName, $null); 
        $updateStmt->send_long_data(1, $fileContent); 

        if ($updateStmt->execute()) {
            $updateSuccessful = true;
        } else {
            $site_identity_message = 'Error updating site identity: ' . $updateStmt->error;
            $site_identity_message_type = 'error';
        }
        $updateStmt->close();

    } elseif (!empty($newSiteName)) { 
        $updateStmt = $con->prepare("UPDATE systemname SET sysname = ? WHERE sysid = 1");
        $updateStmt->bind_param("s", $newSiteName);
        if ($updateStmt->execute()) {
            $updateSuccessful = true;
        } else {
            $site_identity_message = 'Error updating site name: ' . $updateStmt->error;
            $site_identity_message_type = 'error';
        }
        $updateStmt->close();
    } else {
        $site_identity_message = 'No changes to save.';
        $site_identity_message_type = 'error';
    }

    if ($updateSuccessful) {
        $_SESSION['site_identity_message'] = 'Site Identity Successfully Updated!';
        $_SESSION['site_identity_message_type'] = 'success';
        header('Location: settings.php?section=site-identity-content');
        exit();
    } else {
    
        if (!empty($site_identity_message)) {
            $_SESSION['site_identity_message'] = $site_identity_message;
            $_SESSION['site_identity_message_type'] = $site_identity_message_type;
            header('Location: settings.php?section=site-identity-content');
            exit();
        }
    }
}
?>