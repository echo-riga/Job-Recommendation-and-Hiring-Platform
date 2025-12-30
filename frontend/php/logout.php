<?php
session_start();
include_once 'database.php';


if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];

    $stmt = $con->prepare("SELECT firstname, lastname FROM users WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullname = "user: " . $row['firstname'] . " " . $row['lastname'];

        $action = "Logout";
        $details = "$fullname logged out";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $userid, $fullname, $action, $details);
        $audit->execute();
        $audit->close();
    }

    $stmt->close();
}

session_unset();
session_destroy();
header("Location: home.php?logout=success");
exit();
?>
