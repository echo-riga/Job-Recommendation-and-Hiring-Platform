<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'database.php';

if (isset($_GET['appid'])) {
    $applicationID = (int)$_GET['appid'];

    $stmt = $con->prepare("
        SELECT 
            a.resume, 
            u.firstname, 
            u.lastname
        FROM 
            application a
        JOIN 
            users u ON a.userid = u.userid
        WHERE 
            a.applicationid = ?
    ");
    $stmt->bind_param("i", $applicationID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($resumeData, $firstName, $lastName);
        $stmt->fetch();
        

        $processedData = processResumeData($resumeData);
        
        if ($processedData === null) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Error: Invalid resume data format";
            exit();
        }
        

        $fileInfo = detectFileTypeFromContent($processedData);
        
        $fileName = "Resume_" . $lastName . "_" . $firstName . "_" . $applicationID . $fileInfo['extension'];
        

        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: ' . $fileInfo['mime_type']);
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($processedData));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        echo $processedData;
        
        $stmt->close();
        exit();
    } else {
        header("HTTP/1.1 404 Not Found");
        echo "Error: Application or resume not found.";
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo "Error: No application ID provided.";
}

function processResumeData($resumeData) {

    if (substr($resumeData, 0, 4) === "%PDF") {
        return $resumeData;
    }
    

    $decoded = base64_decode($resumeData, true);
    if ($decoded !== false) {
        return $decoded;
    }
    
    return $resumeData;
}

function detectFileTypeFromContent($fileData) {
  
    if (substr($fileData, 0, 4) === "%PDF") {
        return ['mime_type' => 'application/pdf', 'extension' => '.pdf'];
    }
    

    $signatures = [
        "\x50\x4B\x03\x04" => ['mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'extension' => '.docx'], // DOCX
        "\xD0\xCF\x11\xE0" => ['mime_type' => 'application/msword', 'extension' => '.doc'], // DOC
        "\x25\x50\x44\x46" => ['mime_type' => 'application/pdf', 'extension' => '.pdf'], // PDF (alternative)
    ];
    
    foreach ($signatures as $signature => $info) {
        if (substr($fileData, 0, strlen($signature)) === $signature) {
            return $info;
        }
    }
    

    if (class_exists('finfo')) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($fileData);
            
            $mimeMap = [
                'application/pdf' => ['mime_type' => 'application/pdf', 'extension' => '.pdf'],
                'application/msword' => ['mime_type' => 'application/msword', 'extension' => '.doc'],
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'extension' => '.docx'],
                'text/plain' => ['mime_type' => 'text/plain', 'extension' => '.txt'],
                'image/jpeg' => ['mime_type' => 'image/jpeg', 'extension' => '.jpg'],
                'image/png' => ['mime_type' => 'image/png', 'extension' => '.png'],
            ];
            
            if (isset($mimeMap[$mimeType])) {
                return $mimeMap[$mimeType];
            }
        } catch (Exception $e) {
     
        }
    }
    

    return ['mime_type' => 'application/pdf', 'extension' => '.pdf'];
}

if (isset($stmt)) {
    $stmt->close();
}
$con->close();
?>