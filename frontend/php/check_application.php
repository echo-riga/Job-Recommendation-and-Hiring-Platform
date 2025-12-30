<?php
session_start();
include 'database.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode(['alreadyApplied' => false]);
    exit();
}

$user_id = $_SESSION['userid'];
$postid = isset($_GET['postid']) ? (int)$_GET['postid'] : null;

if ($postid === null) {
    echo json_encode(['alreadyApplied' => false]);
    exit();
}

$stmt = $con->prepare("SELECT applicationid FROM application WHERE userid = ? AND postid = ?");
$stmt->bind_param("ii", $user_id, $postid);
$stmt->execute();
$result = $stmt->get_result();

$alreadyApplied = $result->num_rows > 0;

$stmt->close();

echo json_encode(['alreadyApplied' => $alreadyApplied]);
?>