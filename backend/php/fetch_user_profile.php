<?php
include 'database.php';
header('Content-Type: application/json');

if (isset($_GET['userid'])) {
    $userId = (int)$_GET['userid'];

    $query = "SELECT userid, firstname, lastname, email, bday, contact, educlvl, course, school, created_at, status, image FROM users WHERE userid = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        $user['joined_date_formatted'] = date('M d, Y', strtotime($user['created_at']));

        if (!empty($user['image'])) {
            $user['image_base64'] = 'data:image/jpeg;base64,' . base64_encode($user['image']);
        } else {
            $user['image_base64'] = null;
        }
        unset($user['image']);

        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'No user ID provided']);
}

mysqli_close($con);
?>