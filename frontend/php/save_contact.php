<?php
session_start();
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

$userid = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $con = new mysqli("localhost", "root", "", "jftsystem_new_backup");
    if ($con->connect_error) die("Connection failed: " . $con->connect_error);

    $name = $con->real_escape_string($_POST['cntname']);
    $email = $con->real_escape_string($_POST['cntemail']);
    $subject = $con->real_escape_string($_POST['cntsubject']);
    $phone = $con->real_escape_string($_POST['cntphone']);
    $message = $con->real_escape_string($_POST['cntmessage']);

    $sql = "INSERT INTO contactus (conname, conemail, consubject, conphone, conmessage, condate, constatus)
            VALUES ('$name', '$email', '$subject', '$phone', '$message', NOW(), 'pending')";

    if ($con->query($sql)) {
        http_response_code(200);
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . $con->error;
    }
    $con->close();
}
?>