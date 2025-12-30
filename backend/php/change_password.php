<?php
session_start();
include 'database.php'; 

if (!isset($_SESSION['admin'])) {
    // Redirect if not logged in
    header("Location: admin_login.php");
    exit();
}

$password_message = '';
$password_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password_submit'])) {
    $adminEmail = $_SESSION['admin']; 
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    $stmt = $con->prepare("SELECT adminpassword FROM admin WHERE adminemail = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $res = $stmt->get_result();
    $adminData = $res->fetch_assoc();
    $stmt->close();

    if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
        $password_message = 'All password fields are required.';
        $password_message_type = 'error';
    } elseif ($newPassword !== $confirmNewPassword) {
        $password_message = 'New password and confirm new password do not match.';
        $password_message_type = 'error';
    } elseif (strlen($newPassword) < 8 ||
              !preg_match("/[A-Z]/", $newPassword) ||
              !preg_match("/[a-z]/", $newPassword) ||
              !preg_match("/[0-9]/", $newPassword) ||
              !preg_match("/[^A-Za-z0-9]/", $newPassword)) {
        $password_message = 'New password must meet the required password format';
        $password_message_type = 'error';
    } else {
        // Verify current password
        if ($adminData && password_verify($currentPassword, $adminData['adminpassword'])) {
            // Hash the new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in DB
            $updateStmt = $con->prepare("UPDATE admin SET adminpassword = ? WHERE adminemail = ?");
            $updateStmt->bind_param("ss", $hashedNewPassword, $adminEmail);

            if ($updateStmt->execute()) {
                $_SESSION['password_message'] = 'Password Successfully Changed';
                $_SESSION['password_message_type'] = 'success';
                header('Location: settings.php?section=change-password-content');
                exit();
            } else {
                $password_message = 'Error updating password: ' . $updateStmt->error;
                $password_message_type = 'error';
            }
            $updateStmt->close();
        } else {
            $password_message = 'Current password is incorrect.';
            $password_message_type = 'error';
        }
    }

    if ($password_message_type === 'error') {
        $_SESSION['password_message'] = $password_message;
        $_SESSION['password_message_type'] = $password_message_type;
        header('Location: settings.php?section=change-password-content');
        exit();
    }
}
?>