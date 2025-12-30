<?php
session_start();
include 'database.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if (!isset($_POST['postid']) || empty($_POST['postid'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit();
}

$postid = $_POST['postid'];


$stmt = $con->prepare("
    SELECT 
        postid, postjobrole, postsummary, postresponsibilities, 
        postspecification, postexperience, postsalary, postaddress, 
        posttype, postworksetup, postapplicantlimit,
        DATE_FORMAT(postdate, '%M %e, %Y') as formatted_postdate, 
        DATE_FORMAT(postdeadline, '%M %e, %Y') as formatted_postdeadline, 
        poststatus,
        postdeadline as postdeadline_raw
    FROM 
        Jobposting 
    WHERE 
        postid = ?
");
$stmt->bind_param("i", $postid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $postData = $result->fetch_assoc();
    
    $postData['postsalary_formatted'] = 'P ' . number_format($postData['postsalary'], 0, '.', ',');

    echo json_encode(['success' => true, 'data' => $postData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Job post not found.']);
}

$stmt->close();
$con->close();
?>