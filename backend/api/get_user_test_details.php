<?php
session_start();
include '../php/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit();
}

try {
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit();
    }

    // Get user basic information
    $userQuery = "
        SELECT 
            u.userid,
            u.firstname,
            u.lastname,
            u.email,
            u.contact,
            u.created_at as registered_date
        FROM users u
        WHERE u.userid = ? AND u.status = 'ACTIVE'
    ";
    
    $stmt = $con->prepare($userQuery);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    
    if ($userResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $userData = $userResult->fetch_assoc();
    
    // Get ALL test history for this user (latest first)
    $testHistoryQuery = "
        SELECT 
            jr.id,
            jr.job1,
            jr.job2,
            jr.job3,
            jr.job4,
            jr.job5,
            jr.job1_confidence,
            jr.job2_confidence,
            jr.job3_confidence,
            jr.job4_confidence,
            jr.job5_confidence,
            jr.critical_thinking,
            jr.problem_solving,
            jr.communication,
            jr.teamwork,
            jr.adaptability,
            jr.created_at as test_date
        FROM job_recommendations jr
        WHERE jr.user_id = ?
        ORDER BY jr.created_at DESC
    ";
    
    $stmt = $con->prepare($testHistoryQuery);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $testHistoryResult = $stmt->get_result();
    
    $testHistory = [];
    $latestTest = null;
    
    while ($row = $testHistoryResult->fetch_assoc()) {
        $testEntry = [
            'id' => $row['id'],
            'recommended_roles' => [
                ['role' => $row['job1'], 'confidence' => $row['job1_confidence']],
                ['role' => $row['job2'], 'confidence' => $row['job2_confidence']],
                ['role' => $row['job3'], 'confidence' => $row['job3_confidence']],
                ['role' => $row['job4'], 'confidence' => $row['job4_confidence']],
                ['role' => $row['job5'], 'confidence' => $row['job5_confidence']]
            ],
            'skills' => [
                'critical_thinking' => $row['critical_thinking'],
                'problem_solving' => $row['problem_solving'],
                'communication' => $row['communication'],
                'teamwork' => $row['teamwork'],
                'adaptability' => $row['adaptability']
            ],
            'test_date' => $row['test_date']
        ];
        
        $testHistory[] = $testEntry;
        
        // Store the latest test separately
        if (!$latestTest) {
            $latestTest = $testEntry;
        }
    }
    
    // Calculate match percentage for the latest test (using your existing logic)
    $latestMatchPercentage = 0;
    $latestRecommendedRole = '';
    
    if ($latestTest) {
        $latestRecommendedRole = $latestTest['recommended_roles'][0]['role'];
        $latestConfidence = $latestTest['recommended_roles'][0]['confidence'];
        $latestMatchPercentage = calculateMatchPercentage($latestConfidence, 0);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'user_info' => [
                'userid' => $userData['userid'],
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'email' => $userData['email'],
                'contact' => $userData['contact'],
                'registered_date' => $userData['registered_date']
            ],
            'latest_test' => [
                'recommended_role' => $latestRecommendedRole,
                'match_percentage' => $latestMatchPercentage,
                'test_date' => $latestTest ? $latestTest['test_date'] : null,
                'skills' => $latestTest ? $latestTest['skills'] : null
            ],
            'test_history' => $testHistory
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching user test details: ' . $e->getMessage()
    ]);
}

// Reuse your existing match percentage calculation function
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

$con->close();
?>