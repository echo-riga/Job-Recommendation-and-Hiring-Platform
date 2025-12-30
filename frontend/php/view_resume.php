<?php
include 'database.php'; 

if (!isset($_GET['applicationid'])) {
    http_response_code(400); 
    die("Error: Application ID not provided.");
}

$applicationid = intval($_GET['applicationid']);

if ($applicationid === 0) {
    http_response_code(400);
    die("Error: Invalid application ID format.");
}

$query = "SELECT resume FROM application WHERE applicationid = ?";
$stmt = $con->prepare($query);

if ($stmt === false) {
    http_response_code(500);
    die("Database error during preparation: " . htmlspecialchars($con->error));
}

$stmt->bind_param("i", $applicationid);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($resume);
    $stmt->fetch();

    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"resume_{$applicationid}.pdf\"");
    echo $resume;
} else {
    http_response_code(404);
    echo "Resume not found for this application ID.";
}

$stmt->close();
$con->close();
?>
