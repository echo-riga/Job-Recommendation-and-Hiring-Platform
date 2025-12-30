<?php
session_start();
include 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to apply.']);
    exit();
}

$user_id = $_SESSION['userid'];
$postid = isset($_POST['postid']) ? (int)$_POST['postid'] : null;

if ($postid === null) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID.']);
    exit();
}

$checkStmt = $con->prepare("SELECT applicationid FROM application WHERE userid = ? AND postid = ?");
$checkStmt->bind_param("ii", $user_id, $postid);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already applied for this job.']);
    exit();
}
$checkStmt->close();

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please upload your resume.']);
    exit();
}

$resume = file_get_contents($_FILES['resume']['tmp_name']);

$resume_escaped = mysqli_real_escape_string($con, $resume);
$sql = "INSERT INTO application (postid, userid, resume, status) VALUES ($postid, $user_id, '$resume_escaped', 'Pending')";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
} else {
    error_log("Insert failed: " . mysqli_error($con));
    echo json_encode(['success' => false, 'message' => 'Failed to submit application.']);
}
?>