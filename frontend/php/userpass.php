<?php
session_start();
include 'database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userid = $_SESSION['userid'];
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit();
    }

    if ($new !== $confirm) {
        echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
        exit();
    }

    $stmt = $con->prepare("SELECT Password FROM users WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!$hashed_password || !password_verify($current, $hashed_password)) {
        echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
        exit();
    }

    $new_hashed = password_hash($new, PASSWORD_DEFAULT);
    $update = $con->prepare("UPDATE users SET Password = ? WHERE userid = ?");
    $update->bind_param("si", $new_hashed, $userid);

    if ($update->execute()) {
        $nameQuery = $con->prepare("SELECT CONCAT(firstname, ' ', lastname) AS fullname FROM users WHERE userid = ?");
        $nameQuery->bind_param("i", $userid);
        $nameQuery->execute();
        $nameQuery->bind_result($fullname);
        $nameQuery->fetch();
        $nameQuery->close();

        $username = "user: " . $fullname;
        $action = "Password Changed";
        $details = "$username successfully changed their password.";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $userid, $username, $action, $details);
        $audit->execute();
        $audit->close();

        session_destroy();
        echo json_encode(["status" => "success", "message" => "Password successfully changed."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating password."]);
    }

    $update->close();
    $con->close();
}
?>
