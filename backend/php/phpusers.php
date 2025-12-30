
<?php
include 'database.php';

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin_login.php');
    exit();
}


$adminName = "Administrator";
$avatarInitials = "AD";
$adminImageBase64 = null; 
$adminEmail = $_SESSION['admin'];
$stmt = $con->prepare("SELECT adminid, adminname, adminpassword, adminimage FROM admin WHERE adminemail = ?"); 
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$res = $stmt->get_result();
$adminData = null;
if ($res->num_rows === 1) {
    $adminData = $res->fetch_assoc();
    $_SESSION['admin_id_for_update_check'] = $adminData['adminid'];
    $adminName = $adminData['adminname'];
    $nameParts = explode(" ", trim($adminName));
    $avatarInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $avatarInitials .= strtoupper(substr($nameParts[1], 0, 1));
    }

    if (!empty($adminData['adminimage'])) {
        $adminImageBase64 = 'data:image/jpeg;base64,' . base64_encode($adminData['adminimage']);
    }
}
$stmt->close();

$systemName = "JOBFIT"; 
$systemLogoBase64 = '../image/jftlogo.png'; 

$stmtSys = $con->prepare("SELECT sysname, sysimage FROM systemname WHERE sysid = 1");
$stmtSys->execute();
$resSys = $stmtSys->get_result();
if ($resSys->num_rows === 1) {
    $sysData = $resSys->fetch_assoc();
    $systemName = htmlspecialchars($sysData['sysname']);
    if (!empty($sysData['sysimage'])) {
        $systemLogoBase64 = 'data:image/jpeg;base64,' . base64_encode($sysData['sysimage']);
    }
}
$stmtSys->close();


$message = '';
$message_type = ''; 

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}


?>