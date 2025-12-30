<?php
session_start();
include '../../backend/php/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['userid'];

try {
    // Get latest recommendations and match with job postings
    $query = "
        SELECT 
            jp.*,
            CASE 
                WHEN jp.postjobrole = latest_r.job1 THEN latest_r.job1_confidence
                WHEN jp.postjobrole = latest_r.job2 THEN latest_r.job2_confidence
                WHEN jp.postjobrole = latest_r.job3 THEN latest_r.job3_confidence
                WHEN jp.postjobrole = latest_r.job4 THEN latest_r.job4_confidence
                WHEN jp.postjobrole = latest_r.job5 THEN latest_r.job5_confidence
            END as match_confidence
        FROM jobposting jp
        JOIN (
            SELECT 
                job1, job1_confidence, job2, job2_confidence,
                job3, job3_confidence, job4, job4_confidence,
                job5, job5_confidence
            FROM job_recommendations 
            WHERE user_id = ?
            ORDER BY created_at DESC 
            LIMIT 1
        ) latest_r ON (
            jp.postjobrole = latest_r.job1 OR 
            jp.postjobrole = latest_r.job2 OR 
            jp.postjobrole = latest_r.job3 OR 
            jp.postjobrole = latest_r.job4 OR 
            jp.postjobrole = latest_r.job5
        )
        WHERE jp.poststatus = 'Open'
        ORDER BY match_confidence DESC
    ";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recommendedJobs = [];
    while ($row = $result->fetch_assoc()) {
        $recommendedJobs[] = $row;
    }
    
    $stmt->close();
    
    if (empty($recommendedJobs)) {
        echo json_encode([
            'success' => false, 
            'message' => 'No recommended jobs found'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'jobs' => $recommendedJobs,
            'total' => count($recommendedJobs)
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>