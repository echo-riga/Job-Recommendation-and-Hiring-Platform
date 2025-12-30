<?php
session_start();
include '../../backend/php/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get overall statistics (all users) - Total tests ever taken
    $overallTotalQuery = "SELECT COUNT(*) as overall_total FROM job_recommendations";
    $stmt = $con->prepare($overallTotalQuery);
    $stmt->execute();
    $overallTotalResult = $stmt->get_result();
    $overallTotalData = $overallTotalResult->fetch_assoc();
    $overallTotal = $overallTotalData['overall_total'] ?? 0;
    $stmt->close();
    
    // Get overall tests taken today (all users)
    $overallTodayQuery = "SELECT COUNT(*) as overall_today FROM job_recommendations WHERE DATE(created_at) = CURDATE()";
    $stmt = $con->prepare($overallTodayQuery);
    $stmt->execute();
    $overallTodayResult = $stmt->get_result();
    $overallTodayData = $overallTodayResult->fetch_assoc();
    $overallToday = $overallTodayData['overall_today'] ?? 0;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'today_tests' => (int)$overallToday,
            'total_tests' => (int)$overallTotal,
            'accuracy_percentage' => 95 // Default/hardcoded accuracy
        ],
        'current_date' => date('Y-m-d')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving statistics: ' . $e->getMessage()
    ]);
}
?>