<?php
session_start(); 
header('Content-Type: application/json');

include 'database.php'; 

function sendResponse($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    global $con;
    if ($con) {
        $con->close();
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// --- Security Check and Admin Info Retrieval ---
if (!isset($_SESSION['admin'])) {
    sendResponse(false, 'Unauthorized access. Admin session required.');
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
            $adminNameFromDB = $rowAdminInfo['adminname'] !== null ? $rowAdminInfo['adminname'] : 'System Admin';
            $currentAdminName = $adminNameFromDB;
        }
        $stmtAdminInfo->close();
    } else {
        error_log("Failed to prepare statement to get admin info in add_job_post.php: " . $con->error);
    }
}

$required_fields = [
    'postjobrole',
    'postsummary',
    'postresponsibilities',
    'postspecification',
    'postexperience',
    'postsalary',
    'postaddress',
    'posttype',
    'postworksetup'
];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        sendResponse(false, 'Missing required field: ' . htmlspecialchars($field));
    }
}

$postjobrole = trim($_POST['postjobrole']);
$postsummary = trim($_POST['postsummary']);
$postresponsibilities = trim($_POST['postresponsibilities']);
$postspecification = trim($_POST['postspecification']);
$postexperience = trim($_POST['postexperience']);
$postsalary = floatval($_POST['postsalary']); 
$postaddress = trim($_POST['postaddress']);
$posttype = trim($_POST['posttype']);
$postworksetup = trim($_POST['postworksetup']);

$postapplicantlimit = !empty(trim($_POST['postapplicantlimit'])) ? (int)trim($_POST['postapplicantlimit']) : null;
$postdeadline = !empty(trim($_POST['postdeadline'])) ? trim($_POST['postdeadline']) : null;


if ($postsalary < 0) {
    sendResponse(false, 'Salary must be a non-negative value.');
}

$sql = "INSERT INTO Jobposting (
    postjobrole, 
    postsummary, 
    postresponsibilities, 
    postspecification, 
    postexperience, 
    postsalary, 
    postaddress, 
    posttype, 
    postworksetup, 
    postapplicantlimit, 
    postdeadline
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $con->prepare($sql)) {
    
    $stmt->bind_param(
        "sssssdsssis", 
        $postjobrole,
        $postsummary,
        $postresponsibilities,
        $postspecification,
        $postexperience, 
        $postsalary, 
        $postaddress, 
        $posttype,
        $postworksetup,
        $postapplicantlimit,
        $postdeadline
    );

    if ($stmt->execute()) {
        
        // --- Audit Trail Insertion ---
        $action = 'Create Job Post';
        $details = "Created a new job posting for the role: '" . htmlspecialchars($postjobrole) . "' (Salary: " . number_format($postsalary, 2) . ", Type: " . htmlspecialchars($posttype) . ").";
        
        $insertAuditQuery = "INSERT INTO audit (adminid, username, action, details) VALUES (?, ?, ?, ?)";
        $stmtAudit = $con->prepare($insertAuditQuery);
        
        if ($stmtAudit) {
            $stmtAudit->bind_param("isss", $currentAdminId, $currentAdminName, $action, $details);
            $stmtAudit->execute();
            $stmtAudit->close();
        } else {
            error_log("Failed to prepare audit trail statement in add_job_post.php: " . $con->error);
        }
        $_SESSION['message'] = 'Job post created successfully!';
        $_SESSION['message_type'] = 'success';

        sendResponse(true, 'Job post created successfully!');
    } else {
        sendResponse(false, 'Database error: Could not execute statement. ' . $stmt->error);
    }

    $stmt->close();
} else {
    sendResponse(false, 'Database error: Could not prepare statement. ' . $con->error);
}

$con->close();

?>