<?php
session_start();
include '../php/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit();
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

try {
    // Total Tests Taken
    $totalTestsQuery = "SELECT COUNT(*) as total_tests FROM job_recommendations";
    $totalTestsResult = $con->query($totalTestsQuery);
    $totalTests = $totalTestsResult->fetch_assoc()['total_tests'];
    
    // Most Recommended IT Role
    $topRoleQuery = "SELECT job1, COUNT(*) as count 
                    FROM job_recommendations 
                    WHERE job1 IS NOT NULL AND job1 != '' AND job1 != '0'
                    GROUP BY job1 
                    ORDER BY count DESC 
                    LIMIT 1";
    $topRoleResult = $con->query($topRoleQuery);
    $topRole = $topRoleResult->fetch_assoc();
    $mostRecommendedRole = $topRole ? $topRole['job1'] : 'No data available';
    $roleShare = $topRole ? round(($topRole['count'] / $totalTests) * 100) : 0;
    
    // Average Test Score (using match percentage calculation)
    $avgScoreQuery = "SELECT AVG(job1_confidence) as avg_confidence FROM job_recommendations WHERE job1_confidence IS NOT NULL";
    $avgScoreResult = $con->query($avgScoreQuery);
    $avgConfidence = $avgScoreResult->fetch_assoc()['avg_confidence'];
    
    $averageScore = $avgConfidence ? calculateMatchPercentage($avgConfidence, 0) : 0;
    
    // Calculate trends (compared to previous period)
    $trendPeriod = date('Y-m-d', strtotime('-30 days'));
    
    // Previous period total tests
    $prevTestsQuery = "SELECT COUNT(*) as prev_tests FROM job_recommendations WHERE created_at < '$trendPeriod'";
    $prevTestsResult = $con->query($prevTestsQuery);
    $prevTests = $prevTestsResult->fetch_assoc()['prev_tests'];
    
    // Current period total tests (last 30 days)
    $currentTestsQuery = "SELECT COUNT(*) as current_tests FROM job_recommendations WHERE created_at >= '$trendPeriod'";
    $currentTestsResult = $con->query($currentTestsQuery);
    $currentTests = $currentTestsResult->fetch_assoc()['current_tests'];
    
    // Calculate test trend
    $testsTrend = 'neutral';
    $testsTrendValue = 'N/A';
    if ($prevTests > 0 && $currentTests > 0) {
        $trendPercentage = (($currentTests - $prevTests) / $prevTests) * 100;
        $testsTrendValue = abs(round($trendPercentage)) . '%';
        $testsTrend = $trendPercentage > 0 ? 'up' : ($trendPercentage < 0 ? 'down' : 'neutral');
    } else if ($prevTests == 0 && $currentTests > 0) {
        $testsTrendValue = 'New data';
        $testsTrend = 'up';
    }
    
    // Previous period average score
    $prevScoreQuery = "SELECT AVG(job1_confidence) as prev_avg FROM job_recommendations WHERE created_at < '$trendPeriod' AND job1_confidence IS NOT NULL";
    $prevScoreResult = $con->query($prevScoreQuery);
    $prevAvgConfidence = $prevScoreResult->fetch_assoc()['prev_avg'];
    $prevAverageScore = $prevAvgConfidence ? calculateMatchPercentage($prevAvgConfidence, 0) : 0;
    
    // Calculate score trend
    $scoreTrend = 'neutral';
    $scoreTrendValue = 'N/A';
    if ($prevAverageScore > 0 && $averageScore > 0) {
        $scoreDifference = $averageScore - $prevAverageScore;
        $scoreTrendValue = abs(round($scoreDifference)) . '%';
        $scoreTrend = $scoreDifference > 0 ? 'up' : ($scoreDifference < 0 ? 'down' : 'neutral');
    } else if ($prevAverageScore == 0 && $averageScore > 0) {
        $scoreTrendValue = 'New data';
        $scoreTrend = 'up';
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_tests' => $totalTests,
            'most_recommended_role' => $mostRecommendedRole,
            'role_share' => $roleShare,
            'average_score' => $averageScore,
            'trends' => [
                'tests' => [
                    'direction' => $testsTrend,
                    'value' => $testsTrendValue
                ],
                'score' => [
                    'direction' => $scoreTrend,
                    'value' => $scoreTrendValue
                ]
            ]
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching test statistics: ' . $e->getMessage()
    ]);
}

$con->close();
?>