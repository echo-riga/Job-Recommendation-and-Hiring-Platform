<?php
ob_start();
session_start();

include '../../backend/php/database.php';

ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userid = $_SESSION['userid'];

// Function to convert probability to match percentage
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

try {
    // Query for ALL test results to find the one with highest match percentage
    $allTestsQuery = "SELECT 
                    job1, job1_confidence,
                    job2, job2_confidence,
                    job3, job3_confidence,
                    job4, job4_confidence,
                    job5, job5_confidence,
                    created_at as test_date,
                    critical_thinking,
                    problem_solving, 
                    communication,
                    teamwork,
                    adaptability
                  FROM job_recommendations 
                  WHERE user_id = ? 
                    AND job1 IS NOT NULL 
                    AND job1 != '' 
                    AND job1 != '0'
                  ORDER BY created_at DESC";
    
    $stmt = $con->prepare($allTestsQuery);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $allTestsResult = $stmt->get_result();
    
    $allTests = [];
    $bestTest = null;
    $highestPercentage = 0;
    
    while ($test = $allTestsResult->fetch_assoc()) {
        // Calculate the highest match percentage for this test
        $currentHighest = 0;
        
        if (!empty($test['job1'])) {
            $job1Percentage = calculateMatchPercentage($test['job1_confidence'], 0);
            $currentHighest = max($currentHighest, $job1Percentage);
        }
        
        if (!empty($test['job2']) && $test['job2'] != '0') {
            $job2Percentage = calculateMatchPercentage($test['job2_confidence'], 1);
            $currentHighest = max($currentHighest, $job2Percentage);
        }
        
        if (!empty($test['job3']) && $test['job3'] != '0') {
            $job3Percentage = calculateMatchPercentage($test['job3_confidence'], 2);
            $currentHighest = max($currentHighest, $job3Percentage);
        }
        
        if (!empty($test['job4']) && $test['job4'] != '0') {
            $job4Percentage = calculateMatchPercentage($test['job4_confidence'], 3);
            $currentHighest = max($currentHighest, $job4Percentage);
        }
        
        if (!empty($test['job5']) && $test['job5'] != '0') {
            $job5Percentage = calculateMatchPercentage($test['job5_confidence'], 4);
            $currentHighest = max($currentHighest, $job5Percentage);
        }
        
        // Store test with percentage for comparison
        $allTests[] = [
            'test_data' => $test,
            'highest_percentage' => $currentHighest
        ];
        
        // Update best test if this one has higher percentage
        if ($currentHighest > $highestPercentage) {
            $highestPercentage = $currentHighest;
            $bestTest = $test;
        }
    }
    $stmt->close();

    // If no best test found (shouldn't happen if there are tests), use the latest
    if (!$bestTest && count($allTests) > 0) {
        $bestTest = $allTests[0]['test_data'];
        $highestPercentage = $allTests[0]['highest_percentage'];
    }

    // Query for test history (last 10 tests)
    $historyQuery = "SELECT 
                    job1 as recommended_position, 
                    job1_confidence as match_probability,
                    created_at as test_date
                  FROM job_recommendations 
                  WHERE user_id = ? 
                    AND job1 IS NOT NULL 
                    AND job1 != '' 
                    AND job1 != '0'
                  ORDER BY created_at DESC 
                  LIMIT 10";
    
    $stmt = $con->prepare($historyQuery);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $historyResult = $stmt->get_result();
    $testHistory = [];
    
    while ($row = $historyResult->fetch_assoc()) {
        $matchPercentage = calculateMatchPercentage($row['match_probability'], 0);
        
        $testHistory[] = [
            'date' => $row['test_date'],
            'suggested_job' => $row['recommended_position'],
            'match_score' => $matchPercentage . '%'
        ];
    }
    $stmt->close();

    ob_clean();
    
    // Prepare response
    $response = [
        'success' => true,
        'test_history' => $testHistory,
        'count' => count($testHistory)
    ];

    // Add best test data if available
    if ($bestTest) {
        // Calculate match percentages for all jobs in the best test
        $jobRecommendations = [];
        
        if (!empty($bestTest['job1'])) {
            $jobRecommendations[] = [
                'job' => $bestTest['job1'],
                'match_percentage' => calculateMatchPercentage($bestTest['job1_confidence'], 0),
                'probability' => $bestTest['job1_confidence']
            ];
        }
        
        if (!empty($bestTest['job2']) && $bestTest['job2'] != '0') {
            $jobRecommendations[] = [
                'job' => $bestTest['job2'],
                'match_percentage' => calculateMatchPercentage($bestTest['job2_confidence'], 1),
                'probability' => $bestTest['job2_confidence']
            ];
        }
        
        if (!empty($bestTest['job3']) && $bestTest['job3'] != '0') {
            $jobRecommendations[] = [
                'job' => $bestTest['job3'],
                'match_percentage' => calculateMatchPercentage($bestTest['job3_confidence'], 2),
                'probability' => $bestTest['job3_confidence']
            ];
        }
        
        if (!empty($bestTest['job4']) && $bestTest['job4'] != '0') {
            $jobRecommendations[] = [
                'job' => $bestTest['job4'],
                'match_percentage' => calculateMatchPercentage($bestTest['job4_confidence'], 3),
                'probability' => $bestTest['job4_confidence']
            ];
        }
        
        if (!empty($bestTest['job5']) && $bestTest['job5'] != '0') {
            $jobRecommendations[] = [
                'job' => $bestTest['job5'],
                'match_percentage' => calculateMatchPercentage($bestTest['job5_confidence'], 4),
                'probability' => $bestTest['job5_confidence']
            ];
        }
        
        // Sort by match percentage (highest first)
        usort($jobRecommendations, function($a, $b) {
            return $b['match_percentage'] - $a['match_percentage'];
        });

        $response['latest_test'] = [
            'top_job' => !empty($jobRecommendations) ? $jobRecommendations[0] : null,
            'all_recommendations' => $jobRecommendations,
            'test_date' => $bestTest['test_date'],
            'is_highest_score' => true, // Flag to indicate this is the highest score, not necessarily latest
            'highest_percentage' => $highestPercentage,
            'skills' => [
                'Logical Reasoning' => floatval($bestTest['critical_thinking']),
                'Problem Solving' => floatval($bestTest['problem_solving']),
                'Technical Skills' => floatval($bestTest['communication']),
                'Communication' => floatval($bestTest['teamwork']),
                'Creativity' => floatval($bestTest['adaptability'])
            ]
        ];
    }

    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    
    error_log("Test History Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching test history'
    ]);
}

$con->close();
ob_end_flush();
?>