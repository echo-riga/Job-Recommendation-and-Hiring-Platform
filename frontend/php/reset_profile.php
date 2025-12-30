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
    $check = $con->prepare("SELECT userid FROM users WHERE userid = ?");
    $check->bind_param("i", $userid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $stmt = $con->prepare("
            UPDATE users 
            SET firstname = '', 
                lastname = '', 
                email = '', 
                contact = '', 
                bday = NULL, 
                educlvl = '', 
                course = '', 
                school = ''
            WHERE userid = ?
        ");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->close();
    }

    $check->close();

    header("Location: profile2.php?reset=1");
    exit();
}
?>
