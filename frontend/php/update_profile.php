<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'database.php'; 

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $contact   = trim($_POST['contact'] ?? '');
    $bday      = $_POST['bday'] ?? null;
    $educlvl   = trim($_POST['educlvl'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $school    = trim($_POST['school'] ?? '');
    $check = $con->prepare("SELECT userid FROM users WHERE userid = ?");
    $check->bind_param("i", $userid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $stmt = $con->prepare("UPDATE users 
            SET firstname=?, lastname=?, email=?, contact=?, bday=?, educlvl=?, course=?, school=? 
            WHERE userid=?");
        $stmt->bind_param("ssssssssi", $firstname, $lastname, $email, $contact, $bday, $educlvl, $course, $school, $userid);
        $stmt->execute();
        $stmt->close();

        $fullname = "user: " . $firstname . " " . $lastname;
        $action = "Profile Update";
        $details = "$fullname updated their profile information";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $userid, $fullname, $action, $details);
        $audit->execute();
        $audit->close();

    } else {

        $stmt = $con->prepare("INSERT INTO users (userid, firstname, lastname, email, contact, bday, educlvl, course, school, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issssssss", $userid, $firstname, $lastname, $email, $contact, $bday, $educlvl, $course, $school);
        $stmt->execute();
        $stmt->close();

        $fullname = "user: " . $firstname . " " . $lastname;
        $action = "Profile Creation";
        $details = "$fullname created their profile information";

        $audit = $con->prepare("INSERT INTO audit (userid, username, action, details, time) VALUES (?, ?, ?, ?, NOW())");
        $audit->bind_param("isss", $userid, $fullname, $action, $details);
        $audit->execute();
        $audit->close();
    }

    $check->close();

    header("Location: profile2.php?updated=1");
    exit();
}
?>
