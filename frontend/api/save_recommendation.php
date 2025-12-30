<?php
session_start();
include '../../backend/php/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['userid'];

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['api_results'])) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Function to convert probability to match percentage (same logic as JavaScript)
function calculateMatchPercentage($probability, $rank) {
    $baseScore = $probability * 100;
    
    $matchPercentage = 0;
    
    if ($rank === 0) {
        $matchPercentage = 75 + ($baseScore * 0.8);
    } else if ($rank === 1) {
        $matchPercentage = 70 + ($baseScore * 0.7);
    } else if ($rank === 2) {
        $matchPercentage = 65 + ($baseScore * 0.6);
    } else if ($rank === 3) {
        $matchPercentage = 60 + ($baseScore * 0.5);
    } else {
        $matchPercentage = 55 + ($baseScore * 0.4);
    }
    
    $matchPercentage = max(55, min(98, $matchPercentage));
    return round($matchPercentage);
}

// Function to validate job titles
function validateJobTitle($job) {
    if (empty($job) || $job === '0' || $job === 0) {
        return false;
    }
    return true;
}

try {
    $api_results = $input['api_results'];
    $top_jobs = $api_results['top_5_jobs'];
    $soft_traits = $api_results['soft_traits'];
    
    error_log("RAW INPUT RECEIVED: " . print_r($input, true));
    error_log("TOP JOBS RECEIVED: " . print_r($top_jobs, true));
    error_log("SOFT TRAITS RECEIVED: " . print_r($soft_traits, true));
    
    // EXTRACT JOB TITLES
    $job1 = (isset($top_jobs[0]['job']) && validateJobTitle($top_jobs[0]['job'])) 
        ? $top_jobs[0]['job'] 
        : 'Data Analyst';
    
    $job2 = (isset($top_jobs[1]['job']) && validateJobTitle($top_jobs[1]['job'])) 
        ? $top_jobs[1]['job'] 
        : 'Cybersecurity Specialist';
    
    $job3 = (isset($top_jobs[2]['job']) && validateJobTitle($top_jobs[2]['job'])) 
        ? $top_jobs[2]['job'] 
        : 'Database Administrator';
    
    $job4 = (isset($top_jobs[3]['job']) && validateJobTitle($top_jobs[3]['job'])) 
        ? $top_jobs[3]['job'] 
        : 'Network Engineer';
    
    $job5 = (isset($top_jobs[4]['job']) && validateJobTitle($top_jobs[4]['job'])) 
        ? $top_jobs[4]['job'] 
        : 'IT Support Specialist';
    
    // Round confidence values to 2 decimal places to match database schema
    $job1_confidence = isset($top_jobs[0]['probability']) ? round(floatval($top_jobs[0]['probability']), 2) : 0.10;
    $job2_confidence = isset($top_jobs[1]['probability']) ? round(floatval($top_jobs[1]['probability']), 2) : 0.10;
    $job3_confidence = isset($top_jobs[2]['probability']) ? round(floatval($top_jobs[2]['probability']), 2) : 0.10;
    $job4_confidence = isset($top_jobs[3]['probability']) ? round(floatval($top_jobs[3]['probability']), 2) : 0.10;
    $job5_confidence = isset($top_jobs[4]['probability']) ? round(floatval($top_jobs[4]['probability']), 2) : 0.10;

    // DEBUG: Log what we're about to save
    error_log("Jobs to save - job1: $job1, job2: $job2, job3: $job3, job4: $job4, job5: $job5");
    error_log("Rounded confidences - job1: $job1_confidence, job2: $job2_confidence, job3: $job3_confidence, job4: $job4_confidence, job5: $job5_confidence");
    
    // Extract soft traits
    $critical_thinking = isset($soft_traits['Critical Thinking']) ? floatval($soft_traits['Critical Thinking']) : 3.0;
    $problem_solving = isset($soft_traits['Problem Solving']) ? floatval($soft_traits['Problem Solving']) : 3.0;
    $communication = isset($soft_traits['Communication']) ? floatval($soft_traits['Communication']) : 3.0;
    $teamwork = isset($soft_traits['Teamwork']) ? floatval($soft_traits['Teamwork']) : 3.0;
    $adaptability = isset($soft_traits['Adaptability']) ? floatval($soft_traits['Adaptability']) : 3.0;
    
    // Validate soft trait ranges (1-5)
    $critical_thinking = max(1, min(5, $critical_thinking));
    $problem_solving = max(1, min(5, $problem_solving));
    $communication = max(1, min(5, $communication));
    $teamwork = max(1, min(5, $teamwork));
    $adaptability = max(1, min(5, $adaptability));
    
    // Get user's firstname and lastname for audit log
    $userQuery = "SELECT firstname, lastname FROM users WHERE userid = ?";
    $stmt = $con->prepare($userQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
    $firstname = $userData['firstname'] ?? 'Unknown';
    $lastname = $userData['lastname'] ?? 'User';
    $username = $firstname . ' ' . $lastname;
    $stmt->close();
    
    // DEBUG: Check final values before inserting
    error_log("=== FINAL VALUES BEFORE INSERT ===");
    error_log("job1: '$job1', confidence: $job1_confidence");
    error_log("job2: '$job2', confidence: $job2_confidence");
    error_log("job3: '$job3', confidence: $job3_confidence");
    error_log("job4: '$job4', confidence: $job4_confidence");
    error_log("job5: '$job5', confidence: $job5_confidence");
    
    // Prepare SQL statement for job recommendations
    $stmt = $con->prepare("
        INSERT INTO job_recommendations (
            user_id, job1, job1_confidence, job2, job2_confidence, 
            job3, job3_confidence, job4, job4_confidence, job5, job5_confidence,
            critical_thinking, problem_solving, communication, teamwork, adaptability
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        error_log("❌ SQL Prepare failed: " . $con->error);
        echo json_encode(['success' => false, 'message' => 'SQL Prepare failed: ' . $con->error]);
        exit();
    }
    
    // FIX: Use explicit type casting for binding to prevent data corruption
    $bind_user_id = (int)$user_id;
    $bind_job1 = (string)$job1;
    $bind_job1_conf = (float)$job1_confidence;
    $bind_job2 = (string)$job2;
    $bind_job2_conf = (float)$job2_confidence;
    $bind_job3 = (string)$job3;
    $bind_job3_conf = (float)$job3_confidence;
    $bind_job4 = (string)$job4;
    $bind_job4_conf = (float)$job4_confidence;
    $bind_job5 = (string)$job5;
    $bind_job5_conf = (float)$job5_confidence;
    $bind_critical = (float)$critical_thinking;
    $bind_problem = (float)$problem_solving;
    $bind_comm = (float)$communication;
    $bind_team = (float)$teamwork;
    $bind_adapt = (float)$adaptability;

    // DEBUG: Check binding variables
    error_log("Binding variables - job2: '$bind_job2', job5: '$bind_job5'");
    
    $stmt->bind_param(
        "isssssssssssdddd",
        $bind_user_id, $bind_job1, $bind_job1_conf, $bind_job2, $bind_job2_conf,
        $bind_job3, $bind_job3_conf, $bind_job4, $bind_job4_conf, $bind_job5, $bind_job5_conf,
        $bind_critical, $bind_problem, $bind_comm, $bind_team, $bind_adapt
    );
    
    if ($stmt->execute()) {
        $recommendation_id = $con->insert_id;
        error_log("✅ SQL Execute SUCCESS - Inserted ID: $recommendation_id");
        
     $auditAction = "Career Assessment Completed";
$auditDetails = "Top match: " . $job1;

$auditStmt = $con->prepare("
    INSERT INTO audit (adminid, userid, username, action, details) 
    VALUES (NULL, ?, ?, ?, ?)
");

$auditStmt->bind_param("isss", $user_id, $username, $auditAction, $auditDetails);

if ($auditStmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Results saved successfully',
        'recommendation_id' => $recommendation_id,
        'debug_saved_jobs' => [
            'job1' => $job1,
            'job2' => $job2,
            'job3' => $job3,
            'job4' => $job4,
            'job5' => $job5
        ]
    ]);
} else {
    echo json_encode([
        'success' => true, 
        'message' => 'Results saved but audit log failed',
        'recommendation_id' => $recommendation_id
    ]);
}
        $auditStmt->close();
    } else {
        error_log("❌ SQL Execute FAILED: " . $stmt->error);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save results: ' . $stmt->error,
            'debug_info' => [
                'job1' => $job1,
                'job2' => $job2,
                'job3' => $job3,
                'job4' => $job4,
                'job5' => $job5
            ]
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("❌ Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>