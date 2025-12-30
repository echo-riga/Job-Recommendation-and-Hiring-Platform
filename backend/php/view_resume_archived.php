<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'database.php'; 

if (isset($_GET['appid'])) {
    $applicationID = (int)$_GET['appid'];

    $stmt = $con->prepare("
        SELECT 
            a.resume, 
            u.firstname, 
            u.lastname
        FROM 
            archived_applicants a 
        INNER JOIN 
            users u ON a.userid = u.userid
        WHERE 
            a.applicationid = ?
    ");
    $stmt->bind_param("i", $applicationID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($resumeData, $firstName, $lastName);
        $stmt->fetch();
        
        $fileName = "Resume_" . $lastName . "_" . $firstName . "_" . $applicationID . ".pdf";
        

        header('Content-Type: application/pdf'); 
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($resumeData));
        

        if (ob_get_level()) {
            ob_clean();
        }

        echo $resumeData;
        
        $stmt->close();
        exit();
    } else {
        header("HTTP/1.1 404 Not Found");
        echo "Error: Application or resume not found.";
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo "Error: No application ID provided.";
}

if (isset($stmt)) {
    $stmt->close();
}
$con->close();
?>