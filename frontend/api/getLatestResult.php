<?php
session_start();
include '../../backend/php/database.php';

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Log the request for debugging
error_log("getLatestResult.php accessed by user: " . ($_SESSION['userid'] ?? 'not set'));

if (!isset($_SESSION['userid'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['userid'];
error_log("Processing request for user_id: " . $user_id);

try {
    // First, check if the database connection is working
    if (!$con) {
        throw new Exception("Database connection failed: " . $con->connect_error);
    }

    // Fetch the latest career assessment results for the user
    $query = "
        SELECT 
            jr.*,
            u.firstname,
            u.lastname,
            u.email
        FROM job_recommendations jr
        INNER JOIN users u ON jr.user_id = u.userid
        WHERE jr.user_id = ?
        ORDER BY jr.created_at DESC
        LIMIT 1
    ";
    
    error_log("Executing query: " . $query);
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }
    
    if ($result->num_rows > 0) {
        $assessmentData = $result->fetch_assoc();
        error_log("Found assessment data: " . print_r($assessmentData, true));
        
        // Validate required fields exist
        $required_fields = ['job1', 'job1_confidence', 'job2', 'job2_confidence', 'job3', 'job3_confidence', 
                           'job4', 'job4_confidence', 'job5', 'job5_confidence',
                           'critical_thinking', 'problem_solving', 'communication', 'teamwork', 'adaptability'];
        
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (!isset($assessmentData[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
        
        // Format the response with job recommendations
        $response = [
            'success' => true,
            'message' => 'Latest assessment results found',
            'assessment' => [
                'recommendation_id' => $assessmentData['id'] ?? null,
                'user_info' => [
                    'firstname' => $assessmentData['firstname'] ?? '',
                    'lastname' => $assessmentData['lastname'] ?? '',
                    'email' => $assessmentData['email'] ?? ''
                ],
                'top_jobs' => [
                    [
                        'job' => $assessmentData['job1'],
                        'probability' => floatval($assessmentData['job1_confidence']),
                        'displayPercentage' => calculateMatchPercentage(floatval($assessmentData['job1_confidence']), 0)
                    ],
                    [
                        'job' => $assessmentData['job2'],
                        'probability' => floatval($assessmentData['job2_confidence']),
                        'displayPercentage' => calculateMatchPercentage(floatval($assessmentData['job2_confidence']), 1)
                    ],
                    [
                        'job' => $assessmentData['job3'],
                        'probability' => floatval($assessmentData['job3_confidence']),
                        'displayPercentage' => calculateMatchPercentage(floatval($assessmentData['job3_confidence']), 2)
                    ],
                    [
                        'job' => $assessmentData['job4'],
                        'probability' => floatval($assessmentData['job4_confidence']),
                        'displayPercentage' => calculateMatchPercentage(floatval($assessmentData['job4_confidence']), 3)
                    ],
                    [
                        'job' => $assessmentData['job5'],
                        'probability' => floatval($assessmentData['job5_confidence']),
                        'displayPercentage' => calculateMatchPercentage(floatval($assessmentData['job5_confidence']), 4)
                    ]
                ],
                'soft_traits' => [
                    'Critical Thinking' => floatval($assessmentData['critical_thinking']),
                    'Problem Solving' => floatval($assessmentData['problem_solving']),
                    'Communication' => floatval($assessmentData['communication']),
                    'Teamwork' => floatval($assessmentData['teamwork']),
                    'Adaptability' => floatval($assessmentData['adaptability'])
                ],
                'assessment_date' => $assessmentData['created_at'] ?? date('Y-m-d H:i:s')
            ]
        ];
        
        error_log("Sending successful response");
        echo json_encode($response);
        
    } else {
        error_log("No assessment results found for user_id: " . $user_id);
        echo json_encode([
            'success' => false, 
            'message' => 'No assessment results found for this user'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in getLatestResult.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug_info' => [
            'user_id' => $user_id ?? 'not set',
            'session_set' => isset($_SESSION['userid']),
            'database_connected' => isset($con) && $con ? true : false
        ]
    ]);
}

// Function to convert probability to match percentage (same logic as JavaScript)
function calculateMatchPercentage($probability, $rank) {
    $baseScore = $probability * 100;
    
    $matchPercentage = 0;
    
    if ($rank === 0) {
        $matchPercentage = 60 + ($baseScore * 0.6);
    } else if ($rank === 1) {
        $matchPercentage = 55 + ($baseScore * 0.5);
    } else if ($rank === 2) {
        $matchPercentage = 50 + ($baseScore * 0.45);
    } else if ($rank === 3) {
        $matchPercentage = 45 + ($baseScore * 0.4);
    } else {
        $matchPercentage = 40 + ($baseScore * 0.35);
    }
    
    $matchPercentage = max(40, min(95, $matchPercentage));
    return round($matchPercentage);
}


?>