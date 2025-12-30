<?php
include 'database.php'; 
header('Content-Type: application/json'); 

if (isset($_GET['adminid'])) {
    $adminId = (int)$_GET['adminid'];

    $query = "SELECT adminid, adminname, adminemail, admincreated_at, adminstatus, adminimage FROM admin WHERE adminid = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $adminId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {
        $admin['joined_date_formatted'] = date('M d, Y', strtotime($admin['admincreated_at']));

        if (!empty($admin['adminimage'])) {
            $admin['adminimage_base64'] = 'data:image/jpeg;base64,' . base64_encode($admin['adminimage']);
        } else {
            $admin['adminimage_base64'] = null; 
        }
        unset($admin['adminimage']); 

        echo json_encode($admin);
    } else {
        echo json_encode(['error' => 'Admin not found']);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'No admin ID provided']);
}

mysqli_close($con);
?>