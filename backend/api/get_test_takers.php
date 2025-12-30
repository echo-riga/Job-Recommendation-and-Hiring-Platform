<?php
session_start();
include '../php/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit();
}

try {
    // Get pagination parameters
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $search = $_GET['search'] ?? '';
    $offset = ($page - 1) * $limit;
    
    // Base query to get users who have taken tests with their latest test results
    $baseQuery = "
        SELECT 
            u.userid,
            u.firstname,
            u.lastname,
            u.email,
            u.contact,
            u.created_at as registered_date,
            jr.created_at as test_date,
            jr.job1 as recommended_role,
            jr.job1_confidence as confidence,
            jr.critical_thinking,
            jr.problem_solving,
            jr.communication,
            jr.teamwork,
            jr.adaptability
        FROM users u
        INNER JOIN (
            SELECT user_id, MAX(created_at) as latest_test
            FROM job_recommendations 
            GROUP BY user_id
        ) latest ON u.userid = latest.user_id
        INNER JOIN job_recommendations jr ON latest.user_id = jr.user_id AND latest.latest_test = jr.created_at
        WHERE u.status = 'ACTIVE'
    ";
    
    // Add search filter if provided
    if (!empty($search)) {
        $baseQuery .= " AND (u.firstname LIKE '%$search%' OR u.lastname LIKE '%$search%' OR u.email LIKE '%$search%')";
    }
    
    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM ($baseQuery) as filtered";
    $countResult = $con->query($countQuery);
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Add pagination and ordering
    $baseQuery .= " ORDER BY jr.created_at DESC LIMIT $limit OFFSET $offset";
    
    $result = $con->query($baseQuery);
    $testTakers = [];
    
    while ($row = $result->fetch_assoc()) {
        // Calculate match percentage using the same logic as before
        $matchPercentage = calculateMatchPercentage($row['confidence'], 0);
        
        $testTakers[] = [
            'userid' => $row['userid'],
            'name' => $row['firstname'] . ' ' . $row['lastname'],
            'email' => $row['email'],
            'contact' => $row['contact'],
            'registered_date' => $row['registered_date'],
            'test_date' => $row['test_date'],
            'recommended_role' => $row['recommended_role'],
            'match_percentage' => $matchPercentage,
            'skills' => [
                'critical_thinking' => $row['critical_thinking'],
                'problem_solving' => $row['problem_solving'],
                'communication' => $row['communication'],
                'teamwork' => $row['teamwork'],
                'adaptability' => $row['adaptability']
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $testTakers,
        'pagination' => [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'total' => (int)$totalRecords,
            'pages' => ceil($totalRecords / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching test takers: ' . $e->getMessage()
    ]);
}

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