<?php
session_start();
include 'database.php';

require 'Mail/phpmailer/PHPMailerAutoload.php'; 

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitAddAdmin'])) {
    $adminName = trim($_POST['adminName'] ?? '');
    $adminEmail = trim($_POST['adminEmail'] ?? '');
    $adminPassword = $_POST['adminPassword'] ?? '';

    // Validate inputs
    if (empty($adminName) || empty($adminEmail) || empty($adminPassword)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    } elseif (strlen($adminPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "error";
    } else {
        // Check if email already exists
        $stmt_check = $con->prepare("SELECT adminid FROM admin WHERE adminemail = ?");
        $stmt_check->bind_param("s", $adminEmail);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "An admin with this email already exists.";
            $message_type = "error";
        } else {
            // Hash the password
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            $adminStatus = 'Active'; 

            // Prepare and execute the insert statement
            $stmt_insert = $con->prepare("INSERT INTO admin (adminname, adminemail, adminpassword,adminstatus) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $adminName, $adminEmail, $hashedPassword, $adminStatus);

            if ($stmt_insert->execute()) {
                $message = "New admin '" . htmlspecialchars($adminName) . "' added successfully!";
                $message_type = "success";

                // Send email to the new admin
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'jftsystem@gmail.com'; 
                    $mail->Password = 'vwhs rehv nang bxuu'; 

                    $mail->setFrom('jftsystem@gmail.com', 'JOBFIT Administrator');
                    $mail->addAddress($adminEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to JOBFIT Admin Panel - Your New Account Details';
                    $mail->Body    = "
                        <p>Dear " . htmlspecialchars($adminName) . ",</p>
                        <p>A new administrator account has been created for you on the JOBFIT Admin Panel.</p>
                        <p>Here are your login details:</p>
                        <ul>
                            <li><strong>Email:</strong> " . htmlspecialchars($adminEmail) . "</li>
                            <li><strong>Password:</strong> " . htmlspecialchars($adminPassword) . "</li>
                        </ul>
                        <p>For security reasons, we recommend you change your password after your first login.</p>
                        <p>You can access the admin panel here: <a href='http://localhost/jftsystem/backend/php/admin_login.php'>Login to Admin Panel</a></p>
                        <p>Thank you,<br>
                        The JOBFIT Team</p>
                    ";
                    $mail->send();
                    $message .= " Email sent.";
                } catch (Exception $e) {
                    $message .= " However, the welcome email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    $message_type = "warning"; 
                }

                // --- Audit Trail Entry for Adding Admin ---
            $currentAdminEmail = $_SESSION['admin'];
            $currentAdminId = null;
            $currentAdminName = '';

            $getAdminInfoQuery = "SELECT adminid, adminname FROM admin WHERE adminemail = ?";
            $stmtAdminInfo = mysqli_prepare($con, $getAdminInfoQuery);
            mysqli_stmt_bind_param($stmtAdminInfo, "s", $currentAdminEmail);
            mysqli_stmt_execute($stmtAdminInfo);
            $resultAdminInfo = mysqli_stmt_get_result($stmtAdminInfo);
            if ($rowAdminInfo = mysqli_fetch_assoc($resultAdminInfo)) {
                $currentAdminId = $rowAdminInfo['adminid'];
                $currentAdminName = 'Admin ' . $rowAdminInfo['adminname'];
            }
            mysqli_stmt_close($stmtAdminInfo);

            $action = 'Add Admin';
            $details = "Added new admin: " . $adminName . " (" . $adminEmail . ")";
            $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
            $stmtAudit = mysqli_prepare($con, $insertAuditQuery);
            mysqli_stmt_bind_param($stmtAudit, "isss", $currentAdminId, $currentAdminName, $action, $details);
            mysqli_stmt_execute($stmtAudit);
            mysqli_stmt_close($stmtAudit);
            
            } else {
                $message = "Error adding admin: " . $stmt_insert->error;
                $message_type = "error";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
    $con->close();


    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $message_type;
    header("Location: users.php");
    exit();
} else {
    header("Location: users.php");
    exit();
}
?>