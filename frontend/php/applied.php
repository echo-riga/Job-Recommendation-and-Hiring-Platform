<?php
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['userid'];

$query = "
  SELECT 
      a.applicationid,
      a.postid,
      a.userid,
      a.resume,
      a.status,
      a.date_applied,
      j.postjobrole AS job_role,
      j.postsummary AS post_summary,
      j.postresponsibilities AS post_responsibilities,
      j.postspecification AS post_specification,
      j.postexperience AS post_experience,
      j.postsalary AS post_salary,
      j.postaddress AS post_address,
      j.posttype AS job_type,
      j.postworksetup AS post_worksetup,
      j.postapplicantlimit AS post_applicant_limit,
      j.postdate AS post_date,
      j.postdeadline AS post_deadline,
      j.poststatus AS post_status
  FROM application AS a
  INNER JOIN jobposting AS j ON a.postid = j.postid
  WHERE a.userid = ?
  ORDER BY a.date_applied DESC
";



$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$con->close();
?>
