<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");


include_once '../php/database.php';


try {
    // Get average scores for each skill from job_recommendations
    $query = "
        SELECT 
            'Critical Thinking' as skill,
            ROUND(AVG(COALESCE(critical_thinking, 0)), 2) as average_score,
            COUNT(critical_thinking) as test_count
        FROM job_recommendations
        WHERE critical_thinking IS NOT NULL
        
        UNION ALL
        
        SELECT 
            'Problem Solving' as skill,
            ROUND(AVG(COALESCE(problem_solving, 0)), 2) as average_score,
            COUNT(problem_solving) as test_count
        FROM job_recommendations
        WHERE problem_solving IS NOT NULL
        
        UNION ALL
        
        SELECT 
            'Communication' as skill,
            ROUND(AVG(COALESCE(communication, 0)), 2) as average_score,
            COUNT(communication) as test_count
        FROM job_recommendations
        WHERE communication IS NOT NULL
        
        UNION ALL
        
        SELECT 
            'Teamwork' as skill,
            ROUND(AVG(COALESCE(teamwork, 0)), 2) as average_score,
            COUNT(teamwork) as test_count
        FROM job_recommendations
        WHERE teamwork IS NOT NULL
        
        UNION ALL
        
        SELECT 
            'Adaptability' as skill,
            ROUND(AVG(COALESCE(adaptability, 0)), 2) as average_score,
            COUNT(adaptability) as test_count
        FROM job_recommendations
        WHERE adaptability IS NOT NULL
    ";
    
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    
    $skill_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate percentages for pie chart
    $total_score = array_sum(array_column($skill_data, 'average_score'));
    $skills_with_percentages = [];
    
    foreach ($skill_data as $skill) {
        $percentage = $total_score > 0 ? round(($skill['average_score'] / $total_score) * 100, 1) : 0;
        $skills_with_percentages[] = [
            'skill' => $skill['skill'],
            'average_score' => $skill['average_score'],
            'percentage' => $percentage,
            'test_count' => $skill['test_count']
        ];
    }
    
    $response = [
        'success' => true,
        'data' => $skills_with_percentages,
        'total_tests' => $skill_data[0]['test_count'] ?? 0
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>