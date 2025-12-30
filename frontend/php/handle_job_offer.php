<?php
include 'database.php';
include 'mail_config.php';
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$applicationid = $_POST['applicationid'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($applicationid) || empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$appQuery = mysqli_query($con, "SELECT a.*, u.firstname, u.email 
  FROM application a 
  JOIN users u ON a.userid = u.userid 
  WHERE a.applicationid = '$applicationid'");

if (!$appQuery || mysqli_num_rows($appQuery) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Application not found']);
    exit;
}

$app = mysqli_fetch_assoc($appQuery);
$email = $app['email'];
$name = $app['firstname'];

$status = ($action == 'accept') ? 'Job Offer Accepted' : 'Job Offer Rejected';
$subject = ($action == 'accept') ? 'Job Offer Acceptance' : 'Job Offer Decision';

if ($action == 'accept') {
    $message = "
    Dear $name,<br><br>
    Good day! Thank you for accepting our offer and joining <b>Philkoei International</b>!<br>
    We’re excited to have you on board and look forward to working with you as part of our growing team.<br>
    Your skills and experience will surely make a great contribution, and we’re eager to see you thrive in your new role.<br><br>
    Please expect to receive another email soon with details about your onboarding process, including your first-day instructions and other important information.<br><br>
    Once again, welcome to the Philkoei family — we’re happy to have you with us!<br><br>
    <b>Warm regards,</b><br>
    Philkoei International Team
    ";
} else {
    $message = "
    Dear $name,<br><br>
    Good day! Thank you so much for taking the time to meet with us and for your interest in joining <b>Philkoei International</b>.<br>
    It was a pleasure getting to know you and learning more about your background, experiences, and achievements.<br><br>
    After careful consideration, we’ve decided to move forward with other candidates for the position at this time.<br>
    Please know that this decision was not a reflection of your skills or accomplishments, but rather about finding the best fit for the specific requirements of the role.<br><br>
    We truly appreciate the effort you put into your application and interview, and we’re confident that your talents will open doors to great opportunities ahead.<br><br>
    Thank you once again for your time and interest in our company.<br>
    We wish you all the best in your future endeavors!<br><br>
    <b>Warm regards,</b><br>
    Philkoei International Team
    ";
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = MAIL_PORT;

    $mail->setFrom(MAIL_FROM, 'Philkoei International');
    $mail->addAddress($email, $name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->send();

    mysqli_query($con, "UPDATE application SET status = '$status' WHERE applicationid = '$applicationid'");

    echo json_encode(['status' => 'success', 'message' => "Job offer has been $status."]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"]);
}
?>